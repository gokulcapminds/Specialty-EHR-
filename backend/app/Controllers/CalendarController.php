<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\EncryptionService;
use App\Services\AuditLogger;

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

    public function index(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist', 'Billing Staff']);
        header('Content-Type: application/json');

        $sql = "SELECT a.*, p.first_name_encrypted, p.last_name_encrypted, u.first_name as doc_first, u.last_name as doc_last 
                FROM appointments a
                JOIN patients p ON a.patient_id = p.id
                JOIN users u ON a.provider_id = u.id
                ORDER BY a.start_time ASC";
        
        $appointments = Database::fetchAll($sql);

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
                'visit_type' => $a['visit_type'] ?? 'Family Care',
                'appointment_mode' => $a['appointment_mode'] ?? 'In Person',
                'appointment_for' => $a['appointment_for'] ?? 'Single Date',
                'period_frequency' => $a['period_frequency'] ?? null,
                'period_count' => $a['period_count'] ?? 1,
                'message_to_patient' => $a['message_to_patient'] ?? null,
                'waiting_list_data' => $a['waiting_list_data'] ?? null
            ];
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], null, 'View Appointment Calendar', 'Calendar');

        echo json_encode(['status' => 'success', 'data' => $result]);
    }

    public function store(): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
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
        $visitType = $input['visit_type'] ?? 'Family Care';
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

        // Validate date format
        try {
            $startDt = new \DateTime($startTime);
            $endDt = new \DateTime($endTime);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid date format.']);
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

        $createdCount = 0;
        $firstId = null;

        foreach ($occurrences as $occ) {
            $oStart = $occ['start'];
            $oEnd = $occ['end'];

            // Check for double booking / overlapping appointment for the SAME patient OR SAME provider (Appointments only, skip for Waiting List)
            if ($category !== 'Waiting List') {
                $conflictSql = "SELECT * FROM appointments 
                                WHERE (patient_id = ? OR provider_id = ?) 
                                AND status != 'Cancelled'
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
                    $telehealthRes = \App\Controllers\TelehealthController::createAndSendForAppointment($newId, intval($patientId), intval($providerId), $oStart);
                }
            }
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Create Appointment', 'Calendar', $firstId);

        $msg = ($createdCount > 1) ? "{$createdCount} recurring appointments scheduled successfully." : "Appointment scheduled successfully.";
        if (isset($telehealthRes)) {
            $msg .= ' ' . ($telehealthRes['message'] ?? '');
        }

        echo json_encode(['status' => 'success', 'message' => trim($msg), 'telehealth' => $telehealthRes ?? null]);
    }

    public function delete(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        // Fetch patient ID first for audit logger
        $a = Database::fetch("SELECT patient_id FROM appointments WHERE id = ?", [$id]);
        $patientId = $a ? $a['patient_id'] : null;

        $sql = "DELETE FROM appointments WHERE id = ?";
        Database::query($sql, [$id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Delete Appointment', 'Calendar', $id);

        echo json_encode(['status' => 'success', 'message' => 'Appointment deleted successfully.']);
    }

    public function update(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        $existing = Database::fetch("SELECT * FROM appointments WHERE id = ?", [$id]);
        if (!$existing) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Appointment not found.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $patientId = $input['patient_id'] ?? $existing['patient_id'];
        $providerId = $input['provider_id'] ?? $existing['provider_id'];
        $startTime = $input['start_time'] ?? $existing['start_time'];
        $endTime = $input['end_time'] ?? $existing['end_time'];
        $notes = $input['notes'] ?? $existing['notes'];
        $status = $input['status'] ?? $existing['status'];
        $specialty = $input['specialty'] ?? $existing['specialty'];
        $category = $input['category'] ?? $existing['category'];
        $visitType = $input['visit_type'] ?? $existing['visit_type'];
        $appointmentMode = $input['appointment_mode'] ?? $existing['appointment_mode'];
        $messageToPatient = $input['message_to_patient'] ?? $existing['message_to_patient'];

        if (!$patientId || !$providerId || empty($startTime) || empty($endTime)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing required appointment parameters.']);
            return;
        }

        // Check for double booking / overlapping appointment for the SAME patient OR SAME provider (excluding current appointment id)
        $conflictSql = "SELECT * FROM appointments 
                        WHERE id != ?
                        AND (patient_id = ? OR provider_id = ?) 
                        AND status != 'Cancelled'
                        AND (
                            (start_time <= ? AND end_time > ?) OR
                            (start_time < ? AND end_time >= ?) OR
                            (start_time >= ? AND end_time <= ?)
                        )";
        $conflict = Database::fetch($conflictSql, [$id, $patientId, $providerId, $startTime, $startTime, $endTime, $endTime, $startTime, $endTime]);

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

        $sql = "UPDATE appointments SET patient_id = ?, provider_id = ?, start_time = ?, end_time = ?, notes = ?, status = ?, appointment_mode = ?, visit_type = ? WHERE id = ?";
        Database::query($sql, [$patientId, $providerId, $startTime, $endTime, $notes, $status, $appointmentMode, $visitType, $id]);

        if (strtolower(trim($appointmentMode)) === 'telehealth' || strtolower(trim($visitType)) === 'telehealth') {
            $telehealthRes = \App\Controllers\TelehealthController::createAndSendForAppointment(intval($id), intval($patientId), intval($providerId), $startTime);
        }

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Update Appointment', 'Calendar', $id);

        $msg = 'Appointment updated successfully.';
        if (isset($telehealthRes)) {
            $msg .= ' ' . ($telehealthRes['message'] ?? '');
        }

        echo json_encode(['status' => 'success', 'message' => trim($msg), 'telehealth' => $telehealthRes ?? null]);
    }

    public function updateStatus(array $params): void {
        $this->checkAccess(['Super Admin', 'Doctor', 'Therapist', 'Nurse', 'Receptionist']);
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Appointment ID required.']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? 'Scheduled';

        $a = Database::fetch("SELECT patient_id FROM appointments WHERE id = ?", [$id]);
        $patientId = $a ? $a['patient_id'] : null;

        $sql = "UPDATE appointments SET status = ? WHERE id = ?";
        Database::query($sql, [$status, $id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, 'Update Appointment Status to ' . $status, 'Calendar', $id);

        echo json_encode(['status' => 'success', 'message' => 'Appointment status updated to ' . $status . '.']);
    }
}
