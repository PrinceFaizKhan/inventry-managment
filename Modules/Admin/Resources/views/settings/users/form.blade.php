@pushOnce('scripts')
<script type="text/x-template" id="v-user-form-template">
    <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Page Header & Action Buttons -->
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Settings / Users</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                    @{{ mode === 'create' ? 'Create User' : ('Edit User: ' + (form.name || '')) }}
                </h1>
                <p class="page-copy text-xs text-[var(--muted)] mt-1">
                    @{{ mode === 'create' ? 'Add a new administrator or staff member to your workspace.' : 'Update user profile details, role assignment, and access status.' }}
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
                    <span>@{{ saving ? 'Saving...' : (saved ? 'Saved!' : (mode === 'create' ? 'Create User' : 'Save Changes')) }}</span>
                </button>
            </div>
        </div>

        <!-- Success Toast Alert -->
        <div v-if="saved" class="p-3.5 bg-emerald-50 border border-emerald-200 dark:bg-emerald-950/40 dark:border-emerald-800/60 text-emerald-800 dark:text-emerald-300 text-xs rounded-xl font-medium flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-600 dark:text-emerald-400"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>@{{ mode === 'create' ? 'User created successfully! Redirecting...' : 'User details updated successfully!' }}</span>
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

        <!-- Two Column Responsive Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Main Content Area: Left 2 Columns -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Card 1: Account Information -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                    <div class="border-b border-[var(--border)] pb-3">
                        <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Account Information</span>
                        </h2>
                        <p class="text-[11px] text-[var(--muted)] mt-0.5">Primary identification and email contact for this user.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Full Name <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="text"
                                v-model="form.name"
                                required
                                placeholder="e.g. John Doe"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]/40 transition-all"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500/20': errors.name }"
                            />
                            <p v-if="errors.name" class="text-[11px] text-rose-500 font-medium">@{{ errors.name[0] }}</p>
                        </div>

                        <!-- Email Address -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Email Address <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="email"
                                v-model="form.email"
                                required
                                placeholder="e.g. john@example.com"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]/40 transition-all font-mono"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500/20': errors.email }"
                            />
                            <p v-if="errors.email" class="text-[11px] text-rose-500 font-medium">@{{ errors.email[0] }}</p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Security & Password -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                    <div class="border-b border-[var(--border)] pb-3">
                        <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span>Security & Authentication</span>
                        </h2>
                        <p class="text-[11px] text-[var(--muted)] mt-0.5">
                            @{{ mode === 'create' ? 'Set a strong password for account login (minimum 6 characters).' : 'Leave blank to retain current password.' }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Password <span v-if="mode === 'create'" class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="form.password"
                                :required="mode === 'create'"
                                :placeholder="mode === 'create' ? '••••••••' : 'Leave blank to keep unchanged'"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]/40 transition-all font-mono"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500/20': errors.password }"
                            />
                            <p v-if="errors.password" class="text-[11px] text-rose-500 font-medium">@{{ errors.password[0] }}</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-[var(--text)]">
                                Confirm Password <span v-if="mode === 'create' || form.password" class="text-rose-500">*</span>
                            </label>
                            <input
                                type="password"
                                v-model="form.password_confirmation"
                                :required="mode === 'create' || Boolean(form.password)"
                                placeholder="••••••••"
                                class="w-full h-10 px-3.5 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] placeholder-[var(--muted)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]/40 transition-all font-mono"
                                :class="{ 'border-rose-500 ring-1 ring-rose-500/20': errors.password_confirmation }"
                            />
                            <p v-if="errors.password_confirmation" class="text-[11px] text-rose-500 font-medium">@{{ errors.password_confirmation[0] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar Column: Right 1 Column -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Card 3: Role Assignment -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                    <div class="border-b border-[var(--border)] pb-2.5">
                        <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Role & Privileges</span>
                        </h2>
                        <p class="text-[11px] text-[var(--muted)] mt-0.5">Determines the user's operational permissions.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-[var(--text)]">
                            Assigned Role
                        </label>
                        <select
                            v-model="form.role_id"
                            class="w-full h-10 px-3 rounded-xl border border-[var(--border)] bg-[var(--page)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]/40 transition-all cursor-pointer"
                        >
                            <option value="">Unassigned (No Role)</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                @{{ role.name }}
                            </option>
                        </select>
                        <p v-if="errors.role_id" class="text-[11px] text-rose-500 font-medium">@{{ errors.role_id[0] }}</p>
                    </div>
                </div>

                <!-- Card 4: Account Status -->
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xs space-y-4">
                    <div class="border-b border-[var(--border)] pb-2.5">
                        <h2 class="text-sm font-bold text-[var(--text)] flex items-center gap-2">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--teal)]"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            <span>Account Status</span>
                        </h2>
                        <p class="text-[11px] text-[var(--muted)] mt-0.5">Active users can authenticate and access the admin studio.</p>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <!-- Active Option -->
                        <button
                            type="button"
                            @click="form.status = 'active'"
                            class="px-3 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer select-none"
                            :class="form.status === 'active'
                                ? 'bg-emerald-50 text-emerald-700 border-emerald-300 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-700 ring-2 ring-emerald-500/20'
                                : 'bg-[var(--page)] text-[var(--muted)] border-[var(--border)] hover:border-[var(--teal)] hover:text-[var(--text)]'"
                        >
                            <span class="w-2 h-2 rounded-full" :class="form.status === 'active' ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                            <span>Active</span>
                        </button>

                        <!-- Inactive Option -->
                        <button
                            type="button"
                            @click="form.status = 'inactive'"
                            class="px-3 py-2.5 rounded-xl border text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer select-none"
                            :class="form.status === 'inactive'
                                ? 'bg-rose-50 text-rose-700 border-rose-300 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-700 ring-2 ring-rose-500/20'
                                : 'bg-[var(--page)] text-[var(--muted)] border-[var(--border)] hover:border-[var(--teal)] hover:text-[var(--text)]'"
                        >
                            <span class="w-2 h-2 rounded-full" :class="form.status === 'inactive' ? 'bg-rose-500' : 'bg-zinc-400'"></span>
                            <span>Inactive</span>
                        </button>
                    </div>
                    <p v-if="errors.status" class="text-[11px] text-rose-500 font-medium">@{{ errors.status[0] }}</p>
                </div>
            </div>

        </div>
    </form>
