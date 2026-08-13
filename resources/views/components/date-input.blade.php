@props(['name', 'value' => null, 'disabled' => false])

{{--
    Native <input type="date"> can't be typed into on iOS Safari — it only
    opens the wheel picker. This pairs a typable text field (masked as
    dd/mm/yyyy) with a hidden native date input kept in sync by
    resources/js/date-field.js, plus a button to open the native picker for
    people who prefer tapping a calendar.
--}}
<div class="eh-date-field">
    <input
        type="text"
        inputmode="numeric"
        autocomplete="off"
        placeholder="{{ __('admin.date_placeholder') }}"
        class="form-control eh-date-text"
        @disabled($disabled)
    >
    <input
        type="date"
        name="{{ $name }}"
        value="{{ $value }}"
        class="eh-date-native"
        tabindex="-1"
        aria-hidden="true"
        @disabled($disabled)
        {{ $attributes }}
    >
    <button type="button" class="eh-date-calendar-btn" tabindex="-1" aria-label="{{ __('admin.open_calendar') }}" @disabled($disabled)>
        <i class="bi bi-calendar3"></i>
    </button>
</div>
