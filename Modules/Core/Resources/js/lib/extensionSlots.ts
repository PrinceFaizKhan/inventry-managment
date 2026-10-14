import { shallowRef, type Component, type ShallowRef } from 'vue'

const registeredSlots = new Map<string, ShallowRef<Component[]>>()

/**
 * Register a component into a named extension slot (Vue equivalent of view_event_render).
 * Example: registerExtensionComponent('catalog.attribute.form.after', MyCustomCard)
 */
export function registerExtensionComponent(slotName: string, component: Component): void {
  if (!registeredSlots.has(slotName)) {
    registeredSlots.set(slotName, shallowRef<Component[]>([]))
  }
  const slotRef = registeredSlots.get(slotName)!
  const current = slotRef.value || []
  if (!current.includes(component)) {
    slotRef.value = [...current, component]
  }
}

/**
 * Retrieve components registered to a named extension slot.
 */
export function getExtensionComponents(slotName: string): ShallowRef<Component[]> {
  if (!registeredSlots.has(slotName)) {
    registeredSlots.set(slotName, shallowRef<Component[]>([]))
  }
  return registeredSlots.get(slotName)!
}
