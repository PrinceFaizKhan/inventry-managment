<v-language-toggle></v-language-toggle>

@pushOnce('scripts')
<script type="text/x-template" id="v-language-toggle-template">
    <div class="theme language-toggle" :title="locale === 'en-IN' ? 'Switch to Urdu (اردو)' : 'انگریزی میں تبدیل کریں'">
        <button
            v-for="item in supportedLocales"
            :key="item.code"
            :class="{ active: locale === item.code }"
            type="button"
            @click="setLocale(item.code)"
        >
            <svg v-if="item.code === 'en-IN'" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 8 6 6"/><path d="m4 14 6-6 2-3"/><path d="M2 5h12"/><path d="M7 2h1"/><path d="m22 22-5-10-5 10"/><path d="M14 18h6"/></svg>
            <span>@{{ item.code === 'en-IN' ? 'EN' : 'اردو' }}</span>
        </button>
    </div>
</script>

<script type="module">
    const languageToggleComponent = {
        template: '#v-language-toggle-template',
        setup() {
            const i18n = typeof window.useI18n === 'function' ? window.useI18n() : { locale: 'en-IN', setLocale: () => {}, supportedLocales: [] };
            return {
                locale: i18n.locale,
                setLocale: i18n.setLocale,
                supportedLocales: i18n.supportedLocales
            };
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-language-toggle', languageToggleComponent);
        window.registerVueComponent('language-toggle', languageToggleComponent);
    } else if (window.app && window.app.component) {
        window.app.component('v-language-toggle', languageToggleComponent);
        window.app.component('language-toggle', languageToggleComponent);
    }
</script>
@endpushOnce
