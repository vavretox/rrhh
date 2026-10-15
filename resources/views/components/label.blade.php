@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300 pb-3']) }}>
    {{ $value ?? $slot }}
</label>
