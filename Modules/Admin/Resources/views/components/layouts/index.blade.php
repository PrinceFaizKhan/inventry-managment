@props([
    'title' => null,
])

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'ur-IN']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Haseen Telecom · Admin' }}</title>
    @stack('meta')
    <script>
        if (localStorage.getItem('ht-theme') === 'dark' || (!localStorage.getItem('ht-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    {!! view_event_render('layout.body.before') !!}
    <div id="app" class="admin">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="admin-sidebar">
            <div class="side-brand">
                <div class="brand">
                    <span class="brand-icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16.5 9.4 7.55 4.24a1.78 1.78 0 0 0-2.5 1.55v12.42a1.78 1.78 0 0 0 2.5 1.55L16.5 14.6a1.78 1.78 0 0 0 0-3.2z"/></svg>
                    </span>
                    <div>
                        <b>Haseen <em>Telecom</em></b>
                        <small>{{ __('app.brand.workspace') ?? 'Admin workspace' }}</small>
                    </div>
                </div>
                <button type="button" class="close" id="sidebar-close" onclick="window.closeSidebar()" aria-label="Close sidebar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            @php
                $user = auth()->user() ?? new \App\Models\User;
                $menuItems = app(\Modules\Core\Support\Menu\MenuRegistry::class)->for($user);
            @endphp

            <!-- Vue Sidebar Navigation Component -->
            <sidebar-nav
                :items='@json($menuItems)'
                current-path="{{ request()->path() }}"
            ></sidebar-nav>

            <footer>
                {!! view_event_render('sidebar.bottom') !!}
                <div class="footer-meta text-xs text-muted">
                    <span>Haseen Telecom v2.0</span>
                </div>
            </footer>
        </aside>

        <!-- Backdrop overlay for mobile drawer -->
        <div class="backdrop" id="sidebar-backdrop" onclick="window.closeSidebar()"></div>

        <!-- Main Body Area -->
        <div class="app-body">
            <header class="header">
                <div class="flex items-center gap-3">
                    <button type="button" class="mobile-menu" id="sidebar-toggle" onclick="window.toggleSidebar()" aria-label="Toggle navigation">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>

                    <div class="crumb">
                        {!! view_event_render('header.left') !!}
                        <b>{{ $title ?? __('brand.portal') ?? 'Admin Portal' }}</b>
                    </div>
                </div>

                <div class="header-right">
                    {!! view_event_render('header.right') !!}

                    <!-- Theme Toggle Component -->
                    <theme-toggle></theme-toggle>

                    <!-- Vue Language Switcher Component -->
                    <language-toggle></language-toggle>

                    @auth
                        @php
                            $adminUser = auth()->user();
                            $profileData = [
                                'name' => $adminUser?->name ?? 'Admin',
                                'email' => $adminUser?->email ?? '',
                                'role' => $adminUser?->role?->name ?? 'Administrator',
                            ];
                        @endphp
                        <profile-dropdown
                            :user='@json($profileData)'
                            account-url="{{ route('admin.account.edit') }}"
                            users-url="{{ route('settings.users') }}"
                            attributes-url="{{ url('catalog/attributes') }}"
                            logout-url="{{ route('admin.session.destroy') }}"
                            csrf-token="{{ csrf_token() }}"
                        ></profile-dropdown>
                    @endauth
                </div>
            </header>

            <main class="workspace">
                {!! view_event_render('content.before') !!}
                {{ $slot ?? '' }}
                @yield('content')
                {!! view_event_render('content.after') !!}
            </main>
        </div>
    </div>
    {!! view_event_render('layout.body.after') !!}
    @stack('scripts')
</body>
</html>
