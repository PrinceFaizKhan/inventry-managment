<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), ['ur', 'ur-IN']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Haseen Telecom · Admin')</title>
    {!! view_event_render('layout.head') !!}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {!! view_event_render('layout.body.before') !!}
    <div id="app" class="admin">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
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
            </div>

            <nav>
                @php
                    $user = auth()->user() ?? new \App\Models\User;
                    $menuItems = app(\Modules\Core\Support\Menu\MenuRegistry::class)->for($user);
                @endphp

                @foreach($menuItems as $item)
                    @php
                        $iconSvg = match($item['icon'] ?? '') {
                            'layout-dashboard' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>',
                            'sliders-horizontal', 'tags', 'package' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 21v-7"/><path d="M4 10V3"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M20 21v-5"/><path d="M20 12V3"/><path d="M1 14h6"/><path d="M9 8h6"/><path d="M17 16h6"/></svg>',
                            'boxes' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
                            'clipboard-list' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 12h6"/><path d="M9 16h6"/></svg>',
                            'settings' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
                            'users' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
                            'shield-check' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>',
                            default => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/></svg>',
                        };
                    @endphp

                    @if(empty($item['children']))
                        <a href="{{ $item['route'] ? url($item['route']) : '#' }}" class="nav-item {{ request()->is($item['route'].'*') ? 'active' : '' }}">
                            {!! $iconSvg !!}
                            <span>{{ __($item['label']) ?: $item['label'] }}</span>
                        </a>
                    @else
                        <div class="nav-group mb-2">
                            <span class="px-3 py-1 text-xs font-semibold uppercase tracking-wider text-muted opacity-75 flex items-center gap-2">
                                {!! $iconSvg !!}
                                <span>{{ __($item['label']) ?: $item['label'] }}</span>
                            </span>
                            <div class="mt-1 space-y-0.5">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['route'] ? url(str_replace('.', '/', $child['route'])) : '#' }}" class="nav-item pl-8 {{ request()->is(str_replace('.', '/', $child['route']).'*') ? 'active' : '' }}">
                                        <span>{{ __($child['label']) ?: $child['label'] }}</span>
                                        @if(!empty($child['coming_soon']))
                                            <small class="badge badge-subtle">Soon</small>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <footer>
                {!! view_event_render('sidebar.bottom') !!}
                <div class="footer-meta text-xs text-muted">
                    <span>Haseen Telecom v2.0</span>
                </div>
            </footer>
        </aside>

        <!-- Main Body Area -->
        <div class="app-body">
            <header class="header">
                <div class="crumb">
                    {!! view_event_render('header.left') !!}
                    <b>@yield('crumb', __('brand.portal') ?? 'Admin Portal')</b>
                </div>

                <div class="header-right">
                    {!! view_event_render('header.right') !!}

                    <!-- Vue Language Switcher Component -->
                    <language-toggle></language-toggle>

                    @auth
                        <div class="dropdown">
                            <span class="dropdown-trigger">
                                <span class="avatar">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                                <span class="account-name">{{ auth()->user()->name }}</span>
                            </span>
                        </div>
                        <form method="POST" action="/logout" class="inline">
                            @csrf
                            <button type="submit" class="button button--outline text-xs h-8 px-3">
                                {{ __('action.logout') ?? 'Logout' }}
                            </button>
                        </form>
                    @endauth
                </div>
            </header>

            <main class="workspace">
                {!! view_event_render('content.before') !!}
                @yield('content')
                {!! view_event_render('content.after') !!}
            </main>
        </div>
    </div>
    {!! view_event_render('layout.body.after') !!}
</body>
</html>
