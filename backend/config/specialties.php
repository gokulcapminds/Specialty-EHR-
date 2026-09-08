<?php
// Canonical specialty registry — single source of truth for the 7 planned
// specialty engines. Every place that needs a list of specialties (login,
// appointment/waiting-list/referral/encounter dropdowns, encounter_type
// validation) should read from this file instead of hardcoding its own list.
//
// key: canonical machine value stored in appointments.specialty,
//      clinical_notes.encounter_type, patient_referrals.specialty, etc.
// label: user-facing display text.
// data_column: the clinical_notes column holding this specialty's JSON blob.
// form_id: the <form> element id in public/modules/clinical_modal.php that
//          collects this specialty's fields.
// panel_class: the CSS class used to show/hide this specialty's accordion
//              panel in public/modules/clinical_modal.php.
return [
    // 'label' preserves the exact display text already shown across the
    // app's various dropdowns today (e.g. "Cardiology EHR"), so switching
    // those dropdowns to render from this registry changes only the
    // underlying <option value>, never what the user sees.
    'Cardiology' => [
        'label' => 'Cardiology EHR',
        'data_column' => 'cardio_data',
        'form_id' => 'cardio-form',
        'panel_class' => 'cardio-only',
    ],
    'Orthopedics' => [
        'label' => 'Orthopedic EHR',
        'data_column' => 'ortho_data',
        'form_id' => 'ortho-form',
        'panel_class' => 'ortho-only',
    ],
    'Dermatology' => [
        'label' => 'Dermatology EHR',
        'data_column' => 'derma_data',
        'form_id' => 'derma-form',
        'panel_class' => 'derma-only',
    ],
    'Neurology' => [
        'label' => 'Neurology EHR',
        'data_column' => 'neuro_data',
        'form_id' => 'neuro-form',
        'panel_class' => 'neuro-only',
    ],
    'Oncology' => [
        'label' => 'Oncology EHR',
        'data_column' => 'onco_data',
        'form_id' => 'onco-form',
        'panel_class' => 'onco-only',
    ],
    'Ophthalmology' => [
        'label' => 'Ophthalmology EHR (covers optometry)',
        'data_column' => 'ophthal_data',
        'form_id' => 'ophthal-form',
        'panel_class' => 'ophthal-only',
    ],
    'Physical Therapy' => [
        'label' => 'Physical Therapy EHR (covers chiropractic)',
        'data_column' => 'pt_data',
        'form_id' => 'pt-form',
        'panel_class' => 'pt-only',
    ],
];
