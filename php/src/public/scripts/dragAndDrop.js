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

    const innerZone = document.createElement("div");

    dragZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        e.dataTransfer.dropEffect = "copy";
    });

    Object.assign(dragZone.style, {
        position: 'fixed',
        top: '-120px',
        left: '50%',
        transform: 'translateX(-50%) scale(1)',
        width: '50%',
        height: '90px',
        padding: '12px',
        backgroundColor: '#ffffff',
        borderRadius: '0 0 16px 16px',
        boxShadow: '0 15px 35px rgba(0,0,0,0.12)',
        zIndex: '9999',
        transition: 'top 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), transform 0.3s ease',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center'
    });

    Object.assign(innerZone.style, {
        width: '100%',
        height: '100%',
        border: '1.5px dashed #d1d5db',
        borderRadius: '0 0 12px 12px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: '#374151',
        fontSize: '13px',
        fontWeight: '500',
        textTransform: 'uppercase',
        letterSpacing: '0.5px',
        pointerEvents: 'none'
    });

    innerZone.innerText = "Glisser ici pour comparer";
    dragZone.appendChild(innerZone);

    dragZone.addEventListener('dragenter', () => {
        dragZone.style.transform = 'translateX(-50%) scale(1.02)';
        innerZone.style.borderColor = '#00d2ff';
        innerZone.style.color = '#00d2ff';
        innerZone.style.backgroundColor = '#f9fafb';
    });

    dragZone.addEventListener('dragleave', () => {
        dragZone.style.transform = 'translateX(-50%) scale(1)';
        innerZone.style.borderColor = '#d1d5db';
        innerZone.style.color = '#374151';
        innerZone.style.backgroundColor = 'transparent';
    });

    document.body.appendChild(dragZone);

    setTimeout(() => {
        dragZone.style.top = '0';
    }, 50);

    return dragZone;
}