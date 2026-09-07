<!-- public/modules/billing.php -->
<link rel="stylesheet" href="css/modules/billing.css?v=<?= time() ?>">
<div class="app-container">
    <?php $activeNav = 'billing'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div>
                <h1>Billing &amp; Invoicing</h1>
                <p class="text-sm mod-billing-style-1">Encounter → ICD-10 → Charges → Invoice → Payment</p>
            </div>
            <?php include __DIR__ . '/topbar.php'; ?>
        </header>

        <!-- Tab Navigation -->
        <div class="billing-tab-bar mod-billing-style-3">
            <button class="billing-tab-btn active mod-billing-style-4" data-tab="unbilled">
                <i class="fas fa-exclamation-circle"></i> Unbilled Encounters <span id="unbilled-count-badge" class="badge-role badge-danger-xs mod-billing-style-5"></span>
            </button>
            <button class="billing-tab-btn mod-billing-style-6" data-tab="invoices">
                <i class="fas fa-file-invoice"></i> Invoices Ledger
            </button>
            <button class="billing-tab-btn mod-billing-style-6" data-tab="chargemaster">
                <i class="fas fa-tags"></i> CPT Charge Master
            </button>
        </div>

        <!-- Tab 1: Unbilled Encounters -->
        <div id="billing-tab-unbilled" class="billing-tab-content">
            <div class="card">
                <div class="card-header mod-billing-style-7">
                    <h2 class="mod-billing-style-8"><i class="fas fa-file-medical mod-billing-style-9"></i> Encounters Ready to Bill</h2>
                    <button class="btn btn-secondary btn-sm" id="refresh-unbilled-btn"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
                <p class="text-sm mod-billing-style-10">These clinical encounters have ICD-10 diagnosis codes but no invoice has been generated yet. Click "Generate Invoice" to start billing.</p>
                <div class="table-container">
                    <table id="unbilled-table">
                        <thead>
                            <tr>
                                <th>Patient</th>
                                <th>Encounter Date</th>
                                <th>Provider</th>
                                <th>Specialty</th>
                                <th>CPT-4 / Procedure Codes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="unbilled-list">
                            <tr><td class="mod-billing-style-11" colspan="6"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="unbilled-pagination"></div>
            </div>
        </div>

        <!-- Tab 2: Invoices Ledger -->
        <div id="billing-tab-invoices" class="billing-tab-content mod-billing-style-12">
            <div class="card">
                <div class="card-header mod-billing-style-7">
                    <h2 class="mod-billing-style-8"><i class="fas fa-file-invoice-dollar mod-billing-style-13"></i> Invoices Ledger</h2>
                    <div class="mod-billing-style-14">
                        <select id="invoice-status-filter" class="form-control mod-billing-style-15" aria-label="billing field 1">
                            <option value="">All Statuses</option>
                            <option value="Draft">Draft</option>
                            <option value="Issued">Issued</option>
                            <option value="Partially Paid">Partially Paid</option>
                            <option value="Paid">Paid</option>
                            <option value="Overdue">Overdue</option>
                        </select>
                        <button class="btn btn-secondary btn-sm" id="refresh-invoices-btn"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                </div>
                <div class="table-container">
                    <table id="invoices-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Patient</th>
                                <th>Invoice Date</th>
                                <th>Due Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="invoices-list">
                            <tr><td class="mod-billing-style-11" colspan="9"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="invoices-pagination"></div>
            </div>
        </div>

        <!-- Tab 3: CPT Charge Master -->
        <div id="billing-tab-chargemaster" class="billing-tab-content mod-billing-style-12">
            <div class="card">
                <div class="card-header mod-billing-style-7">
                    <h2 class="mod-billing-style-8"><i class="fas fa-tags mod-billing-style-16"></i> CPT Code Charge Master</h2>
                    <button class="btn btn-primary btn-sm" id="add-cpt-btn"><i class="fas fa-plus"></i> Add CPT Code</button>
                </div>
                <div class="mod-billing-style-17">
                    <input type="text" id="cpt-search" class="form-control mod-billing-style-18" placeholder="Search CPT codes..." aria-label="Search CPT codes...">
                    <select id="cpt-category-filter" class="form-control mod-billing-style-19" aria-label="billing field 1">
                        <option value="">All Categories</option>
                        <option value="E&M">E&amp;M</option>
                        <option value="Preventive">Preventive</option>
                        <option value="Consultation">Consultation</option>
                        <option value="Lab">Lab</option>
                        <option value="Procedure">Procedure</option>
                        <option value="Immunization">Immunization</option>
                        <option value="OB/GYN">OB/GYN</option>
                    </select>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>CPT Code</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Default Charge</th>
                            </tr>
                        </thead>
                        <tbody id="cpt-list">
                            <tr><td class="mod-billing-style-11" colspan="4"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="cpt-pagination"></div>
            </div>
        </div>
    </main>
