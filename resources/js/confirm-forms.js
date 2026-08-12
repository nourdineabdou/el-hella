// Any <form data-confirm="..."> asks for a SweetAlert2 confirmation before
// submitting, instead of the browser's plain confirm(). Used for admin
// actions that are hard to reverse (deactivating a distributor, cancelling
// a sale).
document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
        return;
    }

    if (form.dataset.confirmed === 'true') {
        return;
    }

    event.preventDefault();

    window.Swal.fire({
        title: form.dataset.confirmTitle || '',
        text: form.dataset.confirm,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmYes || 'OK',
        cancelButtonText: form.dataset.confirmNo || 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            form.dataset.confirmed = 'true';
            form.submit();
        }
    });
});