</script>

<script>
(function() {
    const UserFormComponent = {
        template: '#v-user-form-template',
        props: {
            mode: {
                type: String,
                default: 'create' // 'create' or 'edit'
            },
            initialUser: {
                type: Object,
                default: () => ({})
            },
            roles: {
                type: Array,
                default: () => []
            },
            backUrl: {
                type: String,
                default: '/settings/users'
            }
        },
        data() {
            return {
                form: {
                    name: this.initialUser?.name || '',
                    email: this.initialUser?.email || '',
                    role_id: this.initialUser?.role_id ?? '',
                    status: this.initialUser?.status || 'active',
                    password: '',
                    password_confirmation: ''
                },
                saving: false,
                saved: false,
                errors: {},
                errorMessage: null
            };
        },
        methods: {
            async submitForm() {
                this.errors = {};
                this.errorMessage = null;

                if (this.form.password && this.form.password !== this.form.password_confirmation) {
                    this.errors.password_confirmation = ['The password confirmation does not match.'];
                    return;
                }

                this.saving = true;

                const payload = {
                    name: this.form.name,
                    email: this.form.email,
                    role_id: this.form.role_id ? Number(this.form.role_id) : null,
                    status: this.form.status
                };

                if (this.mode === 'create' || this.form.password) {
                    payload.password = this.form.password;
                    payload.password_confirmation = this.form.password_confirmation;
                }

                const url = this.mode === 'create'
                    ? '/api/settings/users'
                    : `/api/settings/users/${this.initialUser.id}`;
                const method = this.mode === 'create' ? 'POST' : 'PUT';

                try {
                    if (window.axios) {
                        if (method === 'POST') {
                            await window.axios.post(url, payload);
                        } else {
                            await window.axios.put(url, payload);
                        }
                    } else if (window.request) {
                        await window.request(url, {
                            method,
                            body: JSON.stringify(payload)
                        });
                    }

                    this.saved = true;
                    setTimeout(() => {
                        window.location.href = this.backUrl;
                    }, 1000);
                } catch (e) {
                    if (e.errors) {
                        this.errors = e.errors;
                    }
                    this.errorMessage = e.message || 'Failed to save user. Please verify input fields.';
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    if (typeof window.registerVueComponent === 'function') {
        window.registerVueComponent('v-user-form', UserFormComponent);
    } else if (window.app && typeof window.app.component === 'function') {
        window.app.component('v-user-form', UserFormComponent);
    }
})();
</script>
@endpushOnce
