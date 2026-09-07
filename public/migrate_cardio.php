<?php
require_once __DIR__ . '/../backend/bootstrap/app.php';
use App\Models\Database;

$db = Database::getConnection();

$codes = [
    ['99204', 'Office/Outpatient Visit New Level 4 (45-59 min)', 225.00, 'Cardiology'],
    ['99205', 'Office/Outpatient Visit New Level 5 (60-74 min)', 295.00, 'Cardiology'],
    ['99214', 'Office/Outpatient Visit Est Level 4 (30-39 min)', 165.00, 'Cardiology'],
    ['99215', 'Office/Outpatient Visit Est Level 5 (40-54 min)', 230.00, 'Cardiology'],
    ['93000', '12-lead Electrocardiogram (ECG) with Interpretation & Report', 85.00, 'Cardiology'],
    ['93306', 'Transthoracic Echocardiography (TTE) Complete with Doppler & Color Flow', 380.00, 'Cardiology'],
    ['93015', 'Cardiovascular Stress Test with ECG Monitoring & Physician Supervision', 275.00, 'Cardiology'],
    ['93296', 'Interrogation Device Evaluation (Pacemaker / ICD Remote)', 120.00, 'Cardiology'],
    ['93458', 'Left Heart Catheterization with Coronary Angiography', 1250.00, 'Cardiology'],
    ['92928', 'Percutaneous Coronary Intervention (PCI) with Drug-Eluting Stent', 2100.00, 'Cardiology'],
    ['93798', 'Cardiac Rehabilitation Phase II with Continuous ECG Monitoring', 95.00, 'Cardiology']
];

foreach ($codes as $c) {
    $stmt = $db->prepare("SELECT id FROM cpt_charge_master WHERE cpt_code = ?");
    $stmt->execute([$c[0]]);
    $exists = $stmt->fetch();
    if (!$exists) {
        $ins = $db->prepare("INSERT INTO cpt_charge_master (cpt_code, description, default_charge, category) VALUES (?, ?, ?, ?)");
        $ins->execute([$c[0], $c[1], $c[2], $c[3]]);
    } else {
        $upd = $db->prepare("UPDATE cpt_charge_master SET description = ?, default_charge = ?, category = ? WHERE cpt_code = ?");
        $upd->execute([$c[1], $c[2], $c[3], $c[0]]);
    }
}

use App\Services\EncryptionService;

// Check/Seed Robert Sterling (62-yo Cardiology Demo Patient)
$allPats = $db->query("SELECT id, first_name_encrypted, last_name_encrypted FROM patients")->fetchAll();
$patId = null;
foreach ($allPats as $p) {
    $fName = EncryptionService::decrypt($p['first_name_encrypted']);
    $lName = EncryptionService::decrypt($p['last_name_encrypted']);
    if ($fName === 'Robert' && $lName === 'Sterling') {
        $patId = $p['id'];
        break;
    }
}

if (!$patId) {
    $fnEnc = EncryptionService::encrypt('Robert');
    $lnEnc = EncryptionService::encrypt('Sterling');
    $dobEnc = EncryptionService::encrypt('1964-03-15');
    $phoneEnc = EncryptionService::encrypt('555-234-8901');
    $email = 'robert.sterling@example.com';

    $insPat = $db->prepare("INSERT INTO patients (first_name_encrypted, last_name_encrypted, dob_encrypted, phone_encrypted, email, gender, age, blood_group, smoking_status, about_patient, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insPat->execute([
        $fnEnc,
        $lnEnc,
        $dobEnc,
        $phoneEnc,
        $email,
        'Male',
        62,
        'O+',
        'Former Smoker (15 pack-years, quit 2018)',
        '62-year-old male with exertional chest pressure and hypertension referred by primary care.',
        'Cardiology'
    ]);
    $patId = $db->lastInsertId();
    echo "Created Robert Sterling demo patient ID: {$patId}\n";
} else {
    echo "Robert Sterling already exists with ID: {$patId}\n";
}

// Check/Seed Demo Cardiology Appointment
$chkAppt = $db->prepare("SELECT id FROM appointments WHERE patient_id = ? AND specialty = 'Cardiology'");
$chkAppt->execute([$patId]);
if (!$chkAppt->fetch()) {
    $insAppt = $db->prepare("INSERT INTO appointments (patient_id, provider_id, start_time, end_time, status, specialty, category, visit_type, appointment_mode, message_to_patient) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $insAppt->execute([
        $patId,
        1,
        date('Y-m-d') . ' 10:00:00',
        date('Y-m-d') . ' 10:45:00',
        'Scheduled',
        'Cardiology',
        'Appointment',
        'Cardiology Initial Consultation (45 min)',
        'In Person',
        'Please bring all current cardiac medications and prior ECG/Echo records.'
    ]);
    echo "Created demo Cardiology appointment.\n";
}

echo "Cardiology CPT codes & Demo Patient migration completed successfully.\n";
