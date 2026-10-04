<?php
/**
 * @var \Routes\Router $router
 */

use App\Controllers\AuthController;
use App\Controllers\PatientController;
use App\Controllers\CalendarController;
use App\Controllers\ClinicalController;
use App\Controllers\DocumentController;
use App\Controllers\ImagingController;
use App\Controllers\MessagingController;
use App\Controllers\BillingController;
use App\Controllers\ClaimController;
use App\Controllers\ClaimEdiController;
use App\Controllers\EncounterController;
use App\Controllers\IntakeController;
use App\Controllers\ReferralController;
use App\Controllers\RecallController;
use App\Controllers\OrderController;
use App\Controllers\MedicationController;
use App\Controllers\ProblemController;
use App\Controllers\AllergyController;
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
$router->get('/api/patients/list', PatientController::class . '@listView', [AuthenticationMiddleware::class]);
$router->get('/api/patients/{id}', PatientController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/patient/{id}', PatientController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/providers', PatientController::class . '@providers', [AuthenticationMiddleware::class]);
$router->get('/api/patients/providers', PatientController::class . '@providers', [AuthenticationMiddleware::class]);
$router->post('/api/patient', PatientController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patient/{id}', PatientController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patients/{id}', PatientController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patient/{id}/status', PatientController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/patients/{id}/status', PatientController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
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
$router->get('/api/visit-types', CalendarController::class . '@visitTypes', [AuthenticationMiddleware::class]);
$router->get('/api/provider-blocks', CalendarController::class . '@listBlocks', [AuthenticationMiddleware::class]);
$router->post('/api/provider-blocks', CalendarController::class . '@storeBlock', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/provider-blocks/{id}', CalendarController::class . '@deleteBlock', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Dashboard Routes
$router->get('/api/patient/{id}/dashboard', \App\Controllers\DashboardController::class . '@patient', [AuthenticationMiddleware::class]);
$router->get('/api/dashboard/summary', \App\Controllers\DashboardController::class . '@summary', [AuthenticationMiddleware::class]);
$router->get('/api/dashboard/doctor-appointments', CalendarController::class . '@doctorAppointmentsThisMonth', [AuthenticationMiddleware::class]);
$router->get('/api/dashboard/doctor-appointments-monthly', CalendarController::class . '@doctorAppointmentsMonthWise', [AuthenticationMiddleware::class]);

// Clinical Routes
$router->post('/api/clinical/notes', ClinicalController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/clinical/notes/{patient_id}', ClinicalController::class . '@show', [AuthenticationMiddleware::class]);
$router->get('/api/clinical/note-single/{id}', ClinicalController::class . '@getSingleNote', [AuthenticationMiddleware::class]);
$router->put('/api/clinical/notes/{id}', ClinicalController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/clinical/notes/{id}', ClinicalController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/clinical/icd10-search', ClinicalController::class . '@searchIcd10', [AuthenticationMiddleware::class]);
// C04 Cardiac History (patient-level risk profile behind the cardiac risk scores). Under /api/clinical/ so RouteAreas maps it to `encounters`.
$router->get('/api/clinical/cardiac-profile/{patient_id}', \App\Controllers\CardiacProfileController::class . '@show', [AuthenticationMiddleware::class]);
$router->put('/api/clinical/cardiac-profile/{patient_id}', \App\Controllers\CardiacProfileController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Phase 2 — Core Encounter lifecycle routes
$router->get('/api/encounters/queue', EncounterController::class . '@queue', [AuthenticationMiddleware::class]);
$router->post('/api/encounters/start', EncounterController::class . '@start', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/clinical/notes/{id}/sign', ClinicalController::class . '@signAndLock', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/clinical/notes/{id}/addendum', ClinicalController::class . '@addAddendum', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/clinical/notes/{id}/status', ClinicalController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Document Routes
$router->post('/api/documents/upload', DocumentController::class . '@upload', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/documents/download/{id}', DocumentController::class . '@download', [AuthenticationMiddleware::class]);
$router->get('/api/documents/{patient_id}', DocumentController::class . '@index', [AuthenticationMiddleware::class]);
$router->delete('/api/documents/{id}', DocumentController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Imaging / DICOM Routes
$router->get('/api/imaging/studies', ImagingController::class . '@studies', [AuthenticationMiddleware::class]);
$router->get('/api/imaging/document/{id}', ImagingController::class . '@serveFile', [AuthenticationMiddleware::class]);
$router->post('/api/imaging/update-meta', ImagingController::class . '@updateMeta', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/imaging/analyze', ImagingController::class . '@analyze', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/imaging/save-findings', ImagingController::class . '@saveFindings', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Referral Routes
$router->get('/api/referrals', ReferralController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/referrals/{patient_id}', ReferralController::class . '@index', [AuthenticationMiddleware::class]);
$router->get('/api/referrals/{id}/document', ReferralController::class . '@downloadDocument', [AuthenticationMiddleware::class]); // was unauthenticated: anyone could fetch a referral file by id
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

// Orders & Results (Labs) Routes
$router->get('/api/orders/workspace', OrderController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/orders/{patient_id}', OrderController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/orders', OrderController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/orders/{id}/status', OrderController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/orders/{id}/review', OrderController::class . '@reviewOrder', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/orders/{id}/results', OrderController::class . '@getResults', [AuthenticationMiddleware::class]);
$router->post('/api/orders/{id}/results', OrderController::class . '@storeResult', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Medications Routes
$router->get('/api/medications/workspace', MedicationController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/medications/{patient_id}', MedicationController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/medications', MedicationController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/medications/{id}/status', MedicationController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/medications/{id}/refill', MedicationController::class . '@refill', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Diagnoses (Problem List) Routes
$router->get('/api/problems/workspace', ProblemController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/problems/{patient_id}', ProblemController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/problems', ProblemController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/problems/{id}', ProblemController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/problems/{id}/resolve', ProblemController::class . '@resolve', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/problems/{id}/reactivate', ProblemController::class . '@reactivate', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Allergies Routes
$router->get('/api/allergies/workspace', AllergyController::class . '@all', [AuthenticationMiddleware::class]);
$router->get('/api/allergies/{patient_id}', AllergyController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/allergies', AllergyController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/allergies/{id}', AllergyController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/allergies/{id}/resolve', AllergyController::class . '@resolve', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/allergies/{id}/reactivate', AllergyController::class . '@reactivate', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

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
$router->post('/api/billing/payment/{id}/void', BillingController::class . '@voidPayment', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/billing/invoice/{id}',BillingController::class . '@deleteInvoice', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/billing/patient/{id}/coverage', BillingController::class . '@patientCoverage', [AuthenticationMiddleware::class]);
// Insurance claim workflow (ClaimController). Fixed-segment routes are registered before /{id} ones.
$router->get('/api/billing/ar-aging', ClaimController::class . '@arAging', [AuthenticationMiddleware::class]);
$router->get('/api/billing/claims', ClaimController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/billing/claims', ClaimController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/billing/claims/{id}', ClaimController::class . '@show', [AuthenticationMiddleware::class]);
$router->put('/api/billing/claims/{id}', ClaimController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/status', ClaimController::class . '@setStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/remit', ClaimController::class . '@remit', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/reverse', ClaimController::class . '@reverseRemit', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/appeal', ClaimController::class . '@appeal', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/close', ClaimController::class . '@close', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/bill-secondary', ClaimController::class . '@billSecondary', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/edi/generate', ClaimEdiController::class . '@generate', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/eligibility', ClaimEdiController::class . '@eligibility', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/edi/send', ClaimEdiController::class . '@send', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/edi/check-remittance', ClaimEdiController::class . '@checkRemittance', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/billing/claims/{id}/edi/post-835', ClaimEdiController::class . '@post835', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/billing/claims/{id}/edi/{tx}', ClaimEdiController::class . '@viewTransaction', [AuthenticationMiddleware::class]);
$router->get('/api/billing/claims/{id}/837', ClaimController::class . '@edi837', [AuthenticationMiddleware::class]);
$router->get('/api/billing/claims/{id}/cms1500', ClaimController::class . '@cms1500', [AuthenticationMiddleware::class]);
$router->get('/api/billing/payers', ClaimController::class . '@payers', [AuthenticationMiddleware::class]);
$router->post('/api/billing/payers', ClaimController::class . '@savePayer', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->get('/api/billing/claim-settings', ClaimController::class . '@getSettings', [AuthenticationMiddleware::class]);
$router->put('/api/billing/claim-settings', ClaimController::class . '@saveSettings', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Phase 2 — Billing handoff queue (signed/locked encounters ready for charge capture)
$router->get('/api/billing/queue', BillingController::class . '@billingQueue', [AuthenticationMiddleware::class]);

// User Administration & RBAC Routes
$router->get('/api/users', \App\Controllers\UserController::class . '@index', [AuthenticationMiddleware::class]);
$router->get('/api/user/{id}', \App\Controllers\UserController::class . '@show', [AuthenticationMiddleware::class]);
$router->post('/api/user', \App\Controllers\UserController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/user/{id}', \App\Controllers\UserController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/user/{id}/unlock', \App\Controllers\UserController::class . '@unlock', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/user/{id}', \App\Controllers\UserController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Facility Administration Routes
$router->get('/api/facilities',                      \App\Controllers\FacilityController::class . '@index',  [AuthenticationMiddleware::class]);
$router->get('/api/facilities/{id}',                 \App\Controllers\FacilityController::class . '@show',   [AuthenticationMiddleware::class]);
$router->post('/api/facilities',                     \App\Controllers\FacilityController::class . '@store',  [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/facilities/{id}',                 \App\Controllers\FacilityController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/facilities/{id}',              \App\Controllers\FacilityController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Specialty Administration Routes
$router->get('/api/specialties',                       \App\Controllers\SpecialtyController::class . '@index',  [AuthenticationMiddleware::class]);
$router->get('/api/specialties/{id}',                  \App\Controllers\SpecialtyController::class . '@show',   [AuthenticationMiddleware::class]);
$router->post('/api/specialties',                      \App\Controllers\SpecialtyController::class . '@store',  [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/specialties/{id}',                  \App\Controllers\SpecialtyController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/specialties/{id}',               \App\Controllers\SpecialtyController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Forced Password Change Route (admin-issued credentials must be changed on first login)
$router->post('/api/auth/change-password', AuthController::class . '@changePassword', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Forgot / reset password (public: the user is not signed in; both are rate limited)
$router->post('/api/auth/forgot-password', AuthController::class . '@forgotPassword', [RateLimitingMiddleware::class]);
$router->post('/api/auth/reset-password', AuthController::class . '@resetPassword', [RateLimitingMiddleware::class]);

// Roles & Permissions (read-only table generated from App\Security\Roles; Super Admin only)
$router->get('/api/roles/matrix', \App\Controllers\RoleController::class . '@matrix', [AuthenticationMiddleware::class]);
$router->get('/api/roles/options', \App\Controllers\RoleController::class . '@options', [AuthenticationMiddleware::class]);
$router->post('/api/roles/custom', \App\Controllers\RoleController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/roles/custom/{id}', \App\Controllers\RoleController::class . '@update', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->delete('/api/roles/custom/{id}', \App\Controllers\RoleController::class . '@delete', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// System Settings Routes
$router->get('/api/settings', \App\Controllers\SettingsController::class . '@get', [AuthenticationMiddleware::class]);
$router->post('/api/settings', \App\Controllers\SettingsController::class . '@save', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/settings/test-email', \App\Controllers\SettingsController::class . '@testEmail', [AuthenticationMiddleware::class, CSRFMiddleware::class]);

// Telehealth Routes
// Public: patient join page asks whether the 5-minute join window is open (timing state only, no PHI)
$router->get('/api/telehealth/join-check', TelehealthController::class . '@joinCheck');
$router->get('/api/telehealth/sessions', TelehealthController::class . '@index', [AuthenticationMiddleware::class]);
$router->post('/api/telehealth/session', TelehealthController::class . '@store', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->post('/api/telehealth/session/{id}/resend', TelehealthController::class . '@resendLink', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
$router->put('/api/telehealth/session/{id}/status', TelehealthController::class . '@updateStatus', [AuthenticationMiddleware::class, CSRFMiddleware::class]);
// Reports & Audit Logs (Super Admin only - enforced in AuditController::checkAccess)
$router->get('/api/reports/audit', \App\Controllers\AuditController::class . '@index', [AuthenticationMiddleware::class]);
$router->get('/api/reports/audit/verify', \App\Controllers\AuditController::class . '@verify', [AuthenticationMiddleware::class]);
$router->get('/api/reports/audit/export', \App\Controllers\AuditController::class . '@export', [AuthenticationMiddleware::class]);

// EHR Practice Reports (RBAC checked inside ReportController)
$router->get('/api/reports/summary', \App\Controllers\ReportController::class . '@summary', [AuthenticationMiddleware::class]);
$router->get('/api/reports/financial', \App\Controllers\ReportController::class . '@financial', [AuthenticationMiddleware::class]);
$router->get('/api/reports/clinical', \App\Controllers\ReportController::class . '@clinical', [AuthenticationMiddleware::class]);
$router->get('/api/reports/operations', \App\Controllers\ReportController::class . '@operations', [AuthenticationMiddleware::class]);
$router->get('/api/reports/users', \App\Controllers\ReportController::class . '@users', [AuthenticationMiddleware::class]);
$router->get('/api/reports/specialties', \App\Controllers\ReportController::class . '@specialties', [AuthenticationMiddleware::class]);
$router->get('/api/reports/export', \App\Controllers\ReportController::class . '@export', [AuthenticationMiddleware::class]);


