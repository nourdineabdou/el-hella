import 'leaflet/dist/leaflet.css';
import L from 'leaflet';

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    const mapEl = document.querySelector('#admin-map');
    if (!mapEl) {
        return;
    }

    const data = window.adminMapData || { shops: [], distributors: [], labels: {} };
    const labels = data.labels || {};

    const map = L.map(mapEl, { zoomControl: true });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    const shopIcon = L.divIcon({
        className: 'eh-map-marker eh-map-marker-shop',
        html: '<i class="bi bi-shop"></i>',
        iconSize: [34, 34],
        iconAnchor: [17, 34],
        popupAnchor: [0, -32],
    });

    const distributorIcon = L.divIcon({
        className: 'eh-map-marker eh-map-marker-distributor',
        html: '<i class="bi bi-person-fill"></i>',
        iconSize: [34, 34],
        iconAnchor: [17, 34],
        popupAnchor: [0, -32],
    });

    const shopLayer = L.layerGroup();
    const distributorLayer = L.layerGroup();
    const bounds = [];

    (data.shops || []).forEach((shop) => {
        if (typeof shop.latitude !== 'number' || typeof shop.longitude !== 'number') {
            return;
        }

        L.marker([shop.latitude, shop.longitude], { icon: shopIcon })
            .bindPopup(`
                <div class="eh-map-popup">
                    <div class="fw-bold">${escapeHtml(shop.name)}</div>
                    <div class="text-muted small">${escapeHtml(shop.owner_name)}</div>
                    <div class="text-muted small">${escapeHtml(shop.phone)}</div>
                </div>
            `)
            .addTo(shopLayer);

        bounds.push([shop.latitude, shop.longitude]);
    });

    (data.distributors || []).forEach((distributor) => {
        if (typeof distributor.latitude !== 'number' || typeof distributor.longitude !== 'number') {
            return;
        }

        L.marker([distributor.latitude, distributor.longitude], { icon: distributorIcon })
            .bindPopup(`
                <div class="eh-map-popup">
                    <div class="fw-bold">${escapeHtml(distributor.name)}</div>
                    ${distributor.zone ? `<div class="text-muted small"><i class="bi bi-geo-alt"></i> ${escapeHtml(distributor.zone)}</div>` : ''}
                    ${distributor.last_shop_name ? `<div class="text-muted small">${escapeHtml(labels.lastVisitAt || '')} ${escapeHtml(distributor.last_shop_name)}</div>` : ''}
                    <div class="text-muted small">${escapeHtml(labels.lastSeen || '')} ${escapeHtml(distributor.last_location_at)}</div>
                </div>
            `)
            .addTo(distributorLayer);

        bounds.push([distributor.latitude, distributor.longitude]);
    });

    shopLayer.addTo(map);
    distributorLayer.addTo(map);

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
    } else {
        map.setView([18.08, -15.98], 12);
    }

    const overlays = {};
    overlays[labels.shops || 'Shops'] = shopLayer;
    overlays[labels.distributors || 'Distributors'] = distributorLayer;

    L.control.layers(null, overlays, { collapsed: false }).addTo(map);
});
