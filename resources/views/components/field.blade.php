@props(['name', 'label', 'hint' => null])

<div {{ $attributes }}>
    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    {{ $slot }}
    @if ($hint)
        <p class="field-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
