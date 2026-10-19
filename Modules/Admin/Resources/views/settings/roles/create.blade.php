@extends('admin::layouts.admin')

@section('title', 'Create Role')
@section('crumb', 'Create Role')

@section('content')
<div class="roles-page space-y-6">
    {!! view_event_render('settings.roles.create.before') !!}

    <v-create-user-role></v-create-user-role>
    <div class="hidden" aria-hidden="true"><roles-form></roles-form></div>

    {!! view_event_render('settings.roles.create.after') !!}
</div>

@pushOnce('scripts')
<script type="text/x-template" id="v-create-user-role-template">
    <form @submit.prevent="saveRole" class="space-y-6">
        <!-- Page Header & Action Buttons -->
        <div class="flex items-center justify-between gap-4 pb-4 border-b border-[var(--border)]">
            <div>
                <p class="eyebrow">Settings / Roles</p>
                <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">Create Role</h1>
                <p class="page-copy text-sm text-[var(--muted)] mt-1">Configure access permissions and privileges for this role.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ url('dashboard') }}" class="button button--outline text-xs">
                    Back
                </a>
                <button
                    type="submit"
                    :disabled="saving"
                    class="button button--primary text-xs inline-flex items-center gap-1.5"
                >
                    <svg v-if="!saving" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>@{{ saving ? 'Saving...' : 'Save Role' }}</span>
                </button>
            </div>
        </div>

        <!-- Success Toast -->
        <div v-if="saved" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg font-medium flex items-center justify-between">
            <span>Role created successfully.</span>
            <button type="button" @click="saved = false" class="text-emerald-600 hover:text-emerald-900">&times;</button>
        </div>

        <!-- Global Error Alert -->
        <div v-if="globalError" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg font-medium">
            @{{ globalError }}
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <!-- Left Main Column: Permissions & Access Control -->
            <div class="lg:col-span-2 space-y-6">
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                        Access Control
                    </h2>

                    <!-- Permission Type -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Permissions Access <span class="text-[var(--teal)]">*</span>
                        </label>
                        <select
                            v-model="access"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                        >
                            <option value="administration">All (Full Administration)</option>
                            <option value="custom">Custom Permissions</option>
                        </select>
                    </div>

                    <!-- All Access Banner -->
                    <div v-if="access === 'administration'" class="p-4 rounded-lg bg-[var(--page)] border border-[var(--border)] flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 grid place-items-center flex-shrink-0">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
                        </div>
                        <div>
                            <strong class="block text-xs font-semibold text-[var(--text)]">Full Administration Access</strong>
                            <p class="text-xs text-[var(--muted)] mt-0.5">
                                Users with this role receive complete access to all platform areas, catalog attributes, and administrative settings.
                            </p>
                        </div>
                    </div>

                    <!-- Custom Permission Tree -->
                    <div v-else class="space-y-4 pt-2">
                        <div class="flex items-center justify-between pb-2 border-b border-[var(--border)] text-xs">
                            <span class="font-semibold text-[var(--text)]">Permission Tree</span>
                            <span class="text-[var(--muted)]">@{{ selectedPermissions.length }} selected</span>
                        </div>

                        <div v-if="loadingNodes" class="py-6 text-center text-xs text-[var(--muted)]">
                            Loading permission definitions...
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="section in permissionNodes" :key="section.key" class="p-3.5 rounded-lg border border-[var(--border)] bg-[var(--page)]">
                                <label class="flex items-center gap-2 cursor-pointer font-semibold text-xs text-[var(--text)] select-none">
                                    <input
                                        type="checkbox"
                                        :checked="isSectionFullySelected(section)"
                                        @change="toggleSection(section, $event.target.checked)"
                                        class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-4 h-4"
                                    />
                                    <span>@{{ section.label }}</span>
                                </label>

                                <!-- Subgroups / Children -->
                                <div class="mt-2.5 pl-6 space-y-2 border-l border-[var(--border)]">
                                    <div v-for="child in section.children" :key="child.key">
                                        <div v-if="child.children && child.children.length > 0" class="space-y-1.5">
                                            <span class="text-[11px] font-semibold text-[var(--muted)] uppercase tracking-wider block mb-1">
                                                @{{ child.label }}
                                            </span>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <label v-for="leaf in child.children" :key="leaf.key" class="flex items-center gap-2 cursor-pointer text-xs text-[var(--text)] select-none">
                                                    <input
                                                        type="checkbox"
                                                        :value="leaf.key"
                                                        v-model="selectedPermissions"
                                                        class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-3.5 h-3.5"
                                                    />
                                                    <span>@{{ leaf.label }}</span>
                                                </label>
                                            </div>
                                        </div>

                                        <label v-else class="flex items-center gap-2 cursor-pointer text-xs text-[var(--text)] select-none">
                                            <input
                                                type="checkbox"
                                                :value="child.key"
                                                v-model="selectedPermissions"
                                                class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)] w-3.5 h-3.5"
                                            />
                                            <span>@{{ child.label }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar Column: Role Details -->
            <div class="space-y-6">
                <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                    <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                        Role Details
                    </h2>

                    <!-- Role Name -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Role Name <span class="text-[var(--teal)]">*</span>
                        </label>
                        <input
                            type="text"
                            v-model="name"
                            required
                            placeholder="e.g. Store Manager"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                            :class="errors.name ? 'border-red-500' : ''"
                        />
                        <p v-if="errors.name" class="mt-1 text-xs text-red-600">@{{ errors.name }}</p>
                    </div>

                    <!-- Role Description -->
                    <div>
                        <label class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Description
                        </label>
                        <textarea
                            v-model="description"
                            rows="3"
                            placeholder="Briefly describe what this role is for..."
                            class="w-full p-3 rounded-lg border border-[var(--border)] bg-[var(--card)] text-xs text-[var(--text)] focus:outline-none focus:border-[var(--teal)]"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</script>

<script type="module">
    const CreateRoleComponent = {
        template: '#v-create-user-role-template',
        data() {
            return {
                name: '',
                description: '',
                access: 'administration',
                selectedPermissions: [],
                permissionNodes: [],
                loadingNodes: false,
                saving: false,
                saved: false,
                errors: {},
                globalError: null,
            };
        },
        mounted() {
            this.fetchPermissions();
        },
        methods: {
            async fetchPermissions() {
                this.loadingNodes = true;
                try {
                    const res = await this.$axios.get('/api/permissions');
                    if (res?.data) {
                        this.permissionNodes = res.data;
                    }
                } catch (e) {
                    console.error(e);
                } finally {
                    this.loadingNodes = false;
                }
            },
            getLeafKeys(node) {
                if (!node.children || node.children.length === 0) {
                    return [node.key];
                }
                return node.children.flatMap(child => this.getLeafKeys(child));
            },
            isSectionFullySelected(section) {
                const leaves = this.getLeafKeys(section);
                if (leaves.length === 0) return false;
                return leaves.every(k => this.selectedPermissions.includes(k));
            },
            toggleSection(section, checked) {
                const leaves = this.getLeafKeys(section);
                if (checked) {
                    const toAdd = leaves.filter(k => !this.selectedPermissions.includes(k));
                    this.selectedPermissions.push(...toAdd);
                } else {
                    this.selectedPermissions = this.selectedPermissions.filter(k => !leaves.includes(k));
                }
            },
            async saveRole() {
                if (this.saving) return;

                if (!this.name.trim()) {
                    this.errors = { name: 'Role name is required.' };
                    return;
                }

                this.saving = true;
                this.saved = false;
                this.errors = {};
                this.globalError = null;

                try {
                    const payload = {
                        name: this.name.trim(),
                        access: this.access,
                        permissions: this.access === 'custom' ? this.selectedPermissions : [],
                    };

                    await this.$axios.post('/api/roles', payload);
                    this.saved = true;
                    this.name = '';
                    this.description = '';
                    this.selectedPermissions = [];
                } catch (caught) {
                    this.globalError = caught.message || 'Failed to save role.';
                    if (caught.errors) {
                        this.errors = Object.fromEntries(
                            Object.entries(caught.errors).map(([k, v]) => [k, Array.isArray(v) ? v[0] : v])
                        );
                    }
                } finally {
                    this.saving = false;
                }
            },
        },
    };

    app.component('v-create-user-role', CreateRoleComponent);
    app.component('roles-form', CreateRoleComponent);
</script>
@endPushOnce
@endsection
