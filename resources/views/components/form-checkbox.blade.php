@props(['options' => [], 'selected' => [], 'name'])

<div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
    @foreach ($options as $opt)
        <label class="flex items-center gap-2 p-3 border border-line rounded-xl cursor-pointer hover:border-accent/40 hover:bg-accent/5 transition has-[:checked]:border-accent has-[:checked]:bg-accent/5">
            <input type="checkbox" name="{{ $name }}[]" value="{{ $opt }}"
                class="accent-accent shrink-0"
                {{ in_array($opt, (array) $selected) ? 'checked' : '' }}>
            <span class="text-sm text-primary">{{ $opt }}</span>
        </label>
    @endforeach
</div>
