@props(['label' => null, 'required' => false])

@php $isPassword = $attributes->get('type') === 'password'; @endphp

<x-field :label="$label" :required="$required" {{ $attributes->only(['name', 'id']) }}>
    <div class="relative">
        <input {{ $attributes->whereDoesntStartWith('label')->merge(['class' => $isPassword ? 'input !pr-11' : 'input']) }}>
        @if ($isPassword)
            <button type="button" x-data="{ show: false }"
                    @click="const input = $el.closest('.relative').querySelector('input'); input.type = show ? 'password' : 'text'; show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-ink-400 transition-colors hover:text-ink-600 dark:text-ink-200 dark:hover:text-ink-50"
                    aria-label="Toggle password visibility">
                <x-icon name="eye" class="size-4" x-show="!show" />
                <x-icon name="eye-slash" class="size-4" x-show="show" x-cloak />
            </button>
        @endif
    </div>
</x-field>