</div>

<!-- ======================== GENERATE INVOICE MODAL ======================== -->
<div id="generate-invoice-modal" class="modal-overlay hidden mod-billing-style-20">
    <div class="modal-dialog mod-billing-style-21">
        <div class="modal-header">
            <h2 class="mod-billing-style-22" id="gen-invoice-title"><i class="fas fa-file-invoice-dollar mod-billing-style-13"></i> Generate Invoice</h2>
            <button id="close-gen-invoice-btn" class="btn-icon-close">&times;</button>
        </div>
        <div class="modal-body mod-billing-style-23">
            <!-- Patient & Encounter Info -->
            <div class="mod-billing-style-24">
                <div>
                    <p class="mod-billing-style-25">Patient</p>
                    <p class="mod-billing-style-26" id="inv-patient-name"></p>
                </div>
                <div>
                    <p class="mod-billing-style-25">Provider</p>
                    <p class="mod-billing-style-27" id="inv-provider-name"></p>
                </div>
                <div>
                    <p class="mod-billing-style-25">Encounter Date</p>
                    <p class="mod-billing-style-27" id="inv-encounter-date"></p>
                </div>
                <div>
                    <p class="mod-billing-style-25">Specialty</p>
                    <p class="mod-billing-style-27" id="inv-encounter-type"></p>
                </div>
            </div>
            <input type="hidden" id="inv-patient-id">
            <input type="hidden" id="inv-encounter-id">

            <!-- Invoice Details -->
            <div class="mod-billing-style-28">
                <div class="form-group">
                    <label class="form-label">Invoice Date</label>
                    <input type="date" id="inv-date" class="form-control" aria-label="billing field 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" id="inv-due-date" class="form-control" aria-label="billing field 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Discount ($)</label>
                    <input type="number" id="inv-discount" class="form-control" value="0" min="0" step="0.01" aria-label="billing field 1">
                </div>
            </div>

            <!-- Line Items -->
            <h3 class="mod-billing-style-29">
                <i class="fas fa-list mod-billing-style-30"></i> Charge Line Items
                <small class="mod-billing-style-31">(auto-loaded from encounter CPT-4 codes)</small>
            </h3>
            <div class="table-container mod-billing-style-32">
                <table id="line-items-table">
                    <thead>
                        <tr>
                            <th>CPT Code</th>
                            <th>CPT Description</th>
                            <th>Qty</th>
                            <th>Unit Price ($)</th>
                            <th>Total ($)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="line-items-body"></tbody>
                </table>
            </div>
            <button class="btn btn-secondary btn-sm mod-billing-style-33" id="add-line-item-btn">
                <i class="fas fa-plus"></i> Add Line Item
            </button>

            <!-- Totals -->
            <div class="mod-billing-style-34">
                <div class="mod-billing-style-35">
                    <div class="mod-billing-style-36">
                        <span class="mod-billing-style-37">Subtotal:</span>
                        <strong id="inv-subtotal">$0.00</strong>
                    </div>
                    <div class="mod-billing-style-36">
                        <span class="mod-billing-style-37">Discount:</span>
                        <strong class="mod-billing-style-38" id="inv-discount-display">-$0.00</strong>
                    </div>
                    <div class="mod-billing-style-39">
                        <span class="mod-billing-style-40">Total Due:</span>
                        <strong class="mod-billing-style-41" id="inv-total">$0.00</strong>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group mod-billing-style-33">
                <label class="form-label">Billing Notes (optional)</label>
                <textarea id="inv-notes" class="form-control" rows="2" placeholder="e.g. Patient has secondary insurance, co-pay waived..." aria-label="e.g. Patient has secondary insurance, co-pay waived..."></textarea>
            </div>

            <!-- Actions -->
            <div class="mod-billing-style-42">
                <button class="btn btn-secondary" id="cancel-gen-invoice-btn">Cancel</button>
                <button class="btn btn-primary" id="save-invoice-btn">
                    <i class="fas fa-file-invoice"></i> Create &amp; Issue Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ======================== VIEW INVOICE MODAL ======================== -->
