<?php
namespace App\Controllers;

use App\Models\Database;
use App\Security\Roles;
use App\Services\EncryptionService;
use App\Services\AuditLogger;
use App\Support\VisitTypes;

class CalendarController {
    private function checkAccess(array $allowedRoles): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
        $userRole = $_SESSION['user_role'] ?? '';
        $normalizedUserRole = strtolower(trim($userRole));
        $normalizedAllowed = array_map(function($r) { return strtolower(trim($r)); }, $allowedRoles);
        if (!in_array($normalizedUserRole, $normalizedAllowed) && $normalizedUserRole !== 'super admin' && $normalizedUserRole !== 'admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Access forbidden.']);
            exit();
        }
    }

    private const ALLOWED_STATUSES = ['Scheduled', 'Confirmed', 'Arrived', 'In Room', 'Completed', 'Cancelled', 'No Show', 'Waiting List', 'Checked-In', 'In-Progress'];

    /**
     * Returns the provider_time_blocks row that overlaps [$start, $end] on the appointment's day
     * (one-off block_date rows or weekly-recurring weekday rows), or null if the slot is free.
     */
    private const AVAILABILITY_CATEGORIES = ['In Office', 'Out Of Office', 'Vacation', 'Lunch', 'Reserved'];

    /**
     * Availability check for [$start, $end]. Returns a conflicting row (or a synthetic row with a 'message'
     * when the slot is outside the provider's In Office hours), or null if the slot is bookable.
     * 1) Any non-'In Office' row (time off) overlapping the slot blocks it.
     * 2) If the provider has set any 'In Office' hours, the slot must fall inside one window for that day
     *    (date-specific windows override weekly ones). A provider with no In Office rows is unrestricted.
     */
    private function blockedTimeConflict($providerId, string $start, string $end): ?array {
        $startTs = strtotime($start);
        $endTs = strtotime($end);
        if (!$startTs || !$endTs) return null;
        $date = date('Y-m-d', $startTs);
        $weekday = (int)date('w', $startTs);
        $startClock = date('H:i:s', $startTs);
        $endClock = date('Y-m-d', $endTs) === $date ? date('H:i:s', $endTs) : '23:59:59';
        $row = Database::fetch(
            "SELECT * FROM provider_time_blocks
             WHERE provider_id = ? AND category <> 'In Office'
               AND (block_date = ? OR (block_date IS NULL AND weekday = ?))
               AND start_time < ? AND end_time > ?
             LIMIT 1",
            [$providerId, $date, $weekday, $endClock, $startClock]
        );
        if ($row) return $row;

        $hasHours = Database::fetch("SELECT 1 AS x FROM provider_time_blocks WHERE provider_id = ? AND category = 'In Office' LIMIT 1", [$providerId]);
        if (!$hasHours) return null;

        $windows = Database::fetchAll(
            "SELECT start_time, end_time FROM provider_time_blocks WHERE provider_id = ? AND category = 'In Office' AND block_date = ? ORDER BY start_time",
            [$providerId, $date]
        );
        if (!$windows) {
            $windows = Database::fetchAll(
                "SELECT start_time, end_time FROM provider_time_blocks WHERE provider_id = ? AND category = 'In Office' AND block_date IS NULL AND weekday = ? ORDER BY start_time",
                [$providerId, $weekday]
            );
        }
        foreach ($windows as $w) {
            if ($w['start_time'] <= $startClock && $w['end_time'] >= $endClock) return null;
        }
        $shown = $windows
            ? 'available ' . implode(', ', array_map(fn($w) => substr($w['start_time'], 0, 5) . '-' . substr($w['end_time'], 0, 5), $windows))
            : 'not working that day';
        return ['message' => 'Provider is not available on ' . $date . ' at ' . substr($startClock, 0, 5) . '-' . substr($endClock, 0, 5) . ' (' . $shown . ').'];
    }

    private function blockedMessage(array $block, string $start): string {
        if (isset($block['message'])) return $block['message'];
        $reason = trim((string)($block['reason'] ?? '')) !== '' ? $block['reason'] : 'Blocked time';
        return 'Provider is unavailable on ' . date('Y-m-d', strtotime($start)) . ' from '
            . substr($block['start_time'], 0, 5) . ' to ' . substr($block['end_time'], 0, 5) . ' (' . $reason . ').';
    }

    public function doctorAppointmentsThisMonth(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        try {
            // Group appointments by provider for the current month
            $sql = "SELECT
                        u.id as provider_id,
                        CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                        COUNT(a.id) as appointment_count
                    FROM appointments a
                    JOIN users u ON a.provider_id = u.id
                    WHERE MONTH(a.start_time) = MONTH(CURRENT_DATE())
                      AND YEAR(a.start_time) = YEAR(CURRENT_DATE())
                      AND u.facility_id = ?
                    GROUP BY u.id, u.first_name, u.last_name
                    ORDER BY appointment_count DESC";

            $results = Database::fetchAll($sql, [$_SESSION['facility_id'] ?? null]);

            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch appointments by doctor'
            ]);
        }
    }

    public function doctorAppointmentsMonthWise(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        try {
            // Group appointments by provider and month for the current year
            $sql = "SELECT
                        u.id as provider_id,
                        CONCAT(u.first_name, ' ', u.last_name) as provider_name,
                        YEAR(a.start_time) as appointment_year,
                        MONTH(a.start_time) as appointment_month,
                        COALESCE(a.visit_type, 'General') as appointment_type,
                        COUNT(a.id) as appointment_count
                    FROM appointments a
                    JOIN users u ON a.provider_id = u.id
                    WHERE u.facility_id = ?
                    GROUP BY u.id, u.first_name, u.last_name, YEAR(a.start_time), MONTH(a.start_time), a.visit_type
                    ORDER BY appointment_year DESC, appointment_month ASC, provider_name ASC";

            $results = Database::fetchAll($sql, [$_SESSION['facility_id'] ?? null]);

            echo json_encode([
                'status' => 'success',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to fetch month-wise appointments by doctor'
            ]);
        }
    }

    // Doctor/Nurse see only their own schedule on the Calendar - mirrors DashboardController's identical
    // $isClinician scoping for "Today's Clinical Schedule". Super Admin, Receptionist and Billing Staff are
    // deliberately not scoped: front desk/billing need full-practice visibility to book for any provider and
    // bill per encounter, and the "Set Calendar View" multi-clinician picker is built for that use case.
    private const CLINICIAN_SCOPED = ['Doctor', 'Nurse'];

    public function index(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $role = $_SESSION['user_role'] ?? '';
        $isClinicianScoped = in_array($role, self::CLINICIAN_SCOPED, true);
        $scope = $isClinicianScoped ? ' AND a.provider_id = ?' : '';
        $params = $isClinicianScoped ? [$_SESSION['user_id']] : [];
        // Facility-based data isolation (see Roles::ALL_STAFF doc comment) - every role, not just
        // Doctor/Nurse, only sees appointments for their own facility's patients.
        $scope .= ' AND p.facility_id = ?';
        $params[] = $_SESSION['facility_id'] ?? null;

        $sql = "SELECT a.*, p.first_name_encrypted, p.last_name_encrypted, u.first_name as doc_first, u.last_name as doc_last
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users u ON a.provider_id = u.id
                WHERE 1=1{$scope}
                ORDER BY a.start_time ASC";

        $appointments = Database::fetchAll($sql, $params);

        $result = [];
        foreach ($appointments as $a) {
            $patientName = EncryptionService::decrypt($a['first_name_encrypted']) . ' ' . EncryptionService::decrypt($a['last_name_encrypted']);
            $statusVal = (!empty($a['status']) && trim($a['status']) !== '') ? $a['status'] : 'Scheduled';
            $result[] = [
                'id' => $a['id'],
                'patient_id' => $a['patient_id'],
                'patient_name' => $patientName,
                'provider_name' => $a['doc_first'] . ' ' . $a['doc_last'],
                'provider_id' => $a['provider_id'],
                'start_time' => $a['start_time'],
                'end_time' => $a['end_time'],
                'status' => $statusVal,
                'notes' => $a['notes'],
                'specialty' => $a['specialty'] ?? 'Family Medicine (Internal Medicine)',
                'facility' => $a['facility'] ?? 'raj',
                'category' => $a['category'] ?? 'Appointment',
                'visit_type' => $a['visit_type'] ?? VisitTypes::DEFAULT,
                'appointment_mode' => $a['appointment_mode'] ?? 'In Person',
                'appointment_for' => $a['appointment_for'] ?? 'Single Date',
                'period_frequency' => $a['period_frequency'] ?? null,
                'period_count' => $a['period_count'] ?? 1,
                'message_to_patient' => $a['message_to_patient'] ?? null,
                'waiting_list_data' => $a['waiting_list_data'] ?? null,
                'cancel_reason' => $a['cancel_reason'] ?? null
            ];
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'View Appointment Calendar', 'Calendar');

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    public function store(): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $patientId = $input['patient_id'] ?? null;
        $providerId = $input['provider_id'] ?? null;
        $startTime = $input['start_time'] ?? '';
        $endTime = $input['end_time'] ?? '';
        $notes = $input['notes'] ?? '';
        $status = $input['status'] ?? 'Scheduled';
        $specialty = $input['specialty'] ?? 'Family Medicine (Internal Medicine)';
        $facility = $input['facility'] ?? 'raj';
        $category = $input['category'] ?? 'Appointment';
        [$visitType, $visitTypeError] = VisitTypes::resolve($input['visit_type'] ?? null);
        if ($visitTypeError) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $visitTypeError]);
            return;
        }
        $appointmentMode = $input['appointment_mode'] ?? 'In Person';
        $appointmentFor = $input['appointment_for'] ?? 'Single Date';
        $periodFrequency = $input['period_frequency'] ?? null;
        $periodCount = intval($input['period_count'] ?? 1);
        $messageToPatient = $input['message_to_patient'] ?? null;
        $waitingListData = isset($input['waiting_list_data']) ? (is_array($input['waiting_list_data']) ? json_encode($input['waiting_list_data']) : $input['waiting_list_data']) : null;

        if (!$patientId || !$providerId || empty($startTime) || empty($endTime)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required appointment parameters.']);
            return;
        }

        // Facility-based data isolation: both the patient and the provider must belong to the
        // booking user's own facility - the same generic 404 either side would get if it just didn't exist.
        $fid = $_SESSION['facility_id'] ?? null;
        $patientOwned = Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $fid]);
        $providerOwned = Database::fetch("SELECT id FROM users WHERE id = ? AND facility_id = ?", [$providerId, $fid]);
        if (!$patientOwned || !$providerOwned) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Patient or provider not found.']);
            return;
        }

        // Validate date format
        try {
            $startDt = new \DateTime($startTime);
            $endDt = new \DateTime($endTime);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format.']);
            return;
        }

        if ($endDt <= $startDt) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
            return;
        }
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid appointment status.']);
            return;
        }

        // Calculate occurrences if Period (Recurring)
        $occurrences = [];

        if ($appointmentFor === 'Period' && !empty($input['starts_from']) && !empty($input['ends_on'])) {
            $startsFromStr = $input['starts_from'];
            $endsOnStr = $input['ends_on'];
            $repeatEvery = max(1, intval($input['repeat_every'] ?? 1));
            $weeklyDays = is_array($input['weekly_days'] ?? null) ? array_map('intval', $input['weekly_days']) : [];
            $monthlyOption = $input['monthly_option'] ?? 'day_of_month';

            $timePart = date('H:i:s', strtotime($startTime));
            $durationSeconds = max(300, $endDt->getTimestamp() - $startDt->getTimestamp());

            $currDate = new \DateTime($startsFromStr);
            $endDate = new \DateTime($endsOnStr);
            $endDate->setTime(23, 59, 59);

            if ($periodFrequency === 'Daily') {
                while ($currDate <= $endDate) {
                    $dtStartStr = $currDate->format('Y-m-d') . ' ' . $timePart;
                    $dtStart = new \DateTime($dtStartStr);
                    $dtEnd = clone $dtStart;
                    $dtEnd->modify("+{$durationSeconds} seconds");

                    $occurrences[] = [
                        'start' => $dtStart->format('Y-m-d H:i:s'),
                        'end' => $dtEnd->format('Y-m-d H:i:s')
                    ];

                    $currDate->modify("+{$repeatEvery} day");
                }
            } else if ($periodFrequency === 'Weekly') {
                if (empty($weeklyDays)) {
                    $weeklyDays = [intval($currDate->format('w'))];
                }

                $weeklyRotationDays = is_array($input['weekly_rotation_days'] ?? null) ? $input['weekly_rotation_days'] : [];

                // Calculate the Sunday of the start week as anchor
                $firstSunday = clone $currDate;
                $startDow = intval($firstSunday->format('w'));
                if ($startDow > 0) {
                    $firstSunday->modify("-{$startDow} days");
                }
                $firstSunday->setTime(0, 0, 0);

                while ($currDate <= $endDate) {
                    $dow = intval($currDate->format('w'));
                    $isMatch = false;

                    if (!empty($weeklyRotationDays)) {
                        $daysDiff = $firstSunday->diff($currDate)->days;
                        $weekNum = intval(floor($daysDiff / 7));
                        $rotWeekKey = strval(($weekNum % 4) + 1);
                        $allowedDows = is_array($weeklyRotationDays[$rotWeekKey] ?? null) ? array_map('intval', $weeklyRotationDays[$rotWeekKey]) : [];
                        $isMatch = in_array($dow, $allowedDows, true);
                    } else {
                        $isMatch = in_array($dow, $weeklyDays, true);
                    }

                    if ($isMatch) {
                        $dtStartStr = $currDate->format('Y-m-d') . ' ' . $timePart;
                        $dtStart = new \DateTime($dtStartStr);
                        $dtEnd = clone $dtStart;
                        $dtEnd->modify("+{$durationSeconds} seconds");

                        $occurrences[] = [
                            'start' => $dtStart->format('Y-m-d H:i:s'),
                            'end' => $dtEnd->format('Y-m-d H:i:s')
                        ];
                    }

                    // Advance by 1 day
                    $currDate->modify("+1 day");
                    if (empty($weeklyRotationDays) && $repeatEvery > 1 && intval($currDate->format('w')) === 0) {
                        $extraWeeks = $repeatEvery - 1;
                        if ($extraWeeks > 0) {
                            $currDate->modify("+{$extraWeeks} week");
                        }
                    }
                }
            } else if ($periodFrequency === 'Monthly') {
                $targetDayOfMonth = intval($currDate->format('d'));
                $targetDayOfWeek = intval($currDate->format('w'));
                $targetNthWeek = ceil($targetDayOfMonth / 7);

                while ($currDate <= $endDate) {
                    $year = intval($currDate->format('Y'));
                    $month = intval($currDate->format('m'));

                    if ($monthlyOption === 'day_of_week') {
                        $firstOfMonth = new \DateTime("{$year}-{$month}-01");
                        $firstDow = intval($firstOfMonth->format('w'));
                        $dayOffset = ($targetDayOfWeek - $firstDow + 7) % 7;
                        $targetDay = 1 + $dayOffset + (($targetNthWeek - 1) * 7);
                        $daysInMonth = intval($firstOfMonth->format('t'));
                        if ($targetDay > $daysInMonth) $targetDay -= 7;
                        $targetDateStr = sprintf('%04d-%02d-%02d', $year, $month, $targetDay);
                    } else {
                        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
                        $dayToUse = min($targetDayOfMonth, $daysInMonth);
                        $targetDateStr = sprintf('%04d-%02d-%02d', $year, $month, $dayToUse);
                    }

                    $dtStart = new \DateTime($targetDateStr . ' ' . $timePart);
                    if ($dtStart <= $endDate) {
                        $dtEnd = clone $dtStart;
                        $dtEnd->modify("+{$durationSeconds} seconds");

                        $occurrences[] = [
                            'start' => $dtStart->format('Y-m-d H:i:s'),
                            'end' => $dtEnd->format('Y-m-d H:i:s')
                        ];
                    }

                    $currDate->modify("+{$repeatEvery} month");
                }
            }
        } else if ($appointmentFor === 'Period' && $periodCount > 1) {
            $durationSeconds = $endDt->getTimestamp() - $startDt->getTimestamp();
            for ($i = 1; $i < $periodCount; $i++) {
                $nextStart = clone $startDt;
                if ($periodFrequency === 'Daily') {
                    $nextStart->modify("+{$i} day");
                } else if ($periodFrequency === 'Monthly') {
                    $nextStart->modify("+{$i} month");
                } else {
                    $nextStart->modify("+{$i} week");
                }
                $nextEnd = clone $nextStart;
                $nextEnd->modify("+{$durationSeconds} seconds");

                $occurrences[] = [
                    'start' => $nextStart->format('Y-m-d H:i:s'),
                    'end' => $nextEnd->format('Y-m-d H:i:s')
                ];
            }
        }

        if (empty($occurrences)) {
            $occurrences[] = [
                'start' => $startDt->format('Y-m-d H:i:s'),
                'end' => $endDt->format('Y-m-d H:i:s')
            ];
        }

        // Recurring preview: report each occurrence with conflict/blocked flags, insert nothing.
        if (!empty($input['dry_run'])) {
            $preview = [];
            foreach ($occurrences as $occ) {
                $flag = null;
                $flagReason = null;
                if ($category !== 'Waiting List') {
                    $dupe = Database::fetch(
                        "SELECT id FROM appointments
                         WHERE (patient_id = ? OR provider_id = ?) AND status NOT IN ('Cancelled', 'No Show') AND category != 'Waiting List'
                         AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))",
                        [$patientId, $providerId, $occ['start'], $occ['start'], $occ['end'], $occ['end'], $occ['start'], $occ['end']]
                    );
                    $blk = $this->blockedTimeConflict($providerId, $occ['start'], $occ['end']);
                    if ($dupe) { $flag = 'conflict'; $flagReason = 'Existing appointment overlaps'; }
                    elseif ($blk) { $flag = 'blocked'; $flagReason = $this->blockedMessage($blk, $occ['start']); }
                }
                $preview[] = ['start' => $occ['start'], 'end' => $occ['end'], 'flag' => $flag, 'reason' => $flagReason];
            }
            echo json_encode(['status' => 'success', 'dry_run' => true, 'occurrences' => $preview]);
            return;
        }

        $createdCount = 0;
        $firstId = null;

        foreach ($occurrences as $occ) {
            $oStart = $occ['start'];
            $oEnd = $occ['end'];

            // Provider blocked time (lunch / time off): reject a single booking, skip that date in a recurring series
            if ($category !== 'Waiting List') {
                $blockedRow = $this->blockedTimeConflict($providerId, $oStart, $oEnd);
                if ($blockedRow) {
                    if (count($occurrences) === 1) {
                        http_response_code(409);
                        echo json_encode(['status' => 'error', 'message' => 'Appointment Conflict: ' . $this->blockedMessage($blockedRow, $oStart)]);
                        return;
                    }
                    continue;
                }
            }

            // Check for double booking / overlapping appointment for the SAME patient OR SAME provider (Appointments only, skip for Waiting List)
            if ($category !== 'Waiting List') {
                $conflictSql = "SELECT * FROM appointments 
                                WHERE (patient_id = ? OR provider_id = ?) 
                                AND status NOT IN ('Cancelled', 'No Show')
                                AND category != 'Waiting List'
                                AND (
                                    (start_time <= ? AND end_time > ?) OR
                                    (start_time < ? AND end_time >= ?) OR
                                    (start_time >= ? AND end_time <= ?)
                                )";
                $conflict = Database::fetch($conflictSql, [$patientId, $providerId, $oStart, $oStart, $oEnd, $oEnd, $oStart, $oEnd]);

                if ($conflict && count($occurrences) === 1) {
                    $confStart = date('H:i', strtotime($conflict['start_time']));
                    $confEnd = date('H:i', strtotime($conflict['end_time']));
                    $confDate = date('Y-m-d', strtotime($conflict['start_time']));
                    $reason = ($conflict['patient_id'] == $patientId) ? 'patient' : 'clinician/provider';
                    http_response_code(409);
                    echo json_encode([
                        'status' => 'error',
                        'message' => "Appointment Conflict: This $reason already has an appointment scheduled on {$confDate} from {$confStart} to {$confEnd}. Duplicate booking prevented."
                    ]);
                    return;
                }
            } else {
                $conflict = false;
            }

            if (!$conflict) {
                $sql = "INSERT INTO appointments (patient_id, provider_id, start_time, end_time, notes, status, specialty, facility, category, visit_type, appointment_mode, appointment_for, period_frequency, period_count, message_to_patient, waiting_list_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                Database::query($sql, [$patientId, $providerId, $oStart, $oEnd, $notes, $status, $specialty, $facility, $category, $visitType, $appointmentMode, $appointmentFor, $periodFrequency, $periodCount, $messageToPatient, $waitingListData]);
                $newId = intval(Database::lastInsertId());
                if (!$firstId) $firstId = $newId;
                $createdCount++;

                // If appointment mode or visit type is Telehealth, automatically generate Jitsi room and send email to patient
                if (strtolower(trim($appointmentMode)) === 'telehealth' || strtolower(trim($visitType)) === 'telehealth') {
                    $telehealthRes = \App\Controllers\TelehealthController::createAndSendForAppointment($newId, intval($patientId), intval($providerId), $oStart, $oEnd);
                }
            }
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Create Appointment', 'Calendar', $firstId);

        $msg = ($createdCount > 1) ? "{$createdCount} recurring appointments scheduled successfully." : "Appointment scheduled successfully.";
        if (isset($telehealthRes)) {
            $msg .= ' ' . ($telehealthRes['message'] ?? '');
        }

        echo json_encode(['status' => 'success', 'message' => trim($msg), 'telehealth' => $telehealthRes ?? null, 'appointment_id' => $firstId ? (int)$firstId : null, 'created_count' => $createdCount]);
    }

    public function delete(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        // Fetch patient ID first for audit logger - also the facility ownership check (join to patients).
        $a = Database::fetch(
            "SELECT a.patient_id FROM appointments a JOIN patients p ON p.id = a.patient_id WHERE a.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$a) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Appointment not found.']);
            return;
        }
        $patientId = $a['patient_id'];

        $sql = "DELETE FROM appointments WHERE id = ?";
        Database::query($sql, [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Delete Appointment', 'Calendar', $id);

        echo json_encode(['status' => 'success', 'message' => 'Appointment deleted successfully.']);
    }

    public function update(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        $existing = Database::fetch(
            "SELECT a.* FROM appointments a JOIN patients p ON p.id = a.patient_id WHERE a.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Appointment not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $patientId = $input['patient_id'] ?? $existing['patient_id'];
        $providerId = $input['provider_id'] ?? $existing['provider_id'];

        // If the edit reassigns the patient or provider, the new one must still be the booking user's own facility.
        if ((string)$patientId !== (string)$existing['patient_id'] || (string)$providerId !== (string)$existing['provider_id']) {
            $fid = $_SESSION['facility_id'] ?? null;
            $patientOwned = Database::fetch("SELECT id FROM patients WHERE id = ? AND facility_id = ?", [$patientId, $fid]);
            $providerOwned = Database::fetch("SELECT id FROM users WHERE id = ? AND facility_id = ?", [$providerId, $fid]);
            if (!$patientOwned || !$providerOwned) {
                http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Patient or provider not found.']);
                return;
            }
        }
        $startTime = $input['start_time'] ?? $existing['start_time'];
        $endTime = $input['end_time'] ?? $existing['end_time'];
        $notes = $input['notes'] ?? $existing['notes'];
        $status = $input['status'] ?? $existing['status'];
        $specialty = $input['specialty'] ?? $existing['specialty'];
        $category = $input['category'] ?? $existing['category'];
        [$visitType, $visitTypeError] = VisitTypes::resolve($input['visit_type'] ?? null, $existing['visit_type'] ?? null);
        if ($visitTypeError) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $visitTypeError]);
            return;
        }
        $appointmentMode = $input['appointment_mode'] ?? $existing['appointment_mode'];
        $messageToPatient = $input['message_to_patient'] ?? $existing['message_to_patient'];
        // A waiting-list entry can be converted into a real booking by sending category = Appointment
        $category = $input['category'] ?? $existing['category'];
        if (array_key_exists('waiting_list_data', $input)) {
            $waitingListData = is_array($input['waiting_list_data']) ? json_encode($input['waiting_list_data']) : $input['waiting_list_data'];
        } else {
            $waitingListData = ($category === 'Waiting List') ? $existing['waiting_list_data'] : null;
        }

        if (!$patientId || !$providerId || empty($startTime) || empty($endTime)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required appointment parameters.']);
            return;
        }
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid appointment status.']);
            return;
        }
        // Waiting-list rows carry placeholder times (see the conflict-check comment below), so this only
        // applies to real bookings - a placeholder pair isn't guaranteed to satisfy end > start.
        if ($category !== 'Waiting List' && strtotime($endTime) <= strtotime($startTime)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
            return;
        }

        // Provider blocked time (lunch / time off) - real appointments only
        if ($category !== 'Waiting List') {
            $blockedRow = $this->blockedTimeConflict($providerId, $startTime, $endTime);
            if ($blockedRow) {
                http_response_code(409);
                echo json_encode(['status' => 'error', 'message' => 'Appointment Conflict: ' . $this->blockedMessage($blockedRow, $startTime)]);
                return;
            }
        }

        // Check for double booking / overlapping appointment for the SAME patient OR SAME provider (excluding current appointment id).
        // Waiting-list rows carry placeholder times, so they never count as conflicts (same rule as store()).
        $conflictSql = "SELECT * FROM appointments
                        WHERE id != ?
                        AND (patient_id = ? OR provider_id = ?)
                        AND status NOT IN ('Cancelled', 'No Show')
                        AND category != 'Waiting List'
                        AND (
                            (start_time <= ? AND end_time > ?) OR
                            (start_time < ? AND end_time >= ?) OR
                            (start_time >= ? AND end_time <= ?)
                        )";
        $conflict = ($category === 'Waiting List')
            ? false
            : Database::fetch($conflictSql, [$id, $patientId, $providerId, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);

        if ($conflict) {
            $confStart = date('H:i', strtotime($conflict['start_time']));
            $confEnd = date('H:i', strtotime($conflict['end_time']));
            $confDate = date('Y-m-d', strtotime($conflict['start_time']));
            $reason = ($conflict['patient_id'] == $patientId) ? 'patient' : 'clinician/provider';
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => "Appointment Conflict: This $reason already has an appointment scheduled on {$confDate} from {$confStart} to {$confEnd}. Duplicate booking prevented."
            ]);
            return;
        }

        // specialty and message_to_patient are real, independently-editable fields in the edit popup (Specialty
        // Type dropdown, patient-message textarea) - they used to be read from $input above but never written
        // here, so editing either silently appeared to succeed while the change was discarded.
        $sql = "UPDATE appointments SET patient_id = ?, provider_id = ?, start_time = ?, end_time = ?, notes = ?, status = ?, specialty = ?, appointment_mode = ?, visit_type = ?, category = ?, waiting_list_data = ?, message_to_patient = ? WHERE id = ?";
        Database::query($sql, [$patientId, $providerId, $startTime, $endTime, $notes, $status, $specialty, $appointmentMode, $visitType, $category, $waitingListData, $messageToPatient, $id]);

        if (strtolower(trim($appointmentMode)) === 'telehealth' || strtolower(trim($visitType)) === 'telehealth') {
            $telehealthRes = \App\Controllers\TelehealthController::createAndSendForAppointment(intval($id), intval($patientId), intval($providerId), $startTime, $endTime);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Update Appointment', 'Calendar', $id);

        $msg = 'Appointment updated successfully.';
        if (isset($telehealthRes)) {
            $msg .= ' ' . ($telehealthRes['message'] ?? '');
        }

        echo json_encode(['status' => 'success', 'message' => trim($msg), 'telehealth' => $telehealthRes ?? null, 'appointment_id' => (int)$id]);
    }

    public function updateStatus(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? 'Scheduled';

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid appointment status.']);
            return;
        }

        // A reason is only kept for Cancelled / No Show; any other status clears it
        $cancelReason = null;
        if (in_array($status, ['Cancelled', 'No Show'], true)) {
            $cancelReason = trim((string)($input['cancel_reason'] ?? ''));
            $cancelReason = $cancelReason !== '' ? mb_substr($cancelReason, 0, 255) : null;
        }

        $a = Database::fetch(
            "SELECT a.patient_id FROM appointments a JOIN patients p ON p.id = a.patient_id WHERE a.id = ? AND p.facility_id = ?",
            [$id, $_SESSION['facility_id'] ?? null]
        );
        if (!$a) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Appointment not found.']);
            return;
        }
        $patientId = $a['patient_id'];

        $sql = "UPDATE appointments SET status = ?, cancel_reason = ? WHERE id = ?";
        Database::query($sql, [$status, $cancelReason, $id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Update Appointment Status to ' . $status, 'Calendar', $id);

        echo json_encode(['status' => 'success', 'message' => 'Appointment status updated to ' . $status . '.']);
    }

    // ---- Provider blocked time (lunch / time off) ----

    /** The cardiology visit types + default minutes that feed every visit-type dropdown (see App\Support\VisitTypes). */
    public function visitTypes(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => VisitTypes::all()]);
    }

    public function listBlocks(): void {
        $this->checkAccess(Roles::ALL_STAFF);
        header('Content-Type: application/json');

        $role = $_SESSION['user_role'] ?? '';
        $isClinicianScoped = in_array($role, self::CLINICIAN_SCOPED, true);
        $facilityId = $_SESSION['facility_id'] ?? null;

        $conditions = [];
        $params = [];

        if ($facilityId) {
            $conditions[] = "(b.facility_id = ? OR b.facility_id IS NULL OR u.facility_id = ?)";
            $params[] = $facilityId;
            $params[] = $facilityId;
        }

        if ($isClinicianScoped) {
            $conditions[] = "b.provider_id = ?";
            $params[] = $_SESSION['user_id'] ?? 0;
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

        $rows = Database::fetchAll(
            "SELECT b.id, b.provider_id, b.block_date, b.weekday, b.category, b.facility_id, b.start_time, b.end_time, b.reason,
                    CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM provider_time_blocks b JOIN users u ON u.id = b.provider_id
             {$whereClause}
             ORDER BY b.block_date IS NULL, b.block_date, b.weekday, b.start_time",
            $params
        );
        echo json_encode(['status' => 'success', 'data' => $rows]);
    }

    public function storeBlock(): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $providerId = intval($input['provider_id'] ?? 0);
        $blockDate = !empty($input['block_date']) ? $input['block_date'] : null;
        // Weekly rows: `weekdays` (list, one row each) or a single legacy `weekday`
        $weekdays = [];
        if (isset($input['weekdays']) && is_array($input['weekdays'])) {
            foreach ($input['weekdays'] as $w) if ($w !== '' && $w !== null) $weekdays[] = intval($w);
        } elseif (isset($input['weekday']) && $input['weekday'] !== '' && $input['weekday'] !== null) {
            $weekdays[] = intval($input['weekday']);
        }
        $weekdays = array_values(array_unique($weekdays));
        $start = $input['start_time'] ?? '';
        $end = $input['end_time'] ?? '';
        $category = $input['category'] ?? 'Out Of Office';
        $facilityId = !empty($input['facility_id']) ? intval($input['facility_id']) : null;
        $reason = mb_substr(trim((string)($input['reason'] ?? '')), 0, 255);

        $validTime = function ($t) { return (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $t); };
        if (!$providerId || (($blockDate === null) === (count($weekdays) === 0)) || !$validTime($start) || !$validTime($end)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Provider, either a date or at least one weekday, and a valid start/end time are required.']);
            return;
        }
        if (!in_array($category, self::AVAILABILITY_CATEGORIES, true)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid category.']);
            return;
        }
        if ($blockDate !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $blockDate)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid block date.']);
            return;
        }
        foreach ($weekdays as $w) {
            if ($w < 0 || $w > 6) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Invalid weekday.']);
                return;
            }
        }
        if (strlen($start) === 5) $start .= ':00';
        if (strlen($end) === 5) $end .= ':00';
        if ($end <= $start) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'End time must be after start time.']);
            return;
        }

        $reasonVal = $reason !== '' ? $reason : null;
        $targets = $blockDate !== null ? [[$blockDate, null]] : array_map(fn($w) => [null, $w], $weekdays);
        $newId = 0;
        foreach ($targets as [$d, $w]) {
            Database::query(
                "INSERT INTO provider_time_blocks (provider_id, block_date, weekday, category, facility_id, start_time, end_time, reason, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$providerId, $d, $w, $category, $facilityId, $start, $end, $reasonVal, $_SESSION['user_id']]
            );
            $newId = intval(Database::lastInsertId());
            AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Create Provider Availability (' . $category . ')', 'Calendar', $newId);
        }
        echo json_encode(['status' => 'success', 'message' => 'Availability saved.', 'id' => $newId, 'count' => count($targets)]);
    }

    public function deleteBlock(array $params): void {
        $this->checkAccess(Roles::CARE_COORDINATION);
        header('Content-Type: application/json');
        $id = intval($params['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Block ID required.']);
            return;
        }
        Database::query("DELETE FROM provider_time_blocks WHERE id = ?", [$id]);
        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'Delete Provider Time Block', 'Calendar', $id);
        echo json_encode(['status' => 'success', 'message' => 'Blocked time removed.']);
    }
}
