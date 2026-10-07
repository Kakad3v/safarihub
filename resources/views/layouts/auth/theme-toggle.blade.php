<button type="button" x-data="{
    dark: (document.documentElement.dataset.theme ?? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')) === 'dark',
    toggle() {
        this.dark = !this.dark;
        const theme = this.dark ? 'dark' : 'light';
        document.documentElement.dataset.theme = theme;
        localStorage.setItem('theme', theme);
    },
}" x-on:click="toggle()" x-text="dark ? 'Light' : 'Dark'"
    x-bind:aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
    class="rounded-full border border-line bg-surface px-3 py-[7px] text-[13px] font-semibold text-ink"></button>
