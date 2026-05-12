@props(['options' => [], 'selected' => null, 'placeholder' => '-- Pilih --'])

<select {{ $attributes->merge(['class' => 'w-full px-3 py-2 border border-line rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-accent/30 focus:border-accent']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach ($options as $value => $label)
        <option value="{{ is_numeric($value) ? $label : $value }}" {{ (is_numeric($value) ? $label : $value) == $selected ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
