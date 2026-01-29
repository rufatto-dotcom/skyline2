document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('tbody');
    if (!tbody) return;

    let draggingRow = null;

    tbody.querySelectorAll('tr[data-field]').forEach(row => {
        row.draggable = true;

        row.addEventListener('dragstart', e => {
            draggingRow = row;
            row.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });

        row.addEventListener('dragend', () => {
            draggingRow = null;
            row.classList.remove('dragging');
            recalcOrder();
        });

        row.addEventListener('dragover', e => {
            e.preventDefault();
            const target = e.currentTarget;
            if (!draggingRow || target === draggingRow) return;

            const rect = target.getBoundingClientRect();
            const next = (e.clientY - rect.top) > rect.height / 2;

            tbody.insertBefore(
                draggingRow,
                next ? target.nextSibling : target
            );
        });
    });

    function recalcOrder() {
        tbody.querySelectorAll('tr[data-field]').forEach((row, index) => {
            const field = row.dataset.field;
            const input = document.querySelector(
                `input[data-order-field="${field}"]`
            );

            if (input) {
                input.value = index;
            }
        });
    }
});
