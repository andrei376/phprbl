@props(['value','for'])

<label for="{{ $for }}" {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 '.($errors->has($for) ? 'text-red-700':'')]) }}>
    {{ $value ?? $slot }}
</label>
