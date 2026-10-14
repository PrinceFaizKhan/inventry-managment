export function normalizeAttributeCode(value: string): string {
  return value.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '')
}

export function isValidAttributeCode(value: string): boolean {
  return /^[a-z][a-z0-9_]*$/.test(value)
}
