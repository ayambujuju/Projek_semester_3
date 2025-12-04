<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require '../partials/db.php';
?>
<?php include 'partials/header.php'; ?>

<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading">
             <i class="bi bi-box-seam-fill"></i> Check Cargo
        </div>
        <div class="list-group list-group-flush">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="tracking.php" class="list-group-item list-group-item-action">
                <i class="bi bi-truck"></i> Manage Shipments
            </a>
            <a href="edit_history.php" class="list-group-item list-group-item-action active">
                <i class="bi bi-pencil-fill"></i> Edit History
            </a>
             <a href="#" class="list-group-item list-group-item-action">
                <i class="bi bi-gear-fill"></i> Settings
            </a>
        </div>
    </div>
    <!-- /#sidebar-wrapper -->

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light border-bottom">
            <div class="container-fluid">
                <button class="btn btn-primary" id="menu-toggle"><i class="bi bi-list"></i></button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#">Profile</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="logout.php">Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="container-fluid p-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h4 class="mb-0"><i class="bi bi-search"></i> Find Shipment to Edit History</h4>
                </div>
                <div class="card-body">
                    <form id="find-shipment-form">
                        <div class="input-group mb-3">
                            <input type="text" class="form-control form-control-lg" name="resi" placeholder="Enter Tracking Number..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Find
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="history-results-container" class="mt-4" style="display: none;">
                <!-- Results will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<!-- Edit History Modal -->
<div class="modal fade" id="editHistoryModal" tabindex="-1" aria-labelledby="editHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form id="edit-history-form">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editHistoryModalLabel"><i class="bi bi-pencil-square"></i> Edit History Entry</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="edit-error-alert" class="alert alert-danger" style="display:none;"></div>
                    <input type="hidden" name="history_id" id="edit_history_id">
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <input type="text" class="form-control" id="edit_status" name="status" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="edit_location" name="location" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const findForm = document.getElementById('find-shipment-form');
    const resultsContainer = document.getElementById('history-results-container');
    const editHistoryModal = new bootstrap.Modal(document.getElementById('editHistoryModal'));
    const editHistoryForm = document.getElementById('edit-history-form');

    findForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const resi = findForm.querySelector('input[name="resi"]').value.trim();
        if (!resi) {
            alert('Please enter a tracking number.');
            return;
        }

        resultsContainer.style.display = 'block';
        resultsContainer.innerHTML = `<div class="card shadow-sm"><div class="card-body text-center"><div class="spinner-border text-primary" role="status"></div><p class="mt-2">Fetching shipment data...</p></div></div>`;

        fetch(`api_history_crud.php?action=get_shipment_with_history&resi=${encodeURIComponent(resi)}`)
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    renderResults(res.data);
                } else {
                    resultsContainer.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${res.message}</div>`;
                }
            })
            .catch(error => {
                resultsContainer.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> Could not connect to the server.</div>`;
            });
    });

    function renderResults(shipment) {
        const historyItemsHtml = shipment.history.length > 0 ? shipment.history.map(item => `
            <li class="list-group-item d-flex justify-content-between align-items-center" id="history-item-${item.id}">
                <div>
                    <small class="text-muted">${item.timestamp}</small>
                    <p class="mb-0"><strong>${item.status}:</strong> ${item.description} at ${item.location}</p>
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="editHistory(${item.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteHistory(${item.id})"><i class="bi bi-trash"></i></button>
                </div>
            </li>`).join('') : '<li class="list-group-item text-center text-muted">No history found.</li>';

        const resultsHtml = `
            <div class="card shadow-sm mb-4"><div class="card-header bg-light"><h5 class="mb-0">Shipment: <strong class="text-primary">${shipment.tracking_number}</strong></h5></div><div class="card-body"><div class="row"><div class="col-md-6"><p><strong>Sender:</strong> ${shipment.sender_name}</p><p><strong>Receiver:</strong> ${shipment.receiver_name}</p></div><div class="col-md-6"><p><strong>Status:</strong> <span class="badge bg-info">${shipment.status}</span></p><p><strong>Service:</strong> ${shipment.service_type}</p></div></div></div></div>
            <div class="card shadow-sm mb-4"><div class="card-header bg-light"><h5 class="mb-0">Shipment History</h5></div><ul class="list-group list-group-flush">${historyItemsHtml}</ul></div>
            <div class="card shadow-sm"><div class="card-header bg-light"><h5 class="mb-0">Add New History Entry</h5></div><div class="card-body"><form id="add-history-form"><input type="hidden" name="shipment_id" value="${shipment.id}"><div class="row"><div class="col-md-6 mb-3"><label class="form-label">Status</label><input type="text" name="status" class="form-control" required></div><div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" required>
                                <div class="form-text">
                                    <strong>Saran:</strong> Gunakan nama kota/lokasi spesifik (e.g. <code>Gudang Jakarta Pusat</code>) agar muncul di peta.
                                </div>
                            </div></div><div class="mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2" required></textarea></div><button type="submit" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Entry</button></form></div></div>`;
        
        resultsContainer.innerHTML = resultsHtml;
        document.getElementById('add-history-form').addEventListener('submit', handleAddHistory);
    }

    function handleAddHistory(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        formData.append('action', 'add_history');
        const submitButton = form.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Adding...';

        fetch('api_history_crud.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    alert('History added successfully!');
                    findForm.requestSubmit();
                } else {
                    alert(`Error: ${res.message}`);
                }
            })
            .catch(error => alert('Error connecting to the server.'))
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="bi bi-plus-circle"></i> Add Entry';
            });
    }

    editHistoryForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('action', 'update_history');
        const submitButton = e.target.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
        
        document.getElementById('edit-error-alert').style.display = 'none';

        fetch('api_history_crud.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    editHistoryModal.hide();
                    alert('History updated successfully!');
                    findForm.requestSubmit();
                } else {
                    const errorAlert = document.getElementById('edit-error-alert');
                    errorAlert.textContent = res.message;
                    errorAlert.style.display = 'block';
                }
            })
            .catch(error => {
                 const errorAlert = document.getElementById('edit-error-alert');
                 errorAlert.textContent = 'Error connecting to the server.';
                 errorAlert.style.display = 'block';
            })
            .finally(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Save Changes';
            });
    });
});

