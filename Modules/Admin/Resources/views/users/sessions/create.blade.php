<x-admin::layouts.anonymous>
    <x-slot:title>
        {{ __('admin::app.title') ?? 'Sign In · Haseen Telecom' }}
    </x-slot>

    <div class="login">
        <header>
            <div class="brand">
                <span class="brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16.5 9.4 7.55 4.24a1.78 1.78 0 0 0-2.5 1.55v12.42a1.78 1.78 0 0 0 2.5 1.55L16.5 14.6a1.78 1.78 0 0 0 0-3.2z"/>
                    </svg>
                </span>
                <div>
                    <b>Haseen <em>Telecom</em></b>
                    <small>Admin Workspace</small>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <theme-toggle></theme-toggle>
                <language-toggle></language-toggle>
            </div>
        </header>

        <main>
            <section class="intro">
                <p class="eyebrow">Haseen Telecom · Admin Portal</p>
                <h1>Your business.<br/><em>Connected &amp; in control.</em></h1>
                <p>Manage your catalog, track inventory and keep every sale in one place.</p>
                <div class="tags">
                    <span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Catalog
                    </span>
                    <span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                        Inventory
                    </span>
                    <span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 12h6"/><path d="M9 16h6"/></svg>
                        Sales
                    </span>
                </div>
            </section>

            <section class="login-card">
                <span class="lock">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </span>

                <h2>Welcome back</h2>
                <p>Sign in to Haseen Telecom admin.</p>

                @if ($errors->any())
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.session.store') }}">
                    @csrf

                    <label class="field">
                        <span>Email address</span>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email', 'admin@haseentelecom.test') }}"
                            required
                            autofocus
                            placeholder="you@company.com"
                        />
                    </label>

                    <label class="field">
                        <span>Password</span>
                        <div class="password">
                            <input
                                id="loginPassword"
                                type="password"
                                name="password"
                                value="password"
                                required
                                placeholder="Enter your password"
                            />
                            <button
                                type="button"
                                onclick="togglePasswordVisibility()"
                                aria-label="Toggle password visibility"
                            >
                                <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </label>

                    <label class="remember">
                        <input type="checkbox" name="remember" value="1"/>
                        Remember me
                    </label>

                    <button type="submit" class="button button--primary">
                        <span>Sign in</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/>
                            <path d="m12 5 7 7-7 7"/>
                        </svg>
                    </button>
                </form>
            </section>
        </main>

        <footer>Haseen Telecom <i>·</i> Admin workspace</footer>
    </div>

    @pushOnce('scripts')
    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('loginPassword');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
    </script>
    @endpushOnce
</x-admin::layouts.anonymous>
