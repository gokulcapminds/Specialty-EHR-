<!-- public/modules/referrals.php -->
<link rel="stylesheet" href="css/modules/dashboard.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'referrals'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-user-md" style="color: #0284c7;"></i> Specialist Referrals Management
                </h1>
                <p style="color: #64748b; font-size: 0.9rem; margin: 4px 0 0 0;">View, create, and manage specialist referrals across all patient records.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 14px;">
                <button type="button" class="btn btn-primary" id="btn-workspace-add-referral" style="background: #0284c7; border-color: #0284c7; font-weight: 700; font-size: 0.9rem; border-radius: 8px; padding: 9px 18px; display: inline-flex; align-items: center; gap: 8px; color: #ffffff; cursor: pointer; box-shadow: 0 2px 4px rgba(2,132,199,0.2);">
                    <i class="fas fa-plus"></i> Add New Referral
                </button>
                <?php include __DIR__ . '/topbar.php'; ?>
            </div>
        </header>

        <!-- Filters & Search Toolbar Card -->
        <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 18px 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; align-items: center;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Patient</label>
                    <select id="referral-filter-patient" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Patients</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Referred Doctor</label>
                    <select id="referral-filter-doctor" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Referred Doctors</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Specialty</label>
                    <select id="referral-filter-specialty" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Specialties</option>
                        <option value="Cardiology">Cardiology EHR</option>
                        <option value="Orthopedics">Orthopedic EHR</option>
                        <option value="Dermatology">Dermatology EHR</option>
                        <option value="Neurology">Neurology EHR</option>
                        <option value="Oncology">Oncology EHR</option>
                        <option value="Ophthalmology">Ophthalmology EHR (covers optometry)</option>
                        <option value="Physical Therapy">Physical Therapy EHR (covers chiropractic)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Filter by Status</label>
                    <select id="referral-filter-status" class="form-control" style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                        <option value="">All Statuses</option>
                        <option value="Pending">Pending</option>
                        <option value="Active">Active</option>
                        <option value="Completed">Completed</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.82rem; color: #475569; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">Search Keywords</label>
                    <input type="text" id="referral-filter-search" class="form-control" placeholder="Search doctor, patient, reason..." style="width: 100%; height: 40px; font-size: 0.9rem; border-radius: 6px;">
                </div>
            </div>
        </div>

        <!-- Referrals Directory Table Card -->
        <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div class="table-container" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.5px;">
                            <th style="padding: 14px 16px;">PATIENT NAME</th>
                            <th style="padding: 14px 16px;">REFERRAL ID</th>
                            <th style="padding: 14px 16px;">REFERRED FROM</th>
                            <th style="padding: 14px 16px;">REFERRAL TO</th>
                            <th style="padding: 14px 16px;">SPECIALTY</th>
                            <th style="padding: 14px 16px;">REASON FOR REFERRAL</th>
                            <th style="padding: 14px 16px;">ISSUED DATE</th>
                            <th style="padding: 14px 16px;">STATUS</th>
                            <th style="padding: 14px 16px;">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="workspace-referrals-list">
                        <tr>
                            <td colspan="9" style="padding: 24px; text-align: center; color: #64748b;">
                                <i class="fas fa-spinner fa-spin" style="margin-right: 8px;"></i> Loading referrals directory...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="referrals-workspace-pagination"></div>
        </div>
    </main>
</div>
