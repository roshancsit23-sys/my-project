// SmartGov Market - Leaflet & OpenStreetMap GIS Integration
// File: assets/js/map.js

let smartGovMap = null;
let currentMarker = null;

function initLocationPicker(mapContainerId, latInputId, lngInputId, initialLat = 27.7172, initialLng = 85.3240) {
    const mapElement = document.getElementById(mapContainerId);
    if (!mapElement) return;

    smartGovMap = L.map(mapContainerId).setView([initialLat, initialLng], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | SmartGov GIS'
    }).addTo(smartGovMap);

    currentMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(smartGovMap);

    function updateInputs(lat, lng) {
        const latInput = document.getElementById(latInputId);
        const lngInput = document.getElementById(lngInputId);
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);
    }

    updateInputs(initialLat, initialLng);

    currentMarker.on('dragend', function (e) {
        const coord = e.target.getLatLng();
        updateInputs(coord.lat, coord.lng);
    });

    smartGovMap.on('click', function (e) {
        currentMarker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });
}

function initGISOverviewMap(mapContainerId, locations = []) {
    const mapElement = document.getElementById(mapContainerId);
    if (!mapElement) return;

    const defaultLat = locations.length > 0 ? locations[0].lat : 27.7172;
    const defaultLng = locations.length > 0 ? locations[0].lng : 85.3240;

    smartGovMap = L.map(mapContainerId).setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap | SmartGov Market GIS'
    }).addTo(smartGovMap);

    locations.forEach(loc => {
        if (!loc.lat || !loc.lng) return;
        
        let markerColor = 'blue';
        if (loc.type === 'vendor') markerColor = 'green';
        if (loc.type === 'complaint') markerColor = 'red';

        const marker = L.marker([loc.lat, loc.lng]).addTo(smartGovMap);
        
        let popupHtml = `<div style="max-width: 220px;">
            <h6 style="margin-bottom:4px; font-weight:700;">${loc.title}</h6>
            <p style="font-size:0.85rem; margin-bottom:4px; color:#475569;">${loc.address || ''}</p>
            <span class="badge ${loc.type === 'vendor' ? 'bg-success' : 'bg-danger'}">${loc.category || loc.type}</span>
            ${loc.link ? `<br><a href="${loc.link}" class="btn btn-sm btn-primary mt-2" style="font-size:0.75rem;">View Details</a>` : ''}
        </div>`;
        
        marker.bindPopup(popupHtml);
    });
}