function editHistory(id) {
    const modalForm = document.getElementById('edit-history-form');
    document.getElementById('edit-error-alert').style.display = 'none';

    fetch(`api_history_crud.php?action=get_history_item&history_id=${id}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                modalForm.querySelector('#edit_history_id').value = res.data.id;
                modalForm.querySelector('#edit_status').value = res.data.status;
                modalForm.querySelector('#edit_location').value = res.data.location;
                modalForm.querySelector('#edit_description').value = res.data.description;
                new bootstrap.Modal(document.getElementById('editHistoryModal')).show();
            } else {
                alert(`Error: ${res.message}`);
            }
        })
        .catch(error => alert('Could not fetch history item details.'));
}

function deleteHistory(id) {
    if (confirm('Are you sure you want to delete history item ' + id + '?')) {
        const itemElement = document.getElementById(`history-item-${id}`);
        if(itemElement) itemElement.style.opacity = '0.5';

        const formData = new FormData();
        formData.append('action', 'delete_history');
        formData.append('history_id', id);

        fetch('api_history_crud.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    alert('History item deleted successfully.');
                    document.getElementById('find-shipment-form').requestSubmit();
                } else {
                    alert(`Error: ${res.message}`);
                    if(itemElement) itemElement.style.opacity = '1';
                }
            })
            .catch(error => {
                alert('Could not connect to the server.');
                if(itemElement) itemElement.style.opacity = '1';
            });
    }
}
</script>

<?php include 'partials/footer.php'; ?>
