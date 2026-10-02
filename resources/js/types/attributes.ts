export interface AttributeSettingDefinition {
  label: string
  type: 'select' | 'multi_select' | 'radio' | 'integer' | 'decimal' | 'boolean'
  default?: string | number | boolean | string[]
  min?: number
  max?: number
  options?: Record<string, string>
  applies_to?: string[]
}

export interface AttributeTypeDefinition {
  label: string
  settings: Record<string, AttributeSettingDefinition>
}

export interface AttributeOptionData {
  id?: number
  code: string
  label: string
  sort_order: number
}

export interface AttributeData {
  id: number
  name: string
  code: string
  type: string
  required: boolean
  settings: Record<string, unknown>
  options: AttributeOptionData[]
}
