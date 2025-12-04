/**
 * Tracking Feature Class
 * Handles tracking form submission, modal display, and map visualization.
 */
class Tracking {
    constructor() {
        this.modalElement = null;
        this.modalOverlay = null;
        this.modalContent = null;
        this.trackingForm = document.getElementById('hero-tracking-form');
        this.map = null;
        this.geocodeCache = new Map();
        this.pollingInterval = 30000;
        this.pollingId = null;
        this.currentReceiptNumber = null;
        this._init();
    }

    _init() {
        this._createModal();
        if (this.trackingForm) {
            this.trackingForm.addEventListener('submit', this._handleFormSubmit.bind(this));
        }
    }

    _createModal() {
        this.modalElement = document.createElement('div');
        this.modalElement.className = 'v8-modal-overlay';
        this.modalElement.id = 'v8-tracking-modal';
        this.modalElement.innerHTML = `<div class="v8-modal-content-wrapper"><div class="v8-modal-content"></div></div>`;
        document.body.appendChild(this.modalElement);

        this.modalOverlay = document.getElementById('v8-tracking-modal');
        this.modalContent = this.modalOverlay.querySelector('.v8-modal-content');

        this.modalOverlay.addEventListener('click', e => {
            if (e.target === this.modalOverlay) this._hideModal();
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && this.modalOverlay.classList.contains('is-visible')) {
                this._hideModal();
            }
        });
    }

    _showModal() {
        this.modalOverlay.classList.add('is-visible');
        document.body.style.overflow = 'hidden';
    }

    _hideModal() {
        this.modalOverlay.classList.remove('is-visible');
        document.body.style.overflow = '';
        this._stopPolling();
        if (this.map) {
            this.map.remove();
            this.map = null;
        }
        this.currentReceiptNumber = null;
    }

    _setButtonLoading(isLoading) {
        const button = this.trackingForm.querySelector('button');
        if (!button) return;
        if (isLoading) {
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Melacak...';
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fa-solid fa-magnifying-glass"></i> Lacak';
        }
    }

    _displayError(message) {
        this.modalContent.innerHTML = `
            <div class="v8-modal-header">
                <div class="awb-info">
                    <span class="info-label">Error</span>
                    <h2 class="info-value">Gagal Memuat</h2>
                </div>
                <button class="v8-close-btn" aria-label="Tutup">&times;</button>
            </div>
            <div class="v8-modal-body" style="display: flex; align-items: center; justify-content: center; text-align: center;">
                <div class="tracking-error-v8">
                    <p style="font-size: 1.1rem; color: #dc3545;">${message}</p>
                </div>
            </div>`;
        this._bindCloseButton();
    }

    _bindCloseButton() {
        const closeBtn = this.modalContent.querySelector('.v8-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this._hideModal());
        }
    }

    async _handleFormSubmit(e) {
        e.preventDefault();
        this._setButtonLoading(true);
        const input = this.trackingForm.querySelector('input[name="receipt_number"]');
        this.currentReceiptNumber = input.value.trim();

        if (!this.currentReceiptNumber) {
            alert('Silakan masukkan nomor resi.');
            this._setButtonLoading(false);
            return;
        }

        this._showModal();
        this.modalContent.innerHTML = `<div class="v8-modal-body"><div class="v8-initial-loader"></div><p style="text-align: center; font-weight: 600; color: var(--text-color);">Memuat data untuk ${this.currentReceiptNumber}...</p></div>`;

        try {
            const response = await fetch(`api/track.php?resi=${encodeURIComponent(this.currentReceiptNumber)}`);
            if (!response.ok) throw new Error(`Gagal menghubungi server (Status: ${response.status})`);
            
            const data = await response.json();
            if (data.success && data.data) {
                this.modalContent.innerHTML = this._buildMapModalHtml(data.data);
                this._bindCloseButton();
                setTimeout(() => this._initializeTrackingMap(data.data), 50);
                this._startPolling();
            } else {
                this._displayError(data.message || 'Data tidak ditemukan atau resi tidak valid.');
            }
        } catch (error) {
            console.error('Error fetching tracking data:', error);
            this._displayError('Terjadi kesalahan. Periksa koneksi Anda dan coba lagi.');
        } finally {
            this._setButtonLoading(false);
        }
    }

    _buildMapModalHtml(d) {
        const displayHistory = (d.history && d.history.length > 0)
            ? [...d.history].sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp))
            : [];

        const historyListHtml = displayHistory.map((item, index) => `
            <li class="v8-history-item ${index === 0 ? 'is-latest' : ''}">
                <p class="v8-history-status">${item.description}</p>
                <p class="v8-history-location">${item.location}</p>
                <p class="v8-history-time">${this._formatDate(item.timestamp)}</p>
            </li>`).join('');

        return `
            <div class="v8-modal-header">
                <div class="awb-info">
                    <span class="info-label">Nomor Resi</span>
                    <h2 class="info-value">${d.tracking_number}</h2>
                </div>
                <div class="v8-refresh-indicator" title="Memperbarui data..."></div>
                <button class="v8-close-btn" aria-label="Tutup">&times;</button>
            </div>
            <div class="v8-modal-body">
                <div class="v8-map-container">
                    <div id="trackingMapV8"></div>
                    <div id="map-loader" class="map-loader map-loader-active">
                        <div class="loader-icon"></div>
                        <p>Memvisualisasikan Rute...</p>
                    </div>
                </div>
                <div class="v8-details-container">
                    <div class="v8-summary-card">
                        <h4><i class="fa-solid fa-flag-checkered"></i> Status Pengiriman</h4>
                        <p class="current-status" style="color:var(--v8-accent-green);">${d.status}</p>
                    </div>
                    <div class="v8-summary-card">
                        <h4><i class="fa-solid fa-box-open"></i> Pengirim</h4>
                        <p>${d.sender.name}</p>
                    </div>
                    <div class="v8-summary-card">
                        <h4><i class="fa-solid fa-house-chimney"></i> Penerima</h4>
                        <p>${d.receiver.name}</p>
                    </div>
                    <div class="v8-history-list">
                        <h4><i class="fa-solid fa-timeline"></i> Riwayat Perjalanan</h4>
                        <ul>${historyListHtml || '<li class="v8-history-item"><p>Tidak ada riwayat.</p></li>'}</ul>
                    </div>
                </div>
            </div>`;
    }

    _startPolling() {
        this._stopPolling();
        this.pollingId = setInterval(() => this._fetchLatestData(), this.pollingInterval);
    }

    _stopPolling() {
        if (this.pollingId) {
            clearInterval(this.pollingId);
            this.pollingId = null;
        }
    }

    async _fetchLatestData() {
        if (!this.currentReceiptNumber) return;
        const refreshIndicator = this.modalContent.querySelector('.v8-refresh-indicator');
        if (refreshIndicator) refreshIndicator.classList.add('is-refreshing');

        try {
            const response = await fetch(`api/track.php?resi=${encodeURIComponent(this.currentReceiptNumber)}`);
            if (!response.ok) return;
            const data = await response.json();
            if (data.success && data.data) {
                this.modalContent.innerHTML = this._buildMapModalHtml(data.data);
                this._bindCloseButton();
                setTimeout(() => this._initializeTrackingMap(data.data), 50);
            }
        } catch (error) {
            console.error('Error during polling:', error);
        } finally {
            if (refreshIndicator) {
                setTimeout(() => refreshIndicator.classList.remove('is-refreshing'), 500);
            }
        }
    }

    async _initializeTrackingMap(d) {
        const mapLoader = document.getElementById('map-loader');
        if (this.map) {
            this.map.remove();
            this.map = null;
        }

        // 1. Ambil data koordinat langsung dari data object (d)
        const currentLat = d.current_lat;
        const currentLong = d.current_long;
        const destLat = d.dest_lat;
        const destLong = d.dest_long;

        // Cek apakah koordinat lokasi terkini tersedia
        if (currentLat && currentLong) {
            document.getElementById('trackingMapV8').style.display = 'block';
            if (mapLoader) mapLoader.classList.remove('map-loader-active');

            // Inisialisasi Peta
            this.map = L.map('trackingMapV8');
            
            // Tambahkan Tile Layer (Tampilan Peta)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(this.map);

            const bounds = []; // Array untuk menampung semua titik marker

            // --- MARKER 1: LOKASI TERKINI (Truk Berjalan) ---
            const truckIcon = L.icon({
                iconUrl: 'https://cdn-icons-png.flaticon.com/512/713/713311.png', // Ikon Truk
                iconSize: [38, 38],
                iconAnchor: [19, 38],
                popupAnchor: [0, -30]
            });

            const markerCurrent = L.marker([currentLat, currentLong], {icon: truckIcon})
                .addTo(this.map)
                .bindPopup(`<b>Lokasi Terkini</b><br>${d.current_location}`)
                .openPopup();
            
            bounds.push([currentLat, currentLong]); // Masukkan ke batas pandang

            // --- MARKER 2: TUJUAN (Jika ada datanya) ---
            if (destLat && destLong) {
                const destIcon = L.icon({
                    iconUrl: 'https://cdn-icons-png.flaticon.com/512/684/684908.png', // Ikon Lokasi Merah
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                });

                L.marker([destLat, destLong], {icon: destIcon})
                    .addTo(this.map)
                    .bindPopup(`<b>Tujuan: ${d.receiver.address}</b>`);
                
                bounds.push([destLat, destLong]); // Masukkan ke batas pandang
                
                // GAMBAR GARIS RUTE (Garis Putus-putus)
                const latlngs = [
                    [currentLat, currentLong],
                    [destLat, destLong]
                ];
                L.polyline(latlngs, {
                    color: 'blue',
                    weight: 4,
                    opacity: 0.7,
                    dashArray: '10, 10' // Efek garis putus-putus
                }).addTo(this.map);
            }

            // --- FITUR UTAMA: AUTO ZOOM ---
            this.map.fitBounds(bounds, {padding: [50, 50]});

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 100);

        } else {
            // Jika tidak ada koordinat, tampilkan pesan
             if (mapLoader) {
                mapLoader.innerHTML = '<p style="text-align:center; padding-top:20px;">Lokasi peta belum tersedia untuk resi ini.</p>';
             }
        }
    }

    async _geocodeLocation(locationName) {
        if (!locationName) return null;
        if (this.geocodeCache.has(locationName)) {
            return this.geocodeCache.get(locationName);
        }
        try {
            const response = await fetch(`api/geocode.php?location=${encodeURIComponent(locationName)}`);
            if (!response.ok) {
                this.geocodeCache.set(locationName, null);
                return null;
            }
            const data = await response.json();
            if (data && data.length > 0) {
                const coords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                this.geocodeCache.set(locationName, coords);
                return coords;
            }
            this.geocodeCache.set(locationName, null);
            return null;
        } catch (error) {
            console.error("Geocoding error:", error);
            return null;
        }
    }

    _formatDate(dateString) {
        if (!dateString) return 'N/A';
        try {
            const d = new Date(dateString);
            return d.toLocaleDateString('id-ID', {
                year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        } catch (e) {
            return dateString;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Tracking();
});