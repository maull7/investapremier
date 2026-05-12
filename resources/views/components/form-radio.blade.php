@props(['options' => [], 'selected' => null, 'name'])

<div class="flex gap-4 mt-2">
    @foreach ($options as $opt)
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="radio" name="{{ $name }}" value="{{ $opt }}"
                class="accent-accent"
                {{ $selected === $opt ? 'checked' : '' }}>
            <span class="text-sm text-primary">{{ $opt }}</span>
        </label>
    @endforeach
</div>
