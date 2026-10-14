import type { Component } from 'vue'

const customAttributeComponents: Record<string, Component> = {}

/**
 * Register a custom Vue component for an attribute type.
 * Modules can call this to introduce custom renderers (e.g. ColorPicker, DatePicker, Barcode).
 */
export function registerAttributeComponent(type: string, component: Component): void {
  customAttributeComponents[type] = component
}

/**
 * Resolve a custom component for an attribute type, if registered.
 */
export function resolveAttributeComponent(type: string): Component | null {
  return customAttributeComponents[type] || null
}

/**
 * Check if an attribute type has a registered custom component.
 */
export function hasCustomAttributeComponent(type: string): boolean {
  return Boolean(customAttributeComponents[type])
}
