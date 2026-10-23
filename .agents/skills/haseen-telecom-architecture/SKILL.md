---
name: haseen-telecom-blade-vue
description: "Core architectural standards for Haseen Telecom application: strict Zero .vue policy, Blade + inline Vue 3 x-template architecture, global UI primitives (such as v-toggle), HTTP JSON communication rules, and verification workflow."
license: MIT
metadata:
  author: Haseen Telecom
  version: 1.0.0
---

# Haseen Telecom Architecture & Development Skill

This skill defines the authoritative frontend and full-stack development guidelines for the **Haseen Telecom** application. All developers and AI agents must follow these rules without exception.

---

## 1. Core Architectural Law: Zero `.vue` Files

> [!IMPORTANT]
> **Do NOT create `.vue` Single File Components.**
> All UI views and components must be written directly in Laravel Blade templates (`.blade.php`) or configured as pure JavaScript modules in `resources/js/`.

### Why this rule exists:
- The project follows a modular Laravel architecture where Blade acts as the server-rendered orchestrator and Vue 3 provides client-side reactivity.
- Keeping templates in Blade ensures native Laravel authorization (`Gate::authorize`), Blade directives (`@extends`, `@section`, `@pushOnce`), and fast runtime rendering without requiring complex SFC compilation for every sub-view.

---

## 2. Blade + Vue 3 Component Standard

### Structure of a Page Component
Every interactive page should follow this three-part layout in its `.blade.php` file:

```html
@extends('admin::layouts.admin')

@section('title', 'Page Title')

@section('content')
<div class="page-container">
    <!-- 1. Component Invocation with Server Data -->
    <v-my-feature :initial-data='@json($data)'></v-my-feature>
</div>

@pushOnce('scripts')
<!-- 2. Vue X-Template definition -->
<script type="text/x-template" id="v-my-feature-template">
    <div class="space-y-6">
        <h1 class="text-xl font-bold text-[var(--text)]">@{{ title }}</h1>

        <!-- Use global reusable primitives -->
        <v-toggle
            v-model="isEnabled"
            label="Enable Feature"
            description="Toggle this feature on or off"
            @change="handleToggleChange"
        />

        <button type="button" @click="save" class="button button--primary text-xs">
            Save Changes
        </button>
    </div>
</script>

<!-- 3. Component JavaScript Module Definition -->
<script type="module">
    const MyFeatureComponent = {
        template: '#v-my-feature-template',
        props: {
            initialData: {
                type: Object,
                default: () => ({})
            }
        },
        data() {
            return {
                title: this.initialData?.title || 'Default Title',
                isEnabled: Boolean(this.initialData?.is_enabled),
                saving: false,
                errors: {}
            };
        },
        methods: {
            handleToggleChange(val) {
                console.log('Toggled to:', val);
            },
            async save() {
                this.saving = true;
                try {
                    const payload = {
                        is_enabled: this.isEnabled
                    };
                    await this.$axios.post('/api/my-endpoint', payload);
                } catch (err) {
                    this.errors = err.errors || {};
                } finally {
                    this.saving = false;
                }
            }
        }
    };

    // Register on the global Vue application instance
    app.component('v-my-feature', MyFeatureComponent);
</script>
@endPushOnce
@endsection
```

---

## 3. Global Mounting Lifecycle

In `resources/js/app.js`:
- `const app = createApp({ ... })` is created and exposed on `window.app = app`.
- Global primitives (`v-toggle`, `theme-toggle`, etc.) are registered on `app`.
- **Mount Timing**: `app.mount('#app')` is triggered on `window.addEventListener('load')`. This ensures that all inline `<script type="module">` tags in Blade views have finished executing and registering their components before Vue mounts the DOM.

---

## 4. Global UI Primitives

Reusable UI components must be registered globally so they can be used anywhere across any Blade view:

### `<v-toggle>` (or `<ui-toggle>`, `<toggle-switch>`)
Standard toggle switch replacing standard checkboxes for boolean states:
```html
<v-toggle
    v-model="required"
    label="Is Required"
    description="Require value before saving catalog items"
    @change="saved = false"
/>
```
- **Props**:
  - `modelValue` (boolean | number | string)
  - `label` (string)
  - `description` (string)
  - `disabled` (boolean)
  - `name` / `id` (optional for native form integration)
- **Emits**: `update:modelValue`, `change`

---

## 5. HTTP & API Communication Rules

When interacting with backend endpoints via JavaScript:
1. Always use `this.$axios` or `request` from `resources/js/lib/http.js`.
2. **Crucial Header**: Ensure `'Content-Type': 'application/json'` is present on all POST, PUT, and PATCH requests containing JSON bodies. Without this header, PHP fails to parse input data into Laravel's request bag.
3. **CSRF Protection**: Automatically handled via the `<meta name="csrf-token">` tag.

---

## 6. Theming & Styling Guidelines

- **Color Palette**:
  - Brand Teal: `var(--teal)`
  - Cards & Backgrounds: `var(--card)`, `var(--page)`, `var(--soft)`
  - Borders: `var(--border)`
  - Text & Accents: `var(--text)`, `var(--muted)`
- **Dark Mode**:
  - Always support both light and dark modes using Tailwind `.dark` classes and CSS variables.

---

## 7. Verification Checklist Before Finalizing Changes

Always run the following verification pipeline before completing any task:
1. **Pint Code Formatting**:
   ```bash
   vendor/bin/pint --format agent
   ```
2. **Frontend Build & Typecheck**:
   ```bash
   npm run typecheck && npm run build
   ```
3. **Automated Tests**:
   ```bash
   php artisan test --compact
   ```
   All test suites must pass 100% green.
