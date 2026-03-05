document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById('admin-search');
    const tableBody = document.getElementById('hackathon-table-body');

    const csrfToken = searchInput ? searchInput.dataset.token : '';

    if (searchInput && tableBody) {
        searchInput.addEventListener('input', async (e) => {
            const term = e.target.value;
            try {
                const response = await fetch(`/admin/search?q=${encodeURIComponent(term)}`);
                const hackathons = await response.json();

                tableBody.innerHTML = '';

                if (hackathons.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5" class="text-center">Aucun événement trouvé.</td></tr>';
                    return;
                }

                hackathons.forEach(h => {
                    const dateObj = new Date(h.date_event);
                    const dateStr = dateObj.toLocaleDateString('fr-FR');

                    const row = `
                        <tr>
                            <td>${h.id}</td>
                            <td><strong>${escapeHTML(h.nom)}</strong></td>
                            <td>${dateStr}</td>
                            <td>${escapeHTML(h.ville)}</td>
                            <td class="text-end">
                                <a href="/admin/hackathon/edit?id=${h.id}" class="btn btn-sm btn-warning" aria-label="Modifier ${escapeHTML(h.nom)}">Modifier</a>
                                <form action="/admin/hackathon/delete" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                    <input type="hidden" name="csrf_token" value="${csrfToken}">
                                    <input type="hidden" name="id" value="${h.id}">
                                    <button type="submit" class="btn btn-sm btn-danger" aria-label="Supprimer ${escapeHTML(h.nom)}">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    `;
                    tableBody.insertAdjacentHTML('beforeend', row);
                });
            } catch (error) {
                console.error("Erreur AJAX :", error);
            }
        });
    }
});