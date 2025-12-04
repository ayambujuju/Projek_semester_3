<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require '../partials/db.php';

// --- Data Fetching ---
$result = $conn->query("SELECT * FROM shipments ORDER BY id DESC");
$shipments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

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
            <a href="tracking.php" class="list-group-item list-group-item-action active">
                <i class="bi bi-truck"></i> Manage Shipments
            </a>
            <a href="edit_history.php" class="list-group-item list-group-item-action">
                <i class="bi bi-pencil-fill"></i> Edit History
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
            <?php if (isset($_GET['message'])): ?>
                <div class="alert alert-<?php echo $_GET['status'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($_GET['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-light">
                    <h4 class="mb-0"><i class="bi bi-truck"></i> Manage Shipments</h4>
                    <button class="btn btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#addShipmentModal">
                        <i class="bi bi-plus-circle-fill"></i> Add New Shipment
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="shipmentsTable" class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Tracking Number</th>
                                    <th>Status</th>
                                    <th>Sender</th>
                                    <th>Receiver</th>
                                    <th>Service Type</th>
                                    <th>Estimated Arrival</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($shipments)): ?>
                                    <?php foreach ($shipments as $shipment): ?>
                                        <tr id="row-<?php echo $shipment['id']; ?>">
                                            <td class="fw-bold" data-col="tracking_number"><?php echo htmlspecialchars($shipment['tracking_number']); ?></td>
                                            <td><span class="badge bg-info" data-col="status"><?php echo htmlspecialchars($shipment['status']); ?></span></td>
                                            <td data-col="sender_name"><?php echo htmlspecialchars($shipment['sender_name'] ?? ''); ?></td>
                                            <td data-col="receiver_name"><?php echo htmlspecialchars($shipment['receiver_name'] ?? ''); ?></td>
                                            <td data-col="service_type"><?php echo htmlspecialchars($shipment['service_type'] ?? ''); ?></td>
                                            <td data-col="estimated_arrival"><?php echo htmlspecialchars($shipment['estimated_arrival'] ?? ''); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary me-1 edit-btn" data-bs-toggle="modal" data-bs-target="#editShipmentModal" data-id="<?php echo $shipment['id']; ?>">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-btn" data-bs-toggle="modal" data-bs-target="#deleteShipmentModal" data-id="<?php echo $shipment['id']; ?>">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No shipments found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-content-wrapper -->
</div>
<!-- /#wrapper -->

<!-- Modals -->
<!-- Add Shipment Modal -->
<div class="modal fade" id="addShipmentModal" tabindex="-1" aria-labelledby="addShipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form action="crud.php" method="POST">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addShipmentModalLabel"><i class="bi bi-plus-circle-fill"></i> Add New Shipment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create_shipment">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_tracking_number" class="form-label">Tracking Number</label>
                            <input type="text" class="form-control" id="add_tracking_number" name="tracking_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_status" class="form-label">Status</label>
                            <select class="form-select" id="add_status" name="status">
                                <option>Pending</option>
                                <option>In Transit</option>
                                <option>Out for Delivery</option>
                                <option>Delivered</option>
                                <option>Failed Attempt</option>
                                <option>Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <hr>
                    <h5>Sender Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_sender_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="add_sender_name" name="sender_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_sender_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="add_sender_phone" name="sender_phone">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="add_sender_address" class="form-label">Address</label>
                            <textarea class="form-control" id="add_sender_address" name="sender_address" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    <h5>Receiver Details</h5>
                     <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_receiver_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="add_receiver_name" name="receiver_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_receiver_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="add_receiver_phone" name="receiver_phone">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="add_receiver_address" class="form-label">Address</label>
                            <textarea class="form-control" id="add_receiver_address" name="receiver_address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_dest_lat" class="form-label">Destination Latitude</label>
                            <input type="text" class="form-control" id="add_dest_lat" name="dest_lat">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_dest_long" class="form-label">Destination Longitude</label>
                            <input type="text" class="form-control" id="add_dest_long" name="dest_long">
                        </div>
                    </div>
                    <div id="addShipmentMap" style="height: 300px; margin-top: 15px; border-radius: .375rem; z-index: 1;"></div>
                    <hr>
                    <h5>Cargo Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_cargo_type" class="form-label">Cargo Type</label>
                            <input type="text" class="form-control" id="add_cargo_type" name="cargo_type">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_cargo_weight" class="form-label">Weight (kg)</label>
                            <input type="text" class="form-control" id="add_cargo_weight" name="cargo_weight">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_cargo_dimensions" class="form-label">Dimensions</label>
                            <input type="text" class="form-control" id="add_cargo_dimensions" name="cargo_dimensions">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_package_count" class="form-label">Package Count</label>
                            <input type="number" class="form-control" id="add_package_count" name="package_count">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="add_special_instructions" class="form-label">Special Instructions</label>
                            <textarea class="form-control" id="add_special_instructions" name="special_instructions" rows="2"></textarea>
                        </div>
                    </div>
                    <hr>
                    <h5>Service Details</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="add_service_type" class="form-label">Service Type</label>
                            <input type="text" class="form-control" id="add_service_type" name="service_type">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="add_estimated_arrival" class="form-label">Estimated Arrival</label>
                            <input type="date" class="form-control" id="add_estimated_arrival" name="estimated_arrival">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Save Shipment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Shipment Modal -->
