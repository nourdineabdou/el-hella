/**
 * Shared mobile UX behaviours: keyboard-aware scrolling, enterkeyhint chaining,
 * autofocus suppression on touch devices, and a generic double-submit guard.
 */

function initKeyboardAwareness() {
    if (!window.visualViewport) {
        return;
    }

    const threshold = 120;

    const handleResize = () => {
        const shrink = window.innerHeight - window.visualViewport.height;
        document.body.classList.toggle('eh-keyboard-open', shrink > threshold);
    };

    window.visualViewport.addEventListener('resize', handleResize);
}

function initFocusScroll() {
    document.addEventListener('focusin', (event) => {
        if (!window.matchMedia('(max-width: 991.98px)').matches) {
            return;
        }

        const el = event.target;
        if (!el.matches || !el.matches('input, select, textarea')) {
            return;
        }

        window.setTimeout(() => {
            el.scrollIntoView({ block: 'center', behavior: 'smooth' });
        }, 300);
    });
}

function initEnterKeyChaining() {
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.defaultPrevented) {
            return;
        }

        const el = event.target;
        if (!el.matches || !el.matches('input')) {
            return;
        }

        if (el.getAttribute('enterkeyhint') !== 'next') {
            return;
        }

        const form = el.form;
        if (!form) {
            return;
        }

        const focusable = Array.from(form.querySelectorAll('input, select, textarea'))
            .filter((field) => !field.disabled && field.type !== 'hidden' && field.offsetParent !== null);

        const next = focusable[focusable.indexOf(el) + 1];
        if (next) {
            event.preventDefault();
            next.focus();
        }
    });
}

function initAutofocusSuppression() {
    if (!window.matchMedia('(max-width: 767.98px)').matches) {
        return;
    }

    const active = document.activeElement;
    if (active && active !== document.body && active.hasAttribute && active.hasAttribute('autofocus')) {
        active.blur();
    }
}

function initConfirmSubmit() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.dataset.confirm || form.dataset.confirmed) {
            return;
        }

        event.preventDefault();

        const swal = window.Swal;
        if (!swal) {
            form.submit();
            return;
        }

        swal.fire({
            icon: 'warning',
            title: form.dataset.confirmTitle || 'Are you sure?',
            text: form.dataset.confirm,
            showCancelButton: true,
            confirmButtonText: form.dataset.confirmYes || 'Confirm',
            cancelButtonText: form.dataset.confirmNo || 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                form.dataset.confirmed = 'true';
                form.submit();
            }
        });
    });
}

function initDoubleSubmitGuard() {
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || event.defaultPrevented) {
            return;
        }

        if (form.dataset.confirm && !form.dataset.confirmed) {
            return;
        }

        const submitBtn = form.querySelector('button[type="submit"]');
        if (!submitBtn || submitBtn.disabled) {
            return;
        }

        if (!submitBtn.dataset.defaultHtml) {
            submitBtn.dataset.defaultHtml = submitBtn.innerHTML;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ${submitBtn.dataset.defaultHtml}`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initKeyboardAwareness();
    initFocusScroll();
    initEnterKeyChaining();
    initAutofocusSuppression();
    initConfirmSubmit();
    initDoubleSubmitGuard();
});
