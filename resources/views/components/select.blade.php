@props(['label' => null, 'required' => false, 'placeholder' => 'Select an option'])

<x-field :label="$label" :required="$required" {{ $attributes->only(['name', 'id']) }}>
    <select {{ $attributes->whereDoesntStartWith('label')->merge(['class' => 'input appearance-none']) }}>
        @if ($placeholder !== false)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif
        {{ $slot }}
    </select>
</x-field>
