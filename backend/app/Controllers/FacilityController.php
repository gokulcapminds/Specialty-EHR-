<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;

class FacilityController {
    private function checkAdminAccess(): void {
        $role = $_SESSION['user_role'] ?? '';
        if ($role !== 'Super Admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Administrator privileges required.']);
            exit();
        }
    }

    // Facilities are told apart by name in dropdowns (the code is not shown), so two may not share one (case-insensitive).
    private function nameTaken(string $name, ?int $exceptId = null): bool {
        $sql = "SELECT id FROM facilities WHERE LOWER(TRIM(facility_name)) = LOWER(?)";
        $args = [trim($name)];
        if ($exceptId) {
            $sql .= " AND id <> ?";
            $args[] = $exceptId;
        }
        return (bool)Database::fetch($sql, $args);
    }

    private function syncFacilitySpecialties(int $facilityId, array $specialtyIds): void {
        Database::query("DELETE FROM facility_specialties WHERE facility_id = ?", [$facilityId]);
        foreach ($specialtyIds as $specialtyId) {
            Database::query("INSERT INTO facility_specialties (facility_id, specialty_id) VALUES (?, ?)", [$facilityId, (int)$specialtyId]);
        }
    }

    // GET /api/facilities — list all enterprise facilities
    public function index(): void {
        header('Content-Type: application/json');
        try {
            $facilities = Database::fetchAll("
                SELECT
                    f.*,
                    (SELECT COUNT(*) FROM users u WHERE u.facility_id = f.id) AS provider_count
                FROM facilities f
                ORDER BY f.facility_name ASC
            ");

            $links = Database::fetchAll("SELECT facility_id, specialty_id FROM facility_specialties");
            $byFacility = [];
            foreach ($links as $l) {
                $byFacility[$l['facility_id']][] = (int)$l['specialty_id'];
            }
            foreach ($facilities as &$f) {
                $f['specialty_ids'] = $byFacility[$f['id']] ?? [];
            }

            echo json_encode(['status' => 'success', 'data' => $facilities ?: []]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to load facilities: ' . $e->getMessage()]);
        }
    }

    // GET /api/facilities/{id} — single facility details with locations
    public function show(array $params): void {
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Facility ID required.']); return; }

        try {
            $facility = Database::fetch("SELECT * FROM facilities WHERE id = ?", [$id]);
            if (!$facility) { http_response_code(404); echo json_encode(['status' => 'error', 'message' => 'Facility not found.']); return; }
            $specIds = Database::fetchAll("SELECT specialty_id FROM facility_specialties WHERE facility_id = ?", [$id]);
            $facility['specialty_ids'] = array_map(fn($r) => (int)$r['specialty_id'], $specIds);
            echo json_encode(['status' => 'success', 'data' => $facility]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // POST /api/facilities — create new facility
    public function store(): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['facility_name'] ?? '');
        if (empty($name)) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Facility Name is required.']); return; }
        $addrLine1 = trim($input['address_line1'] ?? $input['address'] ?? '');
        if (empty($addrLine1)) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Address Line 1 is required.']); return; }
        $city = trim($input['city'] ?? '');
        $state = trim($input['state'] ?? '');
        if (empty($city) || empty($state)) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'City and State are required.']); return; }

        $specialtyIds = array_values(array_unique(array_map('intval', $input['specialty_ids'] ?? [])));
        if (empty($specialtyIds)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Select at least one clinical specialty this facility practices.']);
            return;
        }

        if ($this->nameTaken($name)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "A facility named '{$name}' already exists. Use a different name."]);
            return;
        }

        try {
            $code         = trim($input['facility_code'] ?? '') ?: 'FAC-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 3)) . '-' . rand(10, 99);
            $type         = trim($input['facility_type'] ?? 'Clinic');
            $legalName    = trim($input['legal_entity_name'] ?? $name);
            $taxId        = trim($input['tax_id_ein'] ?? '');
            $npi          = trim($input['npi'] ?? '');
            if (!empty($npi) && !preg_match('/^\d{10}$/', $npi)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'NPI must be exactly 10 digits.']);
                return;
            }
            $phone        = trim($input['phone'] ?? '');
            $fax          = trim($input['fax'] ?? '');
            $email        = trim($input['email'] ?? '');
            $website      = trim($input['website'] ?? '');
            $addrLine2    = trim($input['address_line2'] ?? '');
            $postalCode   = trim($input['postal_code'] ?? $input['zip_code'] ?? '');
            $country      = trim($input['country'] ?? 'United States');
            $timezone     = trim($input['timezone'] ?? 'America/New_York');
            $contactPerson= trim($input['contact_person'] ?? '');
            $desc         = trim($input['description'] ?? '');
            $isActive     = isset($input['is_active']) ? (int)$input['is_active'] : 1;

            Database::query("
                INSERT INTO facilities (
                    facility_name, facility_code, facility_type, legal_entity_name, tax_id_ein, npi,
                    phone, fax, email, website,
                    address_line1, address_line2, address, city, state, postal_code, zip_code, country,
                    timezone, contact_person, description, is_active
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                $name, $code, $type, $legalName, $taxId, $npi,
                $phone, $fax, $email, $website,
                $addrLine1, $addrLine2, $addrLine1, $city, $state, $postalCode, $postalCode, $country,
                $timezone, $contactPerson, $desc, $isActive
            ]);

            $newId = Database::lastInsertId();
            $this->syncFacilitySpecialties($newId, $specialtyIds);

            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, "Create Facility: {$name}", 'Administration', isset($newId) ? (string)$newId : null);
            }

            echo json_encode(['status' => 'success', 'message' => 'Facility created successfully.', 'id' => $newId]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to create facility: ' . $e->getMessage()]);
        }
    }

    // PUT /api/facilities/{id} — update facility
    public function update(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Facility ID required.']); return; }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $name = trim($input['facility_name'] ?? '');
        if (empty($name)) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Facility Name is required.']); return; }

        $specialtyIds = array_values(array_unique(array_map('intval', $input['specialty_ids'] ?? [])));
        if (empty($specialtyIds)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Select at least one clinical specialty this facility practices.']);
            return;
        }

        if ($this->nameTaken($name, (int)$id)) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "A facility named '{$name}' already exists. Use a different name."]);
            return;
        }

        try {
            $code         = trim($input['facility_code'] ?? '');
            $type         = trim($input['facility_type'] ?? 'Clinic');
            $legalName    = trim($input['legal_entity_name'] ?? $name);
            $taxId        = trim($input['tax_id_ein'] ?? '');
            $npi          = trim($input['npi'] ?? '');
            if (!empty($npi) && !preg_match('/^\d{10}$/', $npi)) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'NPI must be exactly 10 digits.']);
                return;
            }
            $phone        = trim($input['phone'] ?? '');
            $fax          = trim($input['fax'] ?? '');
            $email        = trim($input['email'] ?? '');
            $website      = trim($input['website'] ?? '');
            $addrLine1    = trim($input['address_line1'] ?? $input['address'] ?? '');
            $addrLine2    = trim($input['address_line2'] ?? '');
            $city         = trim($input['city'] ?? '');
            $state        = trim($input['state'] ?? '');
            $postalCode   = trim($input['postal_code'] ?? $input['zip_code'] ?? '');
            $country      = trim($input['country'] ?? 'United States');
            $timezone     = trim($input['timezone'] ?? 'America/New_York');
            $contactPerson= trim($input['contact_person'] ?? '');
            $desc         = trim($input['description'] ?? '');
            $isActive     = isset($input['is_active']) ? (int)$input['is_active'] : 1;

            Database::query("
                UPDATE facilities SET
                    facility_name = ?, facility_code = ?, facility_type = ?,
                    legal_entity_name = ?, tax_id_ein = ?, npi = ?,
                    phone = ?, fax = ?, email = ?, website = ?,
                    address_line1 = ?, address_line2 = ?, address = ?, city = ?, state = ?,
                    postal_code = ?, zip_code = ?, country = ?,
                    timezone = ?, contact_person = ?, description = ?, is_active = ?, updated_at = NOW()
                WHERE id = ?
            ", [
                $name, $code, $type,
                $legalName, $taxId, $npi,
                $phone, $fax, $email, $website,
                $addrLine1, $addrLine2, $addrLine1, $city, $state,
                $postalCode, $postalCode, $country,
                $timezone, $contactPerson, $desc, $isActive, $id
            ]);

            $this->syncFacilitySpecialties((int)$id, $specialtyIds);

            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, "Update Facility: {$name}", 'Administration', (string)$id);
            }

            echo json_encode(['status' => 'success', 'message' => 'Facility updated successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update facility: ' . $e->getMessage()]);
        }
    }

    // DELETE /api/facilities/{id} — delete facility
    public function delete(array $params): void {
        $this->checkAdminAccess();
        header('Content-Type: application/json');
        $id = $params['id'] ?? null;
        if (!$id) { http_response_code(400); echo json_encode(['status' => 'error', 'message' => 'Facility ID required.']); return; }

        // Staff have no foreign key to their facility, so without this check they would silently point at a facility that no longer exists.
        $staff = (int)Database::fetch("SELECT COUNT(*) AS n FROM users WHERE facility_id = ?", [$id])['n'];
        if ($staff > 0) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "Cannot delete this facility: {$staff} staff member(s) are assigned to it. Reassign them to another facility first."]);
            return;
        }

        try {
            Database::query("DELETE FROM facilities WHERE id = ?", [$id]);
            if (class_exists('App\Services\AuditLogger')) {
                AuditLogger::log($_SESSION['user_id'] ?? null, $_SESSION['username'] ?? null, $_SESSION['user_role'] ?? null, null, 'Delete Facility', 'Administration', (string)$id);
            }
            echo json_encode(['status' => 'success', 'message' => 'Facility deleted successfully.']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete facility: ' . $e->getMessage()]);
        }
    }
}
