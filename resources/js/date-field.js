/**
 * Progressive enhancement for .eh-date-field (see components/date-input.blade.php).
 * iOS Safari's native <input type="date"> only opens a wheel picker and
 * cannot be typed into, so we pair a masked dd/mm/yyyy text field with a
 * hidden native date input: typing updates the real (submitted) native
 * input, and using the calendar button/native picker updates the visible
 * text back. Works the same on every platform, calendar is always optional.
 */

function formatIsoToDisplay(iso) {
    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
    if (!match) {
        return '';
    }

    const [, y, m, d] = match;
    return `${d}/${m}/${y}`;
}

function formatDisplayToIso(display) {
    const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(display || '');
    if (!match) {
        return '';
    }

    const [, d, m, y] = match;
    const date = new Date(`${y}-${m}-${d}T00:00:00`);
    const isValid = date.getFullYear() === Number(y)
        && date.getMonth() + 1 === Number(m)
        && date.getDate() === Number(d);

    return isValid ? `${y}-${m}-${d}` : '';
}

function maskDisplayInput(value) {
    const digits = value.replace(/\D/g, '').slice(0, 8);

    if (digits.length > 4) {
        return `${digits.slice(0, 2)}/${digits.slice(2, 4)}/${digits.slice(4)}`;
    }

    if (digits.length > 2) {
        return `${digits.slice(0, 2)}/${digits.slice(2)}`;
    }

    return digits;
}

function openNativePicker(native) {
    if (native.disabled) {
        return;
    }

    if (typeof native.showPicker === 'function') {
        try {
            native.showPicker();
            return;
        } catch {
            // Fall through to focus() below (e.g. not user-activated).
        }
    }

    native.focus();
}

function initDateField(wrapper) {
    const text = wrapper.querySelector('.eh-date-text');
    const native = wrapper.querySelector('.eh-date-native');
    const button = wrapper.querySelector('.eh-date-calendar-btn');

    if (!text || !native) {
        return;
    }

    text.value = formatIsoToDisplay(native.value);

    text.addEventListener('input', () => {
        const masked = maskDisplayInput(text.value);
        if (masked !== text.value) {
            text.value = masked;
        }

        native.value = formatDisplayToIso(text.value);
    });

    native.addEventListener('change', () => {
        text.value = formatIsoToDisplay(native.value);
    });

    button?.addEventListener('click', () => openNativePicker(native));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.eh-date-field').forEach(initDateField);
});
