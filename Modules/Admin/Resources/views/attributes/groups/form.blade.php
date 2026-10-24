@pushOnce('scripts')
<script type="text/x-template" id="v-attribute-group-form-template">
    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Page Header & Action Buttons -->
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Catalog / Attribute Groups</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                    @{{ mode === 'create' ? 'Create Attribute Group' : ('Edit Group: ' + (form.name || '')) }}
                </h1>
                <p class="page-copy text-xs text-[var(--muted)] mt-1">
                    @{{ mode === 'create' ? 'Add a new attribute group like General, Description, or Money.' : 'Update attribute group name and code identifier.' }}
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <a :href="backUrl" class="button button--outline text-xs">
                    Cancel
                </a>
                <button
                    type="submit"
                    :disabled="saving"
                    class="button button--primary text-xs inline-flex items-center gap-1.5 cursor-pointer shadow-sm disabled:opacity-50"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <svg v-else class="animate-spin -ml-0.5 mr-1 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>@{{ saving ? 'Saving...' : (saved ? 'Saved!' : (mode === 'create' ? 'Create Group' : 'Save Changes')) }}</span>
                </button>
            </div>
        </div>

        <!-- Success Toast Alert -->
        <div v-if="saved" class="p-3.5 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs rounded-xl font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600 dark:text-emerald-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>@{{ mode === 'create' ? 'Attribute group created successfully! Redirecting...' : 'Attribute group updated successfully! Redirecting...' }}</span>
            </div>
            <button type="button" @click="saved = false" class="text-emerald-600 dark:text-emerald-400 hover:opacity-80 text-base leading-none cursor-pointer">&times;</button>
        </div>

        <!-- Global Error Alert -->
        <div v-if="errorMessage" class="p-3.5 bg-rose-50 border border-rose-200 dark:bg-rose-950/40 dark:border-rose-800/60 text-rose-800 dark:text-rose-300 text-xs rounded-xl font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600 dark:text-rose-400"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>@{{ errorMessage }}</span>
            </div>
            <button type="button" @click="errorMessage = null" class="text-rose-600 dark:text-rose-400 hover:opacity-80 text-base leading-none cursor-pointer">&times;</button>
        </div>

        <!-- Form Card -->
        <div class="max-w-2xl p-6 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-6">
            <div class="border-b border-[var(--border)] pb-3">
                <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[var(--teal)]">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    Group Details
                </h2>
                <p class="text-xs text-[var(--muted)] mt-0.5">Define group name and unique code identifier.</p>
            </div>

            <!-- Name Input -->
            <div class="space-y-1.5">
                <label for="group-name" class="block text-xs font-semibold text-[var(--text)]">
                    Name <span class="text-rose-500">*</span>
                </label>
                <input
                    id="group-name"
                    type="text"
                    v-model="form.name"
                    @input="onNameInput"
                    placeholder="e.g. General, Description, Money"
                    required
                    maxlength="120"
                    class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                    :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': errors.name }"
                />
                <p v-if="errors.name" class="text-[11px] text-rose-500 font-medium">@{{ errors.name }}</p>
                <p class="text-[11px] text-[var(--muted)]">Display label for the attribute group in forms and tabs.</p>
            </div>

            <!-- Code Input -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="group-code" class="block text-xs font-semibold text-[var(--text)]">
                        Code <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[10px] font-mono text-[var(--muted)]">Unique identifier</span>
                </div>
                <input
                    id="group-code"
                    type="text"
                    v-model="form.code"
                    @input="codeTouched = true"
                    placeholder="e.g. general, description, money"
                    required
                    maxlength="120"
                    class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] font-mono text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                    :class="{ 'border-rose-500 focus:border-rose-500 focus:ring-rose-500': errors.code }"
                />
                <p v-if="errors.code" class="text-[11px] text-rose-500 font-medium">@{{ errors.code }}</p>
                <p class="text-[11px] text-[var(--muted)]">Unique lowercase slug identifier (letters, numbers, underscores, dashes only).</p>
            </div>
        </div>
    </form>
</script>

<script>
(function() {
    const AttributeGroupFormComponent = {
        name: 'v-attribute-group-form',
        template: '#v-attribute-group-form-template',
        props: {
            mode: {
                type: String,
                default: 'create' // 'create' | 'edit'
            },
            initialGroup: {
                type: Object,
                default: () => null
            },
            backUrl: {
                type: String,
                default: '/catalog/attribute-groups'
            }
        },
        data() {
            return {
                form: {
                    name: this.initialGroup?.name || '',
                    code: this.initialGroup?.code || ''
                },
                codeTouched: Boolean(this.initialGroup?.code),
                errors: {},
                errorMessage: null,
                saving: false,
                saved: false
            };
        },
        methods: {
            slugify(text) {
                return (text || '')
                    .toString()
                    .toLowerCase()
                    .trim()
                    .replace(/[\s\W-]+/g, '_')
                    .replace(/^_+|_+$/g, '');
            },
            onNameInput() {
                this.errors = {};
                this.errorMessage = null;
                if (this.mode === 'create' && !this.codeTouched) {
                    this.form.code = this.slugify(this.form.name);
                }
            },
            async submitForm() {
                if (this.saving) return;

                const trimmedName = (this.form.name || '').trim();
                const trimmedCode = (this.form.code || '').trim();

                this.errors = {};
                this.errorMessage = null;

                if (!trimmedName) {
                    this.errors = { name: 'Attribute group name is required.' };
                    return;
                }
                if (!trimmedCode) {
                    this.errors = { code: 'Attribute group code is required.' };
                    return;
                }

                this.saving = true;

                try {
                    const axiosClient = this.$axios || window.axios;
                    const payload = {
                        name: trimmedName,
                        code: trimmedCode
                    };

                    if (this.mode === 'create') {
                        await axiosClient.post('/api/catalog/attribute-groups', payload);
                    } else {
                        const groupId = this.initialGroup?.id;
                        await axiosClient.put(`/api/catalog/attribute-groups/${groupId}`, payload);
                    }

                    this.saved = true;

                    setTimeout(() => {
                        window.location.href = this.backUrl;
                    }, 600);
                } catch (caught) {
                    const res = caught?.response?.data || caught;
                    this.errorMessage = res?.message || 'Failed to save attribute group. Please check inputs.';
                    if (res?.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(res.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-attribute-group-form', AttributeGroupFormComponent);
        window.registerVueComponent('attribute-group-form', AttributeGroupFormComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-attribute-group-form', AttributeGroupFormComponent);
        window.app.component('attribute-group-form', AttributeGroupFormComponent);
    }
})();
</script>
@endpushOnce
