@props(['name', 'label', 'type' => 'text', 'placeholder' => null, 'value' => null, 'textarea' => false, 'required' => false, 'prefix' => 'f'])
@php $id = $prefix.'-'.$name; @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    @if ($textarea)
        <textarea id="{{ $id }}" name="{{ $name }}" rows="4" placeholder="{{ $placeholder }}" @required($required)
            @class(['input', 'border-red-600' => $errors->has($name)])
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="e-{{ $id }}" @endif
            {{ $attributes->except('class') }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" @required($required)
            @class(['input', 'border-red-600' => $errors->has($name)])
            @if ($errors->has($name)) aria-invalid="true" aria-describedby="e-{{ $id }}" @endif
            {{ $attributes->except('class') }}>
    @endif
    @error($name)
        <p id="e-{{ $id }}" class="mt-1.5 text-sm text-red-700">{{ $message }}</p>
    @enderror
</div>
