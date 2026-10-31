@props([
    'families' => [],
    'initialOpen' => false,
])

<v-product-create-modal
    :families='@json($families)'
    :initial-open="{{ $initialOpen ? 'true' : 'false' }}"
    ref="createProductModal"
></v-product-create-modal>

@pushOnce('scripts')
<script type="text/x-template" id="v-product-create-modal-template">
    <teleport to="body">
        <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-sm overflow-y-auto"
                @click.self="close"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-product-create-title"
            >
                <div
                    class="relative w-full max-w-lg max-h-[calc(100vh-2rem)] flex flex-col rounded-2xl border border-[var(--border)] bg-[var(--card)] shadow-2xl transition-all duration-200 transform scale-100 animate-in fade-in zoom-in-95 my-auto overflow-hidden"
                    @click.stop
                >
                    <!-- Modal Header -->
                    <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-[var(--border)] flex items-center justify-between bg-[var(--page)]/50 rounded-t-2xl shrink-0">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 flex items-center justify-center shrink-0 shadow-xs">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h2 id="modal-product-create-title" class="text-sm font-bold text-[var(--text)] truncate">Create Product</h2>
                                <p class="text-[11px] text-[var(--muted)] truncate">Configure family, type & SKU</p>
                            </div>
                        </div>

                        <button
                            type="button"
                            @click="close"
                            class="p-1.5 rounded-lg text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/40 transition-colors cursor-pointer text-base leading-none shrink-0"
                            title="Close (Esc)"
                            aria-label="Close"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form @submit.prevent="createProduct" class="p-4 sm:p-6 space-y-4 overflow-y-auto">
                        <!-- Global Error Alert -->
                        <div v-if="globalError" class="p-3 bg-red-500/10 border border-red-500/30 text-red-500 text-xs rounded-xl font-medium flex items-center gap-2">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span>@{{ globalError }}</span>
                        </div>

                        <!-- 1. Family Select Box -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-[var(--text)]">
                                    Product Family <span class="text-[var(--teal)]">*</span>
                                </label>
                                <span v-if="selectedFamily" class="text-[10px] font-mono text-[var(--muted)]">
                                    @{{ selectedFamily.code }}
                                </span>
                            </div>
                            <select
                                v-model="productTypeId"
                                @change="handleFamilyChange"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all cursor-pointer"
                                :class="errors.product_type_id ? 'border-red-500' : ''"
                            >
                                <option :value="null" disabled>Select a product family...</option>
                                <option v-for="family in familiesList" :key="family.id" :value="family.id">
                                    @{{ family.name }} (@{{ family.code }})
                                </option>
                            </select>
                            <p v-if="errors.product_type_id" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.product_type_id }}</p>
                        </div>

                        <!-- 2. Product Type Select Box (Shown when family is selected) -->
                        <div v-if="productTypeId" class="animate-in fade-in slide-in-from-top-1 duration-150">
                            <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                                Product Type <span class="text-[var(--teal)]">*</span>
                            </label>
                            <select
                                v-model="productType"
                                @change="handleTypeChange"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all cursor-pointer"
                                :class="errors.type ? 'border-red-500' : ''"
                            >
                                <option :value="null" disabled>Select product type...</option>
                                <option value="simple">Simple</option>
                                <option v-if="hasVariantLevels" value="configurable">Configurable</option>
                            </select>
                            <p v-if="errors.type" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.type }}</p>
                            <p v-if="!hasVariantLevels" class="mt-1 text-[11px] text-[var(--muted)] flex items-center gap-1">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-amber-500 flex-shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <span>This family has no variant levels defined, so only Simple product type is available.</span>
                            </p>
                        </div>

                        <!-- 3. Variant Level Select Box (Shown ONLY when Configurable product type is selected) -->
                        <div v-if="productTypeId && productType === 'configurable'" class="animate-in fade-in slide-in-from-top-1 duration-150">
                            <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                                Variant Type / Level <span class="text-[var(--teal)]">*</span>
                            </label>
                            <select
                                v-model="productTypeVariantId"
                                @change="handleVariantChange"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all cursor-pointer"
                                :class="errors.product_type_variant_id ? 'border-red-500' : ''"
                            >
                                <option :value="null" disabled>Select variant level profile...</option>
                                <option v-for="variant in availableVariants" :key="variant.id" :value="variant.id">
                                    @{{ variant.name }} (@{{ variant.level_count }}-Level: @{{ variant.code }})
                                </option>
                            </select>
                            <p v-if="errors.product_type_variant_id" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.product_type_variant_id }}</p>
                            <p v-if="selectedVariantProfile" class="mt-1 text-[11px] text-[var(--muted)]">
                                Structure: @{{ selectedVariantProfile.level_count === 2 ? '2-Level (Parent → Variant Group → Child SKU)' : '1-Level (Direct Parent → Child SKUs)' }}
                            </p>
                        </div>

                        <!-- 4. SKU Input Field -->
                        <!-- Visible if: (type === 'simple') OR (type === 'configurable' AND productTypeVariantId is selected) -->
                        <div v-if="shouldShowSkuInput" class="animate-in fade-in slide-in-from-top-1 duration-150">
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-semibold text-[var(--text)]">
                                    SKU (Stock Keeping Unit) <span class="text-[var(--teal)]">*</span>
                                </label>
                                <span class="text-[10px] text-[var(--muted)]">Uppercase alphanumeric</span>
                            </div>
                            <input
                                ref="skuInput"
                                type="text"
                                v-model="sku"
                                @input="handleSkuInput"
                                placeholder="e.g. IPHONE-15-PRO-MAX"
                                required
                                maxlength="120"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs font-mono uppercase tracking-wide text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)] transition-all"
                                :class="errors.sku ? 'border-red-500' : ''"
                            />
                            <p v-if="errors.sku" class="mt-1 text-[11px] text-red-500 font-medium">@{{ errors.sku }}</p>
                        </div>

                        <!-- Modal Actions Footer -->
                        <div class="pt-3 border-t border-[var(--border)] flex items-center justify-end gap-2.5 shrink-0">
                            <button
                                type="button"
                                @click="close"
                                class="button button--outline text-xs px-4 flex-1 sm:flex-initial text-center justify-center"
                                :disabled="saving"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="!isFormValid || saving"
                                class="button button--primary text-xs px-4 inline-flex items-center justify-center gap-1.5 shadow-sm disabled:opacity-50 disabled:cursor-not-allowed flex-1 sm:flex-initial"
                            >
                                <svg v-if="!saving" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                <svg v-else class="animate-spin" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10"/></svg>
                                <span>@{{ saving ? 'Creating & Redirecting...' : 'Create & Continue' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </transition>
    </teleport>
</script>

<script>
(function() {
    const ProductCreateModalComponent = {
        template: '#v-product-create-modal-template',
        props: {
            families: {
                type: Array,
                default: () => []
            },
            initialOpen: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                isOpen: Boolean(this.initialOpen),
                productTypeId: null,
                productType: null, // 'simple' | 'configurable'
                productTypeVariantId: null,
                sku: '',
                saving: false,
                errors: {},
                globalError: null,
                familiesList: Array.isArray(this.families) ? this.families : []
            };
        },
        computed: {
            selectedFamily() {
                if (!this.productTypeId) return null;
                return this.familiesList.find(f => f.id === this.productTypeId) || null;
            },
            hasVariantLevels() {
                if (!this.selectedFamily) return false;
                const variants = this.selectedFamily.variants || [];
                return variants.length > 0;
            },
            availableVariants() {
                if (!this.selectedFamily) return [];
                return this.selectedFamily.variants || [];
            },
            selectedVariantProfile() {
                if (!this.productTypeVariantId) return null;
                return this.availableVariants.find(v => v.id === this.productTypeVariantId) || null;
            },
            shouldShowSkuInput() {
                if (!this.productTypeId || !this.productType) return false;
                if (this.productType === 'simple') return true;
                if (this.productType === 'configurable') {
                    return Boolean(this.productTypeVariantId);
                }
                return false;
            },
            isFormValid() {
                if (!this.productTypeId || !this.productType) return false;
                const cleanSku = (this.sku || '').trim();
                if (!cleanSku) return false;

                if (this.productType === 'configurable' && !this.productTypeVariantId) {
                    return false;
                }

                return true;
            }
        },
        mounted() {
            this._handleOpen = () => this.open();
            this._handleKeydown = (e) => this.handleKeydown(e);
            window.addEventListener('ht-open-create-product', this._handleOpen);
            window.addEventListener('keydown', this._handleKeydown);

            if (this.isOpen) {
                this.resetForm();
            }
        },
        beforeUnmount() {
            if (this._handleOpen) window.removeEventListener('ht-open-create-product', this._handleOpen);
            if (this._handleKeydown) window.removeEventListener('keydown', this._handleKeydown);
        },
        methods: {
            open() {
                this.isOpen = true;
                this.resetForm();
            },
            close() {
                this.isOpen = false;
                this.resetForm();
            },
            resetForm() {
                this.productTypeId = null;
                this.productType = null;
                this.productTypeVariantId = null;
                this.sku = '';
                this.saving = false;
                this.errors = {};
                this.globalError = null;
            },
            handleKeydown(e) {
                if (e.key === 'Escape' && this.isOpen) {
                    this.close();
                }
            },
            handleFamilyChange() {
                this.errors = {};
                this.globalError = null;
                this.productTypeVariantId = null;
                this.sku = '';

                // If family has no variant levels, only 'simple' is available and can be auto-selected
                if (!this.hasVariantLevels) {
                    this.productType = 'simple';
                    this.$nextTick(() => {
                        this.$refs.skuInput?.focus();
                    });
                } else {
                    // Let user choose between Simple and Configurable
                    this.productType = null;
                }
            },
            handleTypeChange() {
                this.errors = {};
                this.globalError = null;
                this.productTypeVariantId = null;
                this.sku = '';

                if (this.productType === 'simple') {
                    this.$nextTick(() => {
                        this.$refs.skuInput?.focus();
                    });
                }
            },
            handleVariantChange() {
                this.errors = {};
                this.globalError = null;
                this.sku = '';

                this.$nextTick(() => {
                    this.$refs.skuInput?.focus();
                });
            },
            handleSkuInput() {
                this.errors = {};
                this.globalError = null;
                // Auto format SKU: uppercase, replace spaces with dashes, disallow special characters
                if (this.sku) {
                    this.sku = this.sku.toUpperCase().replace(/\s+/g, '-').replace(/[^A-Z0-9_-]/g, '');
                }
            },
            async createProduct() {
                if (!this.isFormValid || this.saving) return;

                const cleanSku = (this.sku || '').trim();
                if (!cleanSku) {
                    this.errors = { sku: 'SKU is required.' };
                    return;
                }

                this.saving = true;
                this.errors = {};
                this.globalError = null;

                try {
                    const payload = {
                        sku: cleanSku,
                        product_type_id: this.productTypeId,
                        type: this.productType,
                        product_type_variant_id: this.productType === 'configurable' ? this.productTypeVariantId : null
                    };

                    const axiosClient = window.axios || this.$axios;
                    const res = await axiosClient.post('/api/catalog/products', payload);

                    const redirectUrl = res?.data?.redirect_url || (res?.data?.data?.id ? `/catalog/products/${res.data.data.id}/edit` : '/catalog/products');

                    // Go to product edit page directly
                    window.location.href = redirectUrl;
                } catch (caught) {
                    const resp = caught?.response?.data || caught;
                    this.globalError = resp?.message || 'Failed to create product.';

                    if (resp?.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(resp.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-product-create-modal', ProductCreateModalComponent);
        window.registerVueComponent('product-create-modal', ProductCreateModalComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-product-create-modal', ProductCreateModalComponent);
        window.app.component('product-create-modal', ProductCreateModalComponent);
    }
})();
</script>
@endpushOnce
