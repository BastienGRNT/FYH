document.addEventListener("DOMContentLoaded", () => {

    const map = L.map('hackathon-map').setView([46.603354, 1.888334], 6);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 20
    }).addTo(map);

    const allMarkers = [];

    const customIcon = L.divIcon({
        className: 'custom-marker',
        html: `<div class="marker-dot"></div>`,
        iconSize: [20, 20],
        iconAnchor: [10, 10]
    });

    function checkZoom() {
        const currentZoom = map.getZoom();
        allMarkers.forEach(marker => {
            if (currentZoom >= 7) {
                marker.openTooltip();
            } else {
                marker.closeTooltip();
            }
        });
    }

    fetch('/api/hackathons')
        .then(response => response.json())
        .then(data => {
            data.forEach(h => {
                if (h.latitude && h.longitude) {
                    const marker = L.marker([h.latitude, h.longitude], { icon: customIcon }).addTo(map);

                    marker.bindTooltip(escapeHTML(h.nom), {
                        direction: 'top',
                        offset: [0, -10],
                        className: 'transparent-tooltip',
                        permanent: false
                    });

                    const popupContent = `
                        <div style="min-width: 150px;">
                            <h6 style="margin: 0 0 5px 0;">${escapeHTML(h.nom)}</h6>
                            <p style="margin: 0 0 10px 0; color: #666; font-size: 12px;">${escapeHTML(h.ville)}</p>
                            <div style="font-size: 13px;">
                                <strong>Prix :</strong> ${h.prix ? escapeHTML(h.prix) + ' €' : 'Gratuit'}<br>
                                <strong>Email :</strong> <a href="mailto:${escapeHTML(h.email_organisateur)}">${escapeHTML(h.email_organisateur)}</a>
                            </div>
                        </div>
                    `;

                    marker.bindPopup(popupContent);
                    allMarkers.push(marker);
                }
            });

            checkZoom();
        })
        .catch(error => console.error("Erreur réseau (Leaflet) :", error));

    map.on('zoomend', checkZoom);

    const tabMap = document.getElementById('tab-map');
    if (tabMap) {
        tabMap.addEventListener('shown.bs.tab', () => {
            map.invalidateSize();
        });
    }
});