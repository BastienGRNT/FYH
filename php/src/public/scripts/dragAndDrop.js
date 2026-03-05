document.addEventListener("DOMContentLoaded", () => {
    const containerComparaison = document.getElementById("compare-container")

    let cards = document.querySelectorAll('.cardDraggable');
    let dragZone;
    let comparableCards = [];
    let clientX;
    let clientY

    containerComparaison.addEventListener("click", async () => {
        if (comparableCards.length > 0) {
            const ids = comparableCards.join(',');
            window.location.href = `/hackatons/compare?ids=${ids}`;
        }
    });

    cards.forEach(card => {
        card.setAttribute('draggable', 'true');

        card.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = "copy";
        });

        card.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', e.currentTarget.id);
            dragZone = createDragZone();
        });

        card.addEventListener('drag', (e) => {
            if (e.clientX !== 0 && e.clientY !== 0) {
                clientX = e.clientX;
                clientY = e.clientY;
            }
        });

        card.addEventListener('dragend', (e) => {
            if (dragZone) {
                const rect = dragZone.getBoundingClientRect();

                if (
                    clientX > rect.left &&
                    clientX < rect.right &&
                    clientY > rect.top &&
                    clientY < rect.bottom
                ) {
                    const hackathonId = e.currentTarget.id;

                    if (!comparableCards.includes(hackathonId)) {
                        comparableCards.push(hackathonId);

                        const container = document.getElementById('compare-container');
                        const countSpan = document.getElementById('compare-count');

                        if (comparableCards.length > 0) {
                            container.style.display = 'block';
                            countSpan.innerText = comparableCards.length;
                        }
                    }
                }

                dragZone.remove();
            }
            console.log("Cartes à comparer :", comparableCards);
        });
    });
});

function createDragZone() {
    const dragZone = document.createElement("div");
    dragZone.setAttribute('id', 'drag-zone');

    dragZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy"
    });

    Object.assign(dragZone.style, {
        position: 'fixed',
        top: '-100px',
        left: '50%',
        transform: 'translateX(-50%) scale(1)',
        width: '60%',
        height: '80px',
        backgroundColor: '#00d2ff',
        color: 'white',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        borderRadius: '0 0 15px 15px',
        boxShadow: '0 4px 10px rgba(0,0,0,0.2)',
        zIndex: '9999',
        transition: 'top 0.5s ease, transform 0.3s ease',
        fontWeight: 'bold',
        cursor: 'pointer'
    });

    dragZone.addEventListener('dragenter', () => {
        dragZone.style.transform = 'translateX(-50%) scale(1.1)';
        dragZone.style.backgroundColor = '#00b8e6';
    });

    dragZone.addEventListener('dragleave', () => {
        dragZone.style.transform = 'translateX(-50%) scale(1)';
        dragZone.style.backgroundColor = '#00d2ff';
    });

    dragZone.innerHTML = "Déposez ici pour comparer";
    document.body.appendChild(dragZone);

    setTimeout(() => {
        dragZone.style.top = '0';
    }, 100);

    return dragZone;
}