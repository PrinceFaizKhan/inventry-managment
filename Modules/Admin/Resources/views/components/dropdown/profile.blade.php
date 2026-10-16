@props([
    'user' => [],
    'accountUrl' => '/account',
    'usersUrl' => '/settings/users',
    'attributesUrl' => '/catalog/attributes',
    'logoutUrl' => '/logout',
    'csrfToken' => '',
])

<v-profile-dropdown
    :user='@json($user)'
    account-url="{{ $accountUrl }}"
    users-url="{{ $usersUrl }}"
    attributes-url="{{ $attributesUrl }}"
    logout-url="{{ $logoutUrl }}"
    csrf-token="{{ $csrfToken }}"
></v-profile-dropdown>

@pushOnce('scripts')
<script type="text/x-template" id="v-profile-dropdown-template">
    <div class="relative" ref="dropdownRef">
        <!-- Trigger Button -->
        <button
            type="button"
            class="flex items-center gap-2.5 h-9 px-2 rounded-lg hover:bg-[var(--soft)] text-[var(--text)] transition-colors focus:outline-none cursor-pointer"
            :class="{ 'bg-[var(--soft)] text-[var(--teal)]': isOpen }"
            :aria-expanded="isOpen"
            aria-haspopup="true"
            @click="toggle"
        >
            <span class="w-7 h-7 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-xs grid place-items-center">
                @{{ (user.name || 'A').charAt(0).toUpperCase() }}
            </span>

            <span class="text-xs font-semibold max-w-[120px] truncate hidden sm:inline-block">
                @{{ user.name }}
            </span>

            <svg
                width="14"
                height="14"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="text-[var(--muted)] transition-transform duration-200"
                :class="{ 'rotate-180 text-[var(--teal)]': isOpen }"
            >
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </button>

        <!-- Floating Dropdown Popup -->
        <div
            v-if="isOpen"
            class="absolute right-0 top-11 w-64 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-xl p-2 z-50 text-[var(--text)] rtl:right-auto rtl:left-0"
            role="menu"
        >
            <!-- User Info Header -->
            <div class="p-2.5 rounded-lg bg-[var(--page)] border border-[var(--border)] mb-1.5">
                <div class="flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-sm grid place-items-center flex-shrink-0">
                        @{{ (user.name || 'A').charAt(0).toUpperCase() }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-xs text-[var(--text)] truncate">
                            @{{ user.name }}
                        </div>
                        <div class="text-[11px] text-[var(--muted)] truncate">
                            @{{ user.email }}
                        </div>
                    </div>
                </div>
                <div class="mt-2 flex items-center justify-between text-[10px]">
                    <span class="text-[var(--muted)]">Role:</span>
                    <span class="px-2 py-0.5 rounded-full bg-[var(--soft)] text-[var(--teal)] font-semibold">
                        @{{ user.role || 'Administrator' }}
                    </span>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="space-y-0.5 py-1">
                <a
                    :href="accountUrl"
                    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
                    role="menuitem"
                    @click="close"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--muted)]"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span>My Profile</span>
                </a>

                <a
                    :href="usersUrl"
                    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
                    role="menuitem"
                    @click="close"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--muted)]"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>Manage Users</span>
                </a>

                <a
                    :href="attributesUrl"
                    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium text-[var(--text)] hover:bg-[var(--soft)] hover:text-[var(--teal)] transition-colors"
                    role="menuitem"
                    @click="close"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-[var(--muted)]"><line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/></svg>
                    <span>Catalog Attributes</span>
                </a>
            </div>

            <div class="border-t border-[var(--border)] my-1"></div>

            <!-- Logout Action Form -->
            <form method="POST" :action="logoutUrl">
                <input type="hidden" name="_token" :value="csrfToken" />
                <button
                    type="submit"
                    class="w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors ltr:text-left rtl:text-right cursor-pointer"
                    role="menuitem"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-500"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
    </div>
</script>

<script type="module">
    const profileDropdownComponent = {
        template: '#v-profile-dropdown-template',
        props: {
            user: {
                type: Object,
                default: () => ({ name: 'Admin', email: '', role: 'Administrator' })
            },
            accountUrl: {
                type: String,
                default: '/account'
            },
            usersUrl: {
                type: String,
                default: '/settings/users'
            },
            attributesUrl: {
                type: String,
                default: '/catalog/attributes'
            },
            logoutUrl: {
                type: String,
                default: '/logout'
            },
            csrfToken: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                isOpen: false
            };
        },
        mounted() {
            document.addEventListener('pointerdown', this.handleOutsideClick);
            document.addEventListener('keydown', this.handleKeydown);
        },
        beforeUnmount() {
            document.removeEventListener('pointerdown', this.handleOutsideClick);
            document.removeEventListener('keydown', this.handleKeydown);
        },
        methods: {
            toggle() {
                this.isOpen = !this.isOpen;
            },
            close() {
                this.isOpen = false;
            },
            handleOutsideClick(event) {
                if (this.$refs.dropdownRef && !this.$refs.dropdownRef.contains(event.target)) {
                    this.close();
                }
            },
            handleKeydown(event) {
                if (event.key === 'Escape') {
                    this.close();
                }
            }
        }
    };

    app.component('v-profile-dropdown', profileDropdownComponent);
    app.component('profile-dropdown', profileDropdownComponent);
</script>
@endpushOnce
