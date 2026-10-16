@extends('admin::layouts.admin')

@section('title', 'My Account')
@section('crumb', 'My Account')

@section('content')
<div class="account-page space-y-6 max-w-5xl">
    <div class="flex items-center justify-between pb-4 border-b border-[var(--border)]">
        <div>
            <p class="eyebrow">Admin / Profile</p>
            <h1 class="text-2xl font-bold tracking-tight text-[var(--text)]">My Account</h1>
            <p class="page-copy text-sm text-[var(--muted)] mt-1">Manage your administrator profile, email address, and security credentials.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard.index') }}" class="button button--outline text-xs">
                Back to Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-lg font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg font-medium space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.account.update') }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        @csrf
        @method('PUT')

        <!-- Left Column: Details & Password -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Profile Info Card -->
            <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                <h2 class="text-base font-bold text-[var(--text)] border-b border-[var(--border)] pb-2.5">
                    Profile Information
                </h2>

                <div>
                    <label for="name" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                        Full Name <span class="text-[var(--teal)]">*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                    />
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                        Email Address <span class="text-[var(--teal)]">*</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                    />
                </div>
            </div>

            <!-- Password Card -->
            <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                <div class="border-b border-[var(--border)] pb-2.5">
                    <h2 class="text-base font-bold text-[var(--text)]">Change Password</h2>
                    <p class="text-xs text-[var(--muted)] mt-0.5">Leave blank if you do not want to change your password.</p>
                </div>

                <div>
                    <label for="current_password" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                        Current Password
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            New Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            placeholder="At least 6 characters"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                        />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-[var(--text)] mb-1.5">
                            Confirm New Password
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            autocomplete="new-password"
                            placeholder="Re-enter new password"
                            class="w-full h-10 px-3.5 rounded-lg border border-[var(--border)] bg-[var(--card)] text-sm text-[var(--text)] focus:outline-none focus:border-[var(--teal)] focus:ring-1 focus:ring-[var(--teal)]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Account Card & Submit -->
        <div class="space-y-6">
            <div class="p-5 rounded-xl border border-[var(--border)] bg-[var(--card)] shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-[var(--border)]">
                    <span class="w-12 h-12 rounded-full bg-[var(--soft)] text-[var(--teal)] font-bold text-base grid place-items-center">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-sm text-[var(--text)] truncate">{{ $user->name }}</div>
                        <div class="text-xs text-[var(--muted)] truncate">{{ $user->email }}</div>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex justify-between py-1">
                        <span class="text-[var(--muted)]">Role:</span>
                        <span class="font-semibold px-2 py-0.5 rounded-full bg-[var(--soft)] text-[var(--teal)]">
                            {{ $user->role?->name ?? 'Administrator' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-[var(--muted)]">Status:</span>
                        <span class="font-semibold text-emerald-700">Active</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-[var(--muted)]">Member Since:</span>
                        <span class="text-[var(--text)] font-medium">{{ $user->created_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-[var(--border)]">
                    <button type="submit" class="button button--primary w-full text-xs inline-flex items-center justify-center gap-1.5">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
