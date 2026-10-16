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
    import { useI18n } from '/resources/js/lib/i18n.js';

    const languageToggleComponent = {
        template: '#v-language-toggle-template',
        setup() {
            const { locale, setLocale, supportedLocales } = useI18n();
            return { locale, setLocale, supportedLocales };
        }
    };

    app.component('v-language-toggle', languageToggleComponent);
    app.component('language-toggle', languageToggleComponent);
</script>
@endpushOnce
