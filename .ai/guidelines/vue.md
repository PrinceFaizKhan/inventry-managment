# Vue 3

- Use Vue 3 Single File Components (SFCs) with `<script setup>`.
- Prefer `ref()` for state management and `computed()` for derived state.
- Use explicit types with TypeScript in `<script setup lang="ts">` where applicable.
- Re-use existing UI components in `resources/js/components/ui/` (`BaseButton`, `FormField`, `SelectControl`, `AppDropdown`) before creating new primitives.
- Perform API requests through `request` from `resources/js/lib/http.js`.
- Always verify frontend changes with `npm run typecheck` and `npm run build`.
