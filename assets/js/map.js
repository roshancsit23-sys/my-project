// SmartGov Market - Leaflet & OpenStreetMap GIS Integration
// File: assets/js/map.js

let smartGovMap = null;
let currentMarker = null;

function initLocationPicker(mapContainerId, latInputId, lngInputId, initialLat = 27.7172, initialLng = 85.3240, addressInputId = null) {
    const mapElement = document.getElementById(mapContainerId);
    if (!mapElement) return;

    if (smartGovMap) {
        try { smartGovMap.remove(); } catch (e) {}
    }

    smartGovMap = L.map(mapContainerId).setView([initialLat, initialLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | SmartGov GIS'
    }).addTo(smartGovMap);

    currentMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(smartGovMap);
    currentMarker.bindPopup("Drag pin or click map to set exact incident location").openPopup();

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
        tryReverseGeocode(coord.lat, coord.lng, addressInputId);
    });

    smartGovMap.on('click', function (e) {
        currentMarker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
        tryReverseGeocode(e.latlng.lat, e.latlng.lng, addressInputId);
    });
}

function useCurrentLocation(latInputId, lngInputId, addressInputId = null) {
    if (!navigator.geolocation) {
        alert("Geolocation is not supported by your browser.");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;

            const latInput = document.getElementById(latInputId);
            const lngInput = document.getElementById(lngInputId);
            if (latInput) latInput.value = lat.toFixed(6);
            if (lngInput) lngInput.value = lng.toFixed(6);

            if (smartGovMap && currentMarker) {
                smartGovMap.setView([lat, lng], 15);
                currentMarker.setLatLng([lat, lng]);
                currentMarker.bindPopup("Your current detected location").openPopup();
            }

            tryReverseGeocode(lat, lng, addressInputId);
        },
        function (error) {
            console.warn("Geolocation warning:", error.message);
            alert("Could not retrieve your precise location. Please drag the pin on the map or enter your address.");
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
}

function tryReverseGeocode(lat, lng, addressInputId) {
    if (!addressInputId) return;
    const addrField = document.getElementById(addressInputId);
    if (!addrField || addrField.value.trim().length > 5) return; // Keep user's custom address if already entered

    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`, {
        headers: { 'Accept-Language': 'en' }
    })
    .then(res => res.json())
    .then(data => {
        if (data && data.display_name && addrField && addrField.value.trim().length === 0) {
            addrField.value = data.display_name.split(',').slice(0, 3).join(', ').trim();
        }
    })
    .catch(e => {
        // Silently ignore geocoding failure fallback
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
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | SmartGov Market GIS'
    }).addTo(smartGovMap);

    const bounds = [];

    locations.forEach(loc => {
        if (!loc.lat || !loc.lng) return;
        bounds.push([loc.lat, loc.lng]);

        const marker = L.marker([loc.lat, loc.lng]).addTo(smartGovMap);
        
        const badgeBg = loc.type === 'vendor' ? '#10b981' : '#ef4444';
        const popupHtml = `
            <div style="min-width: 200px; padding: 4px;">
                <span style="display:inline-block; font-size:0.75rem; font-weight:700; color:#fff; background:${badgeBg}; padding:2px 8px; border-radius:12px; margin-bottom:6px;">
                    ${loc.type === 'vendor' ? 'Verified Vendor Store' : 'Registered Grievance'}
                </span>
                <h6 style="margin: 0 0 4px 0; font-weight: 700; color: #0b1d3a; font-size: 0.95rem;">${loc.title}</h6>
                <p style="font-size: 0.85rem; margin: 0 0 6px 0; color: #475569;">
                    <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i>${loc.address || ''}
                </p>
                <div style="font-size:0.75rem; color:#64748b; margin-bottom:8px;">
                    <strong>GPS:</strong> ${loc.lat.toFixed(4)}, ${loc.lng.toFixed(4)}
                </div>
                ${loc.link ? `<a href="${loc.link}" class="btn btn-sm btn-primary" style="font-size: 0.75rem; padding: 3px 10px; border-radius: 6px; text-decoration:none; color:#fff; display:inline-block;">View Details</a>` : ''}
            </div>
        `;
        
        marker.bindPopup(popupHtml);
    });

    if (bounds.length > 1) {
        smartGovMap.fitBounds(bounds, { padding: [30, 30] });
    }
}
