<!-- public/modules/documents.php --><link rel="stylesheet" href="css/modules/documents.css?v=<?= time() ?>">

<div class="app-container">
    <?php $activeNav = 'documents'; include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="workspace-header">
            <h1>Document Manager</h1>
        </header>

        <div class="card select-patient-card">
            <div class="form-group">
                <label class="form-label" for="document-patient-select">Patient Document Directory</label>
                <select id="document-patient-select" class="form-control">
                    <option value="">Select a patient to view files...</option>
                </select>
            </div>
        </div>

        <div id="documents-area" class="hidden">
            <div class="card upload-card">
                <h2>Upload Secure Document</h2>
                <form id="document-upload-form" novalidate enctype="multipart/form-data">
                    <div class="form-group">
                        <label class="form-label" for="doc-file">Select Document File (PDF, JPEG, PNG, DICOM)</label>
                        <input type="file" id="doc-file" class="form-control" name="document" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="upload-doc-btn">Upload Document</button>
                </form>
            </div>

            <div class="card list-card">
                <h2>Uploaded Patient Documents</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Filename</th>
                                <th>MIME Type</th>
                                <th>File Size</th>
                                <th>Uploaded At</th>
                                <th>Uploaded By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="documents-list">
                            <tr>
                                <td colspan="6">Select a patient to load documents.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="documents-pagination"></div>
            </div>
        </div>
    </main>
</div>