<div class="modal fade" id="editShipmentModal" tabindex="-1" aria-labelledby="editShipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form action="crud.php" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editShipmentModalLabel"><i class="bi bi-pencil-square"></i> Edit Shipment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_shipment">
                    <input type="hidden" name="id" id="edit_id">
                    <!-- Fields will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update Shipment</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Delete Shipment Modal -->
<div class="modal fade" id="deleteShipmentModal" tabindex="-1" aria-labelledby="deleteShipmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteShipmentModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this shipment? This action cannot be undone.</p>
                <p><strong>Tracking Number:</strong> <span id="delete_tracking_number"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a id="deleteConfirmButton" href="#" class="btn btn-danger">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// --- Geocoding Function ---
async function geocodeAddress(address) {
    if (!address || address.trim() === '') {
        return null;
    }
    const endpoint = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(address)}&format=json&limit=1`;
    try {
        const response = await fetch(endpoint);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        if (data && data.length > 0) {
            return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
        }
        return null;
    } catch (error) {
        console.error('Geocoding error:', error);
        return null;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menu-toggle');
    const wrapper = document.getElementById('wrapper');
    if(menuToggle) {
        menuToggle.addEventListener('click', function () {
            wrapper.classList.toggle('toggled');
        });
    }

    const editShipmentModal = document.getElementById('editShipmentModal');
    let editMap;
    let editMarker;

    if(editShipmentModal) {
        editShipmentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const modal = this;

            // Fetch existing data and populate the form
            fetch(`crud.php?action=get_shipment&id=${id}`)
                .then(response => response.json())
                .catch(error => {
                    // Handle JSON parsing errors or network errors
                    console.error('Error fetching or parsing shipment data:', error);
                    modal.querySelector('.modal-body').innerHTML = '<div class="alert alert-danger">Gagal memuat data pengiriman. Silakan coba lagi.</div>';
                })
                .then(data => {
                    if(data.success) {
                        const shipment = data.data;
                        // use empty strings for null, undefined, or empty values
                        for (const key in shipment) {
                            if (shipment[key] === null || shipment[key] === undefined) {
                                shipment[key] = '';
                            }
                        }

                        const formHtml = `
                            <input type="hidden" name="action" value="update_shipment">
                            <input type="hidden" name="id" value="${shipment.id}">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tracking Number</label>
                                    <input type="text" class="form-control" name="tracking_number" value="${shipment.tracking_number}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option ${shipment.status === 'Pending' ? 'selected' : ''}>Pending</option>
                                        <option ${shipment.status === 'In Transit' ? 'selected' : ''}>In Transit</option>
                                        <option ${shipment.status === 'Out for Delivery' ? 'selected' : ''}>Out for Delivery</option>
                                        <option ${shipment.status === 'Delivered' ? 'selected' : ''}>Delivered</option>
                                        <option ${shipment.status === 'Failed Attempt' ? 'selected' : ''}>Failed Attempt</option>
                                        <option ${shipment.status === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <hr>
                            <h5>Sender Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Name</label><input type="text" class="form-control" name="sender_name" value="${shipment.sender_name}" required></div>
                                <div class="col-md-6 mb-3"><label>Phone</label><input type="text" class="form-control" name="sender_phone" value="${shipment.sender_phone}"></div>
                                <div class="col-12 mb-3"><label>Address</label><textarea class="form-control" name="sender_address" rows="2">${shipment.sender_address}</textarea></div>
                            </div>
                            <hr>
                            <h5>Receiver Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Name</label><input type="text" class="form-control" name="receiver_name" value="${shipment.receiver_name}" required></div>
                                <div class="col-md-6 mb-3"><label>Phone</label><input type="text" class="form-control" name="receiver_phone" value="${shipment.receiver_phone}"></div>
                                <div class="col-12 mb-3"><label>Address</label><textarea class="form-control" name="receiver_address" rows="2">${shipment.receiver_address}</textarea></div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_dest_lat" class="form-label">Destination Latitude</label>
                                    <input type="text" class="form-control" id="edit_dest_lat" name="dest_lat" value="${shipment.dest_lat || ''}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_dest_long" class="form-label">Destination Longitude</label>
                                    <input type="text" class="form-control" id="edit_dest_long" name="dest_long" value="${shipment.dest_long || ''}">
                                </div>
                            </div>
                            <div id="editShipmentMap" style="height: 300px; margin-top: 15px; border-radius: .375rem; z-index: 1;"></div>
                            <hr>
                            <h5>Cargo Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Cargo Type</label><input type="text" class="form-control" name="cargo_type" value="${shipment.cargo_type}"></div>
                                <div class="col-md-6 mb-3"><label>Weight (kg)</label><input type="text" class="form-control" name="cargo_weight" value="${shipment.cargo_weight}"></div>
                                <div class="col-md-6 mb-3"><label>Dimensions</label><input type="text" class="form-control" name="cargo_dimensions" value="${shipment.cargo_dimensions}"></div>
                                <div class="col-md-6 mb-3"><label>Package Count</label><input type="number" class="form-control" name="package_count" value="${shipment.package_count}"></div>
                                <div class="col-12 mb-3"><label>Special Instructions</label><textarea class="form-control" name="special_instructions" rows="2">${shipment.special_instructions}</textarea></div>
                            </div>
                            <hr>
                            <h5>Service Details</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3"><label>Service Type</label><input type="text" class="form-control" name="service_type" value="${shipment.service_type}"></div>
                                <div class="col-md-6 mb-3"><label>Estimated Arrival</label><input type="date" class="form-control" name="estimated_arrival" value="${shipment.estimated_arrival}"></div>
                            </div>
                        `;
                        modal.querySelector('.modal-body').innerHTML = formHtml;
                        
                        // Add event listener for geocoding on address blur
                        const receiverAddressInput = modal.querySelector('textarea[name="receiver_address"]');
                        receiverAddressInput.addEventListener('blur', async function() {
                            const address = this.value;
                            const coords = await geocodeAddress(address);
                            if (coords && editMap && editMarker) {
                                const newLatLng = L.latLng(coords.lat, coords.lon);
                                document.getElementById('edit_dest_lat').value = coords.lat.toFixed(6);
                                document.getElementById('edit_dest_long').value = coords.lon.toFixed(6);
                                editMap.setView(newLatLng, 15);
                                editMarker.setLatLng(newLatLng);
                            } else if (address) {
                                alert('Alamat tidak ditemukan. Silakan sesuaikan lokasi di peta secara manual.');
                            }
                        });

                        // -- Initialize Map for Edit Modal --
                        setTimeout(() => {
                            // Destroy previous map instance if it exists
                            if (editMap) {
                                editMap.remove();
                            }

                            const lat = parseFloat(shipment.dest_lat) || -2.5489;
                            const lng = parseFloat(shipment.dest_long) || 118.0149;
                            
                            editMap = L.map('editShipmentMap').setView([lat, lng], 10);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                attribution: '© OpenStreetMap contributors'
                            }).addTo(editMap);

                            editMarker = L.marker([lat, lng], { draggable: true }).addTo(editMap);

                            editMarker.on('dragend', function(e) {
                                const newLat = e.target.getLatLng().lat.toFixed(6);
                                const newLng = e.target.getLatLng().lng.toFixed(6);
                                document.getElementById('edit_dest_lat').value = newLat;
                                document.getElementById('edit_dest_long').value = newLng;
                            });

                            editMap.on('click', function(e) {
                                const newLat = e.latlng.lat.toFixed(6);
                                const newLng = e.latlng.lng.toFixed(6);
                                editMarker.setLatLng(e.latlng);
                                document.getElementById('edit_dest_lat').value = newLat;
                                document.getElementById('edit_dest_long').value = newLng;
                            });

                            editMap.invalidateSize();
                        }, 300); // Delay to ensure modal and DOM is ready

                    }
                });
        });

        editShipmentModal.addEventListener('hidden.bs.modal', function() {
            if (editMap) {
                editMap.remove();
                editMap = null;
            }
        });
    }

    const deleteShipmentModal = document.getElementById('deleteShipmentModal');
    if(deleteShipmentModal) {
        deleteShipmentModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const row = document.getElementById('row-' + id);
            const tracking_number = row.querySelector('[data-col="tracking_number"]').textContent;
            
            const modal = this;
            modal.querySelector('#delete_tracking_number').textContent = tracking_number;
            const deleteConfirmButton = document.getElementById('deleteConfirmButton');
            deleteConfirmButton.href = `crud.php?action=delete_shipment&id=${id}`;
        });
    }

    const addShipmentModal = document.getElementById('addShipmentModal');
    let addMap;
    let addMarker;

    addShipmentModal.addEventListener('shown.bs.modal', function () {
        // Must run after modal is shown and animation is complete
        setTimeout(function() {
            if (!addMap) {
                // Initialize the map
                addMap = L.map('addShipmentMap').setView([-2.5489, 118.0149], 5);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(addMap);

                const defaultLatLng = [-2.5489, 118.0149];
                addMarker = L.marker(defaultLatLng, {
                    draggable: true
                }).addTo(addMap);
                
                addMarker.on('dragend', function(e) {
                    const lat = e.target.getLatLng().lat.toFixed(6);
                    const lng = e.target.getLatLng().lng.toFixed(6);
                    document.getElementById('add_dest_lat').value = lat;
                    document.getElementById('add_dest_long').value = lng;
                });

                addMap.on('click', function(e) {
                    const lat = e.latlng.lat.toFixed(6);
                    const lng = e.latlng.lng.toFixed(6);
                    addMarker.setLatLng(e.latlng);
                    document.getElementById('add_dest_lat').value = lat;
                    document.getElementById('add_dest_long').value = lng;
                });
            }
            addMap.invalidateSize();
        }, 300);

        // Add event listener for geocoding on address blur for the "Add" modal
        const addReceiverAddressInput = addShipmentModal.querySelector('#add_receiver_address');
        addReceiverAddressInput.addEventListener('blur', async function() {
            const address = this.value;
            const coords = await geocodeAddress(address);
            if (coords && addMap && addMarker) {
                const newLatLng = L.latLng(coords.lat, coords.lon);
                document.getElementById('add_dest_lat').value = coords.lat.toFixed(6);
                document.getElementById('add_dest_long').value = coords.lon.toFixed(6);
                addMap.setView(newLatLng, 15); // Zoom in to the location
                addMarker.setLatLng(newLatLng);
            } else if (address) {
                alert('Alamat tidak ditemukan. Silakan sesuaikan lokasi di peta secara manual.');
            }
        });
    });

    addShipmentModal.addEventListener('hidden.bs.modal', function() {
        // Reset form fields
        addShipmentModal.querySelector('form').reset();
        // Destroy map instance to allow re-initialization
        if (addMap) {
            addMap.remove();
            addMap = null;
        }
    });
});
</script>

<?php include 'partials/footer.php'; ?>