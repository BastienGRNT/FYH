document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('search');
    const listContainer = document.getElementById('hackathon-list');

    if (searchInput && listContainer) {
        searchInput.addEventListener('input', async (e) => {
            const term = e.target.value;
            try {
                const response = await fetch(`/search?q=${encodeURIComponent(term)}`);
                const hackathons = await response.json();

                listContainer.innerHTML = '';

                if (hackathons.length === 0) {
                    listContainer.innerHTML = '<div class="col-12"><div class="alert alert-info">Aucun événement trouvé.</div></div>';
                    return;
                }

                hackathons.forEach(h => {
                    const dateObj = new Date(h.date_event);
                    const dateStr = dateObj.toLocaleDateString('fr-FR');
                    const prix = h.prix > 0 ? `${h.prix} €` : 'Gratuit';
                    const img = h.photo_url ? h.photo_url : 'https://via.placeholder.com/300x150';

                    const card = `
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <img src="${img}" class="card-img-top" alt="${escapeHTML(h.nom)}" style="height: 200px; object-fit: cover; width: 100%;">
                                <div class="card-body">
                                    <h5 class="card-title">${escapeHTML(h.nom)}</h5>
                                    <p class="card-text text-truncate">${escapeHTML(h.description)}</p>
                                    <p class="text-muted">📅 ${dateStr}</p>
                                    <p class="fw-bold">${prix}</p>
                                    <a href="/hackathon?id=${h.id}" class="btn btn-primary w-100">Voir détails</a>
                                </div>
                            </div>
                        </div>
                    `;
                    listContainer.insertAdjacentHTML('beforeend', card);
                });
            } catch (error) {
                console.error("Erreur AJAX :", error);
            }
        });
    }
});