<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'ur-IN']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haseen Telecom · Studio')</title>
    {!! view_event_render('layout.head') !!}
    <script>
        // Synchronously restore theme and sidebar state to prevent layout shift
        if (localStorage.getItem('ht-theme') === 'dark' || (!localStorage.getItem('ht-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
            document.documentElement.classList.remove('dark');
        }

        if (localStorage.getItem('ht-sidebar-expanded') !== 'false' && window.innerWidth > 768) {
            document.documentElement.classList.add('sidebar-expanded');
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('sidebar-expanded');
            });
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased selection:bg-[var(--teal)] selection:text-white">
    {!! view_event_render('layout.body.before') !!}

    <div id="app" class="admin min-h-screen flex bg-[var(--page)] text-[var(--text)] transition-colors duration-200">
        
        <!-- 1. OBSIDIAN NAVIGATION RAIL / SIDEBAR (Collapsible & Expandable) -->
        <aside class="sidebar fixed top-0 bottom-0 left-0 z-40 flex flex-col justify-between py-3 bg-[var(--card)] border-r border-[var(--border)] transition-all select-none" id="admin-sidebar">
            
            <div class="flex flex-col w-full">
                <!-- Top Brand Pod & Collapse Toggle -->
                <div class="side-brand-wrap flex items-center justify-between w-full px-3 py-1.5 mb-2 border-b border-[var(--border)]/70">
                    <a href="{{ url('dashboard') }}" class="flex items-center gap-3 overflow-hidden group py-1" title="Haseen Telecom Studio">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[var(--teal)] to-cyan-400 p-[1.5px] shadow-sm hover:scale-105 transition-transform flex-shrink-0 flex items-center justify-center">
                            <div class="w-full h-full bg-[var(--card)] rounded-[10px] flex items-center justify-center group-hover:bg-transparent transition-colors">
                                <span class="font-black text-sm tracking-tighter text-[var(--teal)] group-hover:text-white">HT</span>
                            </div>
                        </div>
                        <div class="sidebar-brand-text min-w-0 text-left">
                            <b class="text-sm font-bold block leading-tight text-[var(--text)] truncate">Haseen <em class="text-[var(--teal)] not-italic">Telecom</em></b>
                            <small class="text-[9px] uppercase tracking-wider font-semibold text-[var(--muted)] block">Studio Workspace</small>
                        </div>
                    </a>

                    <!-- Dedicated Collapse Arrow Button in Sidebar -->
                    <button
                        type="button"
                        class="sidebar-collapse-btn p-1.5 rounded-lg text-[var(--muted)] hover:text-[var(--text)] hover:bg-[var(--border)]/40 transition-colors cursor-pointer"
                        onclick="window.toggleSidebar()"
                        title="Collapse Sidebar"
                        aria-label="Collapse Sidebar"
                    >
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="11 17 6 12 11 7"/>
                            <polyline points="18 17 13 12 18 7"/>
                        </svg>
                    </button>
                </div>

                @php
                    $user = auth()->user() ?? new \App\Models\User;
                    $menuItems = app(\Modules\Core\Support\Menu\MenuRegistry::class)->for($user);
                @endphp

                <!-- Dynamic Navigation Component -->
                <x-admin::layouts.sidebar
                    :items="$menuItems"
                    :current-path="request()->path()"
                />
            </div>

            <!-- Bottom: System Status & Version Tag -->
            <div class="sidebar-bottom-meta flex items-center justify-between px-4 py-2 border-t border-[var(--border)]/70 w-full">
                {!! view_event_render('sidebar.bottom') !!}
                
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[var(--teal)] animate-pulse" title="System Live"></span>
                    <span class="sidebar-version-text text-[10px] font-mono font-bold uppercase text-[var(--muted)]">v2.0 Live</span>
                </div>

                <button
                    type="button"
                    class="sidebar-expand-pill p-1 rounded-lg text-[var(--muted)] hover:text-[var(--teal)] hover:bg-[var(--soft)] transition-colors cursor-pointer"
                    onclick="window.toggleSidebar()"
                    title="Toggle sidebar"
                >
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M9 3v18"/></svg>
                </button>
            </div>
        </aside>

        <!-- Mobile Backdrop Overlay -->
        <div class="backdrop fixed inset-0 bg-black/60 backdrop-blur-sm z-35 hidden transition-opacity duration-200" id="sidebar-backdrop" onclick="window.closeSidebar()"></div>

        <!-- 2. MAIN APP BODY -->
        <div class="app-body flex-1 flex flex-col min-w-0 min-h-screen">
            
            <!-- FLOATING DYNAMIC CAPSULE HEADER BAR -->
            <header class="sticky top-3 z-30 px-4 sm:px-6 lg:px-8 max-w-[1720px] w-full mx-auto">
                <div class="rounded-2xl border border-[var(--border)] bg-[var(--card)]/85 backdrop-blur-xl px-4 sm:px-5 py-2.5 shadow-sm flex items-center justify-between gap-4 transition-colors">
                    
                    <!-- Left: Interactive Menu Toggle Button + Breadcrumb Capsule -->
                    <div class="flex items-center gap-3">
                        
                        <!-- UNIVERSAL MENU TOGGLE BUTTON (Works on Desktop & Mobile) -->
                        <button
                            type="button"
                            class="sidebar-toggle-btn p-2 rounded-xl border border-[var(--border)] bg-[var(--card)] text-[var(--muted)] hover:text-[var(--teal)] hover:border-[var(--teal)]/40 hover:bg-[var(--soft)] transition-all cursor-pointer shadow-xs flex items-center justify-center select-none"
                            id="sidebar-toggle"
                            onclick="window.toggleSidebar()"
                            title="Toggle Navigation Menu (⌘\)"
                            aria-label="Toggle navigation menu"
                        >
                            <svg class="sidebar-toggle-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2"/>
                                <path d="M9 3v18"/>
                                <path d="m14 9 3 3-3 3"/>
                            </svg>
                        </button>

                        <div class="crumb flex items-center gap-2 text-xs font-semibold">
                            {!! view_event_render('header.left') !!}
                            <span class="text-[var(--muted)] hover:text-[var(--text)] transition-colors">Workspace</span>
                            <span class="text-[var(--border)]">/</span>
                            <span class="px-2 py-0.5 rounded-md bg-[var(--soft)] text-[var(--teal)] border border-[var(--teal)]/20 font-bold">
                                @yield('crumb', __('brand.portal') ?? 'Studio')
                            </span>
                        </div>
                    </div>


                    <!-- Right Controls: Theme Switcher, Language & Profile -->
                    <div class="header-right flex items-center gap-2.5">
                        {!! view_event_render('header.right') !!}

                        <!-- Theme Toggle Component -->
                        <theme-toggle></theme-toggle>

                        <!-- Language Switcher Component -->
                        <x-admin::language-toggle />

                        @auth
                            @php
                                $adminUser = auth()->user();
                                $profileData = [
                                    'name' => $adminUser?->name ?? 'Admin',
                                    'email' => $adminUser?->email ?? '',
                                    'role' => $adminUser?->role?->name ?? 'Administrator',
                                ];
                            @endphp
                            <x-admin::dropdown.profile
                                :user="$profileData"
                                :account-url="route('admin.account.edit')"
                                :users-url="route('settings.users')"
                                :attributes-url="url('catalog/attributes')"
                                :logout-url="route('admin.session.destroy')"
                                :csrf-token="csrf_token()"
                            />
                        @endauth
                    </div>

                </div>
            </header>

            <!-- 3. WORKSPACE CANVAS -->
            <main class="workspace flex-1 px-4 sm:px-6 lg:px-8 max-w-[1720px] w-full mx-auto py-6">
                {!! view_event_render('content.before') !!}
                @yield('content')
                {!! view_event_render('content.after') !!}
            </main>

        </div>

    </div>

    {!! view_event_render('layout.body.after') !!}
    @stack('scripts')
</body>
</html>