<div id="view-invoice-modal" class="modal-overlay hidden mod-billing-style-20">
    <div class="modal-dialog mod-billing-style-43">
        <div class="modal-header">
            <h2 class="mod-billing-style-22"><i class="fas fa-file-invoice mod-billing-style-13"></i> Invoice Details</h2>
            <div class="mod-billing-style-14">
                <button id="print-invoice-btn" class="btn btn-secondary btn-sm"><i class="fas fa-print"></i> Print</button>
                <button id="close-view-invoice-btn" class="btn-icon-close">&times;</button>
            </div>
        </div>
        <div class="modal-body mod-billing-style-44" id="invoice-print-area">
            <!-- Content loaded dynamically -->
        </div>
        <!-- Record Payment -->
        <div class="mod-billing-style-45" id="record-payment-section">
            <span class="mod-billing-style-46">Record Payment:</span>
            <input type="number" id="payment-amount-input" class="form-control mod-billing-style-47" placeholder="Amount ($)" min="0.01" step="0.01" aria-label="Amount ($)">
            <button class="btn btn-success btn-sm" id="submit-payment-btn"><i class="fas fa-check-circle"></i> Apply Payment</button>
            <button class="btn btn-secondary btn-sm" id="close-view-invoice-btn2">Close</button>
        </div>
    </div>
</div>

<!-- ======================== ADD CPT MODAL ======================== -->
<div id="add-cpt-modal" class="modal-overlay hidden mod-billing-style-20">
    <div class="modal-dialog mod-billing-style-48">
        <div class="modal-header">
            <h2 class="mod-billing-style-49"><i class="fas fa-plus mod-billing-style-13"></i> Add / Edit CPT Code</h2>
            <button id="close-add-cpt-btn" class="btn-icon-close">&times;</button>
        </div>
        <div class="modal-body mod-billing-style-23">
            <div class="form-group">
                <label class="form-label">CPT Code *</label>
                <input type="text" id="new-cpt-code" class="form-control" placeholder="e.g. 99213" aria-label="e.g. 99213">
            </div>
            <div class="form-group">
                <label class="form-label">Description *</label>
                <input type="text" id="new-cpt-desc" class="form-control" placeholder="e.g. Office Visit, Est. Patient, Low Complexity" aria-label="e.g. Office Visit, Est. Patient, Low Complexity">
            </div>
            <div class="form-group">
                <label class="form-label">Category</label>
                <select id="new-cpt-category" class="form-control" aria-label="billing field 1">
                    <option value="Primary Care">Primary Care</option>
                    <option value="Pediatrics">Pediatrics</option>
                    <option value="Gynecology">Gynecology</option>
                    
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Default Charge ($) *</label>
                <input type="number" id="new-cpt-charge" class="form-control" min="0" step="0.01" placeholder="120.00" aria-label="120.00">
            </div>
            <div class="mod-billing-style-50">
                <button class="btn btn-secondary" id="cancel-add-cpt-btn">Cancel</button>
                <button class="btn btn-primary" id="save-cpt-btn"><i class="fas fa-save"></i> Save CPT Code</button>
            </div>
        </div>
    </div>
</div>

