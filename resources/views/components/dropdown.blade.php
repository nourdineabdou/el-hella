@props(['align' => 'right', 'width' => '48', 'contentClasses' => 'py-1'])

@php
$alignmentClass = $align === 'left' ? 'dropdown-menu-start' : ($align === 'top' ? '' : 'dropdown-menu-end');
@endphp

<div class="dropdown">
    <div class="dropdown-toggle" style="cursor:pointer" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $trigger }}
    </div>

    <div class="dropdown-menu {{ $alignmentClass }} {{ $contentClasses }} shadow-sm">
        {{ $content }}
    </div>
</div>
