document.addEventListener('DOMContentLoaded', () => {
    console.log('confirm.js chargé');

    const modal = document.getElementById('confirmDeleteModal');
    if (!modal) {
        console.warn('Modal introuvable');
        return;
    }

    const form  = document.getElementById('confirmDeleteForm');
    const token = document.getElementById('confirmDeleteToken');
    const msg   = document.getElementById('confirmDeleteMessage');

    modal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;

        if (!button) return;

        form.action = button.dataset.action;
        token.value = button.dataset.token;
        msg.textContent = button.dataset.message;
    });
});
