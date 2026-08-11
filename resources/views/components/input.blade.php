@props(['label' => null, 'required' => false])

<x-field :label="$label" :required="$required" {{ $attributes->only(['name', 'id']) }}>
    <input {{ $attributes->whereDoesntStartWith('label')->merge(['class' => 'input']) }}>
</x-field>
