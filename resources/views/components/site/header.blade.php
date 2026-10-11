<header class="sticky top-0 z-10 border-b border-line bg-canvas">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-3 px-4 py-3">
        <a href="{{ route('home') }}" wire:navigate class="font-display text-xl font-bold tracking-tight">SafariHub</a>
        <div class="flex items-center gap-2">
            <x-auth.theme-toggle />
            @auth
                <span
                    class="hidden text-sm font-semibold text-muted sm:inline">{{ str(auth()->user()->name)->before(' ') }}</span>
            @else
                <a href="{{ route('login') }}" wire:navigate
                    class="rounded-full bg-accent px-3.5 py-[7px] text-[13px] font-semibold text-on-accent">Sign in</a>
            @endauth
        </div>
    </div>
</header>
