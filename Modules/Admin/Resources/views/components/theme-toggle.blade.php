<div class="theme-toggle-wrapper inline-flex items-center">
    <v-theme-toggle></v-theme-toggle>
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-theme-toggle-template">
    <div class="theme flex items-center p-0.5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs">
        <button
            type="button"
            @click="setTheme('light')"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            :class="[
                theme === 'light'
                    ? 'active bg-[var(--soft)] text-[var(--teal)] font-bold shadow-xs'
                    : 'text-[var(--muted)] hover:text-[var(--text)]'
            ]"
            title="Light Mode"
            aria-label="Light Mode"
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/>
                <line x1="12" y1="1" x2="12" y2="3"/>
                <line x1="12" y1="21" x2="12" y2="23"/>
                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                <line x1="1" y1="12" x2="3" y2="12"/>
                <line x1="21" y1="12" x2="23" y2="12"/>
                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <span class="text-[11px] leading-none">Light</span>
        </button>
        <button
            type="button"
            @click="setTheme('dark')"
            class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
            :class="[
                theme === 'dark'
                    ? 'active bg-[var(--soft)] text-[var(--teal)] font-bold shadow-xs'
                    : 'text-[var(--muted)] hover:text-[var(--text)]'
            ]"
            title="Dark Mode"
            aria-label="Dark Mode"
        >
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <span class="text-[11px] leading-none">Dark</span>
        </button>
    </div>
</script>

<script>
(function() {
    const ThemeToggleComponent = {
        template: '#v-theme-toggle-template',
        data() {
            return {
                theme: localStorage.getItem('ht-theme') || (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
            };
        },
        mounted() {
            this.applyTheme(this.theme);
        },
        methods: {
            setTheme(t) {
                this.theme = t;
                localStorage.setItem('ht-theme', t);
                this.applyTheme(t);
            },
            applyTheme(t) {
                if (t === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    document.documentElement.classList.remove('dark');
                }
            },
        },
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('theme-toggle', ThemeToggleComponent);
        window.registerVueComponent('v-theme-toggle', ThemeToggleComponent);
    } else if (window.app && window.app.component) {
        window.app.component('theme-toggle', ThemeToggleComponent);
        window.app.component('v-theme-toggle', ThemeToggleComponent);
    }
})();
</script>
@endpushOnce
