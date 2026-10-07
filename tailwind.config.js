export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            colors: {
                canvas: 'var(--bg)',
                surface: 'var(--surface)',
                ink: 'var(--ink)',
                muted: 'var(--muted)',
                line: 'var(--line)',
                accent: 'var(--accent)',
                'on-accent': 'var(--on)',
                soft: 'var(--soft)',
                danger: 'var(--err)',
                panel: 'var(--panel)',
                'panel-ink': 'var(--panel-ink)',
                'panel-mute': 'var(--panel-mute)',
                'panel-line': 'var(--panel-line)',
            },
            fontFamily: {
                display: ['"Bricolage Grotesque"', 'system-ui', 'sans-serif'],
                sans: ['Figtree', 'system-ui', 'sans-serif'],
            },
        },
    },
};