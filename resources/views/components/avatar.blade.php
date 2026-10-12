@props(['user'])
<span
    {{ $attributes->class('grid shrink-0 place-items-center overflow-hidden rounded-full bg-soft font-display font-bold text-ink') }}>
    @if ($url = $user->avatarUrl())
        <img src="{{ $url }}" alt="" class="size-12 object-cover">
    @else
        {{ $user->initials() }}
    @endif
</span>
