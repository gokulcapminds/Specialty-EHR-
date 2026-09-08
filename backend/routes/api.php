<?php
/**
 * @var \Routes\Router $router
 */

use App\Controllers\AuthController;
use App\Controllers\PatientController;
use App\Controllers\CalendarController;
use App\Controllers\ClinicalController;
use App\Controllers\DocumentController;
use App\Controllers\MessagingController;
use App\Controllers\BillingController;
use App\Controllers\IntakeController;
use App\Controllers\ReferralController;
use App\Controllers\RecallController;
use App\Controllers\TelehealthController;

use App\Middleware\AuthenticationMiddleware;
use App\Middleware\CSRFMiddleware;
use App\Middleware\RBACMiddleware;
use App\Middleware\RateLimitingMiddleware;

// Auth Routes
$router->post('/api/login', AuthController::class . '@login', [RateLimitingMiddleware::class]);
$router->post('/api/logout', AuthController::class . '@logout');
$router->get('/api/me', AuthController::class . '@me', [AuthenticationMiddleware::class]);

// Patient Routes
$router->get('/api/patients', PatientController::class . '@index', [AuthenticationMiddleware::class]);
$router->get('/api/patients/{id}', PatientController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/patient/{id}', PatientController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/providers', PatientController::class . '@providers', [AuthenticationMiddleware::class]);
$router->get('/api/patients/providers', PatientController::class . '@providers', [AuthenticationMiddleware::class]);
$router->post('/api/patient', PatientController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patient/{id}', PatientController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patients/{id}', PatientController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patient/{id}/insurance', PatientController::class . '@updateInsurance', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patients/{id}/insurance', PatientController::class . '@updateInsurance', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/patient/{id}', PatientController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Patient Intake Routes
$router->get('/api/intake/view', IntakeController::class . '@getByToken');
$router->post('/api/intake/submit', IntakeController::class . '@submit');
$router->post('/api/intake/send', IntakeController::class . '@send', [AuthenticationMiddleware::class]);
$router->get('/api/intake/patient/{patient_id}', IntakeController::class . '@getByPatientId', [AuthenticationMiddleware::class]);
$router->get('/api/intake/notifications', IntakeController::class . '@notifications', [AuthenticationMiddleware::class]);
$router->get('/api/notifications', \App\Controllers\NotificationController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/intake/approve/{id}', IntakeController::class . '@approve', [AuthenticationMiddleware::class]);

// Calendar Routes
$router->get('/api/appointments', CalendarController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/appointment', CalendarController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/appointment/{id}', CalendarController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/appointment/{id}/status', CalendarController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/appointment/{id}', CalendarController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Clinical Routes
$router->post('/api/clinical/notes', ClinicalController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/clinical/notes/{patient_id}', ClinicalController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/clinical/note-single/{id}', ClinicalController::class . '@getSingleNote', [AuthenticationMiddleware::class]);
$router->put('/api/clinical/notes/{id}', ClinicalController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/clinical/notes/{id}', ClinicalController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/clinical/icd10-search', ClinicalController::class . '@searchIcd10', [AuthenticationMiddleware::class]);
// Phase 2 — Core Encounter lifecycle routes
$router->post('/api/clinical/notes/{id}/sign', ClinicalController::class . '@signAndLock', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/clinical/notes/{id}/addendum', ClinicalController::class . '@addAddendum', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/clinical/notes/{id}/status', ClinicalController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Document Routes
$router->post('/api/documents/upload', DocumentController::class . '@upload', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/documents/download/{id}', DocumentController::class . '@download', [AuthenticationMiddleware::class]);
$router->get('/api/documents/{patient_id}', DocumentController::class . '@index', [AuthenticationMiddleware::class]);
$router->delete('/api/documents/{id}', DocumentController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Referral Routes
$router->get('/api/referrals', ReferralController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/referrals/{patient_id}', ReferralController::class . '@index', [AuthenticationMiddleware::class]);
$router->get('/api/referrals/{id}/document', ReferralController::class . '@downloadDocument');
$router->post('/api/referrals', ReferralController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/referrals/{id}', ReferralController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/referrals/{id}', ReferralController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/referrals/{id}', ReferralController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/referrals/{id}/accept', ReferralController::class . '@accept', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/referrals/{id}/reject', ReferralController::class . '@reject', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Recall Routes
$router->get('/api/recalls/workspace', RecallController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/recalls/export', RecallController::class . '@exportCsv', [AuthenticationMiddleware::class]);
$router->get('/api/recalls/{patient_id}', RecallController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/recalls', RecallController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/recalls/{id}/status', RecallController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/recalls/{id}/status', RecallController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/recalls/{id}/attempt', RecallController::class . '@logAttempt', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/recalls/{id}/snooze', RecallController::class . '@snooze', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/recalls/{id}', RecallController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/recalls/{id}', RecallController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/recalls/{id}', RecallController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Messaging Routes (Chat UI & Patient Chart)
$router->get('/api/messages/conversations', MessagingController::class . '@conversations', [AuthenticationMiddleware::class]);
$router->get('/api/messages/unread-count', MessagingController::class . '@unreadCount', [AuthenticationMiddleware::class]);
$router->get('/api/messages/staff-list', MessagingController::class . '@staffList', [AuthenticationMiddleware::class]);
$router->get('/api/messages/chat/{id}', MessagingController::class . '@chat', [AuthenticationMiddleware::class]);
$router->post('/api/messages/chat/send', MessagingController::class . '@sendChat', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/messages/patient/{patient_id}', MessagingController::class . '@patientMessages', [AuthenticationMiddleware::class]);
$router->post('/api/messages/patient', MessagingController::class . '@sendPatientMessage', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/messages/patient/{id}', MessagingController::class . '@deletePatientMessage', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Billing Routes
$router->get('/api/billing/unbilled-encounters', BillingController::class . '@unbilledEncounters', [AuthenticationMiddleware::class]);
$router->get('/api/billing/encounter/{id}/charges', BillingController::class . '@encounterCharges', [AuthenticationMiddleware::class]);
$router->get('/api/billing/cpt-codes', BillingController::class . '@cptCodes', [AuthenticationMiddleware::class]);
$router->post('/api/billing/cpt-codes', BillingController::class . '@storeCptCode', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/invoices', BillingController::class . '@storeInvoice', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/billing/invoices', BillingController::class . '@listInvoices', [AuthenticationMiddleware::class]);
$router->get('/api/billing/invoice/{id}', BillingController::class . '@getInvoice', [AuthenticationMiddleware::class]);
$router->put('/api/billing/invoice/{id}/payment', BillingController::class . '@recordPayment', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/billing/invoice/{id}', BillingController::class . '@deleteInvoice', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Legacy claims routes (kept for compatibility)
$router->get('/api/billing/claims', BillingController::class . '@listInvoices', [AuthenticationMiddleware::class]);
$router->post('/api/billing/claims', BillingController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Phase 2 — Billing handoff queue (signed/locked encounters ready for charge capture)
$router->get('/api/billing/queue', BillingController::class . '@billingQueue', [AuthenticationMiddleware::class]);

// User Administration & RBAC Routes
$router->get('/api/users', \App\Controllers\UserController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/user', \App\Controllers\UserController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/user/{id}', \App\Controllers\UserController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/user/{id}', \App\Controllers\UserController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

$router->get('/api/rbac/policies', \App\Controllers\UserController::class . '@getRbacPolicies', [AuthenticationMiddleware::class]);
$router->put('/api/rbac/policies', \App\Controllers\UserController::class . '@updateRbacPolicy', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// System Settings Routes
$router->get('/api/settings', \App\Controllers\SettingsController::class . '@get', [AuthenticationMiddleware::class]);
$router->post('/api/settings', \App\Controllers\SettingsController::class . '@save', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Telehealth Routes
$router->get('/api/telehealth/sessions', TelehealthController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/telehealth/session', TelehealthController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/telehealth/session/{id}/resend', TelehealthController::class . '@resendLink', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/telehealth/session/{id}/status', TelehealthController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Reports & Audit Logs Route
$router->get('/api/reports/audit', \App\Controllers\AuditController::class . '@index', [AuthenticationMiddleware::class]);
