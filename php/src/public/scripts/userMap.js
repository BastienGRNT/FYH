document.addEventListener("DOMContentLoaded", function () {
    const mapContainer = document.getElementById('map');
    if (!mapContainer) return;

    const latAttr = mapContainer.dataset.lat;
    const lngAttr = mapContainer.dataset.lng;
    const hasMarker = mapContainer.dataset.exists === '1';

    const franceLat = 46.603354;
    const franceLng = 1.888334;


    const startLat = hasMarker ? parseFloat(latAttr) : franceLat;
    const startLng = hasMarker ? parseFloat(lngAttr) : franceLng;
    const zoomLevel = hasMarker ? 13 : 6;

    const map = L.map('map').setView([startLat, startLng], zoomLevel);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    let marker;

    if (hasMarker) {
        marker = L.marker([startLat, startLng]).addTo(map);
    }

    const latInput = document.getElementById('latInput');
    const lngInput = document.getElementById('lngInput');

    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;

        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(map);
        }
    });
});