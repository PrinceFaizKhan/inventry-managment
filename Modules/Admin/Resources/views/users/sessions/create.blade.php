<x-admin::layouts.anonymous>
    <x-slot:title>
        {{ __('admin::app.title') ?? 'Sign In · Haseen Telecom' }}
    </x-slot>

    <div class="w-full max-w-md space-y-6">
        <!-- Brand Header -->
        <div class="flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[var(--teal)] text-[var(--teal-text)] grid place-items-center mb-3 shadow-md">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16.5 9.4 7.55 4.24a1.78 1.78 0 0 0-2.5 1.55v12.42a1.78 1.78 0 0 0 2.5 1.55L16.5 14.6a1.78 1.78 0 0 0 0-3.2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">
                Haseen <em class="not-italic text-[var(--teal)]">Telecom</em>
            </h1>
            <p class="text-xs font-semibold uppercase tracking-wider text-[var(--muted)] mt-1">
                Admin Workspace
            </p>
        </div>

        <!-- Login Card -->
        <div class="bg-[var(--card)] border border-[var(--border)] rounded-2xl p-7 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-bold text-[var(--text)]">
                    {{ __('admin::app.welcome_back') ?? 'Welcome back' }}
                </h2>
                <p class="text-xs text-[var(--muted)] mt-0.5">
                    {{ __('admin::app.welcome_sub') ?? 'Sign in to access your UnoPim admin portal.' }}
                </p>
            </div>

            <!-- Error Alerts -->
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

            @if (session('warning'))
                <div class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-xs font-medium">
                    {{ session('warning') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.session.store') }}" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                        {{ __('admin::app.email') ?? 'Email address' }} <span class="text-[var(--teal)]">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', 'admin@haseentelecom.test') }}"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="admin@haseentelecom.test"
                        class="w-full h-11 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-[var(--text)] text-sm focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                    />
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                        {{ __('admin::app.password') ?? 'Password' }} <span class="text-[var(--teal)]">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            value="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full h-11 px-3.5 pr-10 rounded-lg border border-[var(--border)] bg-[var(--card)] text-[var(--text)] text-sm focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                        />
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute right-0 top-0 h-11 w-10 grid place-items-center text-[var(--muted)] hover:text-[var(--text)]"
                            onclick="togglePasswordVisibility()"
                            aria-label="Toggle password visibility"
                        >
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 text-[var(--muted)] cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="remember"
                            id="remember"
                            value="1"
                            class="rounded border-[var(--border)] text-[var(--teal)] focus:ring-[var(--teal)]"
                        />
                        <span>{{ __('admin::app.remember_me') ?? 'Remember me' }}</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full h-11 rounded-lg bg-[var(--teal)] text-[var(--teal-text)] font-semibold text-sm hover:opacity-95 transition-opacity flex items-center justify-center gap-2 shadow-sm"
                >
                    <span>{{ __('admin::app.sign_in') ?? 'Sign In' }}</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>

                <!-- Demo Credentials Box -->
                <div class="mt-4 p-3 rounded-lg bg-[var(--page)] border border-[var(--border)] flex items-center justify-between text-xs">
                    <div>
                        <strong class="block text-[11px] text-[var(--teal)] font-semibold">Demo Administrator</strong>
                        <span class="text-[var(--muted)] text-[11px]">admin@haseentelecom.test / password</span>
                    </div>
                    <button
                        type="button"
                        onclick="fillAdminCredentials()"
                        class="px-2.5 py-1 rounded bg-[var(--card)] border border-[var(--border)] text-[var(--teal)] font-semibold text-[11px] hover:border-[var(--teal)] transition-colors"
                    >
                        Auto-fill
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-[var(--muted)]">
            <span>Haseen Telecom</span> <i>·</i> <span>UnoPim Admin v2.0</span>
        </div>
    </div>

    @push('scripts')
    <script>
        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        function fillAdminCredentials() {
            document.getElementById('email').value = 'admin@haseentelecom.test';
            document.getElementById('password').value = 'password';
        }
    </script>
    @endpush
</x-admin::layouts.anonymous>
