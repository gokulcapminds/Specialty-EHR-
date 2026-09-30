<?php
namespace App\Controllers;

use App\Models\Database;
use App\Services\AuditLogger;
use App\Services\EncryptionService;

class OrderController {
    private function checkAccess(): void {
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthenticated session.', 'authenticated' => false]);
            exit();
        }
    }

    private function decoratePatientName(array &$row): void {
        $row['patient_first_name'] = !empty($row['first_name_encrypted']) ? EncryptionService::decrypt($row['first_name_encrypted']) : 'Patient';
        $row['patient_last_name'] = !empty($row['last_name_encrypted']) ? EncryptionService::decrypt($row['last_name_encrypted']) : ('#' . $row['patient_id']);
        unset($row['first_name_encrypted'], $row['last_name_encrypted']);
    }

    // GET /api/orders/workspace — cross-patient order queue
    public function all(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $userId = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'] ?? 'Doctor';

        $where = [];
        $params = [];

        if (!in_array($userRole, ['Super Admin', 'Admin', 'Staff', 'Billing Staff', 'Receptionist'])) {
            $where[] = "(o.provider_id = ? OR o.provider_id IS NULL)";
            $params[] = $userId;
        }

        foreach (['status', 'order_type', 'specialty', 'patient_id'] as $filterKey) {
            if (!empty($_GET[$filterKey])) {
                $where[] = "o.$filterKey = ?";
                $params[] = $_GET[$filterKey];
            }
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $orders = Database::fetchAll(
            "SELECT o.*,
                    p.first_name_encrypted, p.last_name_encrypted,
                    CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM orders o
             LEFT JOIN patients p ON o.patient_id = p.id
             LEFT JOIN users u ON o.provider_id = u.id
             $whereSql
             ORDER BY o.ordered_at DESC, o.id DESC",
            $params
        );

        foreach ($orders as &$order) {
            $this->decoratePatientName($order);
        }

        echo json_encode(['status' => 'success', 'data' => $orders]);
    }

    // GET /api/orders/{patient_id} — one patient's orders
    public function index(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $patientId = $params['patient_id'] ?? null;
        if (!$patientId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient ID required.']);
            return;
        }

        $where = ["o.patient_id = ?"];
        $queryParams = [$patientId];
        if (!empty($_GET['encounter_id'])) {
            $where[] = "o.encounter_id = ?";
            $queryParams[] = $_GET['encounter_id'];
        }

        $orders = Database::fetchAll(
            "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS provider_name
             FROM orders o
             LEFT JOIN users u ON o.provider_id = u.id
             WHERE " . implode(" AND ", $where) . "
             ORDER BY o.ordered_at DESC, o.id DESC",
            $queryParams
        );

        echo json_encode(['status' => 'success', 'data' => $orders]);
    }

    // POST /api/orders — place a new order
    public function store(): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $patientId = $input['patient_id'] ?? null;
        $providerId = $input['provider_id'] ?? null;
        $orderType = $input['order_type'] ?? '';
        $orderName = trim($input['order_name'] ?? '');

        if (empty($patientId) || empty($providerId) || empty($orderType) || empty($orderName)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Patient, provider, order type, and order name/test are required.']);
            return;
        }

        $specialty = $input['specialty'] ?? 'General';
        $orderCode = $input['order_code'] ?? null;
        $priority = $input['priority'] ?? 'Routine';
        $clinicalIndication = $input['clinical_indication'] ?? null;
        $specialInstructions = $input['special_instructions'] ?? null;
        $encounterId = !empty($input['encounter_id']) ? intval($input['encounter_id']) : null;

        $lastOrder = Database::fetch("SELECT MAX(id) AS max_id FROM orders");
        $nextId = ($lastOrder['max_id'] ?? 0) + 1;
        $orderNumber = 'ORD-' . str_pad((string)$nextId, 5, '0', STR_PAD_LEFT);

        Database::query(
            "INSERT INTO orders (order_number, patient_id, provider_id, encounter_id, specialty, order_type, order_code, order_name, priority, status, clinical_indication, special_instructions, ordered_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Submitted', ?, ?, NOW())",
            [$orderNumber, $patientId, $providerId, $encounterId, $specialty, $orderType, $orderCode, $orderName, $priority, $clinicalIndication, $specialInstructions]
        );
        $newId = Database::lastInsertId();

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $patientId, "Order Placed: {$orderNumber} ({$orderType} - {$orderName})", 'Orders', (string)$newId);

        echo json_encode(['status' => 'success', 'message' => 'Order placed successfully.', 'id' => $newId, 'order_number' => $orderNumber]);
    }

    // PUT /api/orders/{id}/status — generic status transition (In-Progress, Cancelled)
    public function updateStatus(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $id = $params['id'] ?? null;
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $status = $input['status'] ?? null;

        $allowed = ['Draft', 'Submitted', 'In-Progress', 'Cancelled'];
        if (!$id || !$status || !in_array($status, $allowed)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Order ID and a valid status are required.']);
            return;
        }

        $order = Database::fetch("SELECT id, patient_id, order_number, status FROM orders WHERE id = ?", [$id]);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            return;
        }
        if (in_array($order['status'], ['Reviewed', 'Cancelled'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "Order {$order['order_number']} is {$order['status']} and can no longer be changed."]);
            return;
        }

        Database::query("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?", [$status, $id]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $order['patient_id'], "Order Status Changed: {$order['order_number']} -> {$status}", 'Orders', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "Order {$order['order_number']} marked {$status}."]);
    }

    // POST /api/orders/{id}/review — provider reviews & signs off
    public function reviewOrder(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $userRole = $_SESSION['user_role'] ?? '';
        if (!in_array($userRole, ['Doctor', 'Therapist', 'Super Admin'])) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Only a provider can review and sign off on results.']);
            return;
        }

        $id = $params['id'] ?? null;
        $order = Database::fetch("SELECT id, patient_id, order_number, status FROM orders WHERE id = ?", [$id]);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            return;
        }
        if ($order['status'] !== 'Resulted') {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "Order {$order['order_number']} has no pending result to review."]);
            return;
        }

        $userId = $_SESSION['user_id'];

        Database::query(
            "UPDATE orders SET status = 'Reviewed', reviewed_by = ?, reviewed_at = NOW(), updated_at = NOW() WHERE id = ?",
            [$userId, $id]
        );
        Database::query(
            "UPDATE results SET signed_by = ?, signed_at = NOW(), updated_at = NOW() WHERE order_id = ? AND signed_by IS NULL",
            [$userId, $id]
        );

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $order['patient_id'], "Order Reviewed & Signed: {$order['order_number']}", 'Orders', (string)$id);

        echo json_encode(['status' => 'success', 'message' => "Order {$order['order_number']} reviewed and signed."]);
    }

    // GET /api/orders/{id}/results
    public function getResults(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $orderId = $params['id'] ?? null;
        $results = Database::fetchAll(
            "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS signed_by_name
             FROM results r
             LEFT JOIN users u ON r.signed_by = u.id
             WHERE r.order_id = ?
             ORDER BY r.created_at DESC, r.id DESC",
            [$orderId]
        );

        echo json_encode(['status' => 'success', 'data' => $results]);
    }

    // POST /api/orders/{id}/results — enter a result against an order
    public function storeResult(array $params): void {
        $this->checkAccess();
        header('Content-Type: application/json');

        $orderId = $params['id'] ?? null;
        $order = Database::fetch("SELECT id, patient_id, order_number, order_type, status FROM orders WHERE id = ?", [$orderId]);
        if (!$order) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
            return;
        }
        if (in_array($order['status'], ['Reviewed', 'Cancelled'])) {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => "Order {$order['order_number']} is {$order['status']} and cannot receive new results."]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $testName = trim($input['test_name'] ?? '');
        $resultValue = trim($input['result_value'] ?? '');

        if (empty($testName) || empty($resultValue)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Test name and result value are required.']);
            return;
        }

        $resultType = $input['result_type'] ?? $order['order_type'];
        $units = $input['units'] ?? null;
        $referenceRange = $input['reference_range'] ?? null;
        $abnormalFlag = $input['abnormal_flag'] ?? 'Normal';
        $resultStatus = $input['status'] ?? 'Final';
        $performingLab = $input['performing_lab'] ?? null;
        $providerNotes = $input['provider_notes'] ?? null;

        Database::query(
            "INSERT INTO results (order_id, patient_id, result_type, test_name, result_value, units, reference_range, abnormal_flag, status, performing_lab, provider_notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$orderId, $order['patient_id'], $resultType, $testName, $resultValue, $units, $referenceRange, $abnormalFlag, $resultStatus, $performingLab, $providerNotes]
        );
        $newResultId = Database::lastInsertId();

        Database::query("UPDATE orders SET status = 'Resulted', resulted_at = NOW(), updated_at = NOW() WHERE id = ?", [$orderId]);

        AuditLogger::log($_SESSION['user_id'], $_SESSION['username'], $_SESSION['user_role'], $order['patient_id'], "Result Entered: {$order['order_number']} - {$testName}", 'Orders', (string)$orderId);

        echo json_encode(['status' => 'success', 'message' => 'Result recorded.', 'id' => $newResultId]);
    }
}
