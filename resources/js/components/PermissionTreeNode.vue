<script setup>
import { computed, ref, watchEffect } from 'vue'
import { ChevronDown, ChevronRight } from 'lucide-vue-next'

const props = defineProps({ node: Object, selected: Array, depth: { type: Number, default: 0 } })
const emit = defineEmits(['change'])
const expanded = ref(false)
const checkbox = ref(null)
function leaves(node) { return node.children?.length ? node.children.flatMap(leaves) : [node.key] }
const descendants = computed(() => leaves(props.node))
const selectedCount = computed(() => descendants.value.filter(key => props.selected.includes(key)).length)
const checked = computed(() => selectedCount.value === descendants.value.length)
const partial = computed(() => selectedCount.value > 0 && !checked.value)
watchEffect(() => { if (checkbox.value) checkbox.value.indeterminate = partial.value })
function toggle() { const next = new Set(props.selected); descendants.value.forEach(key => checked.value ? next.delete(key) : next.add(key)); emit('change', [...next]) }
</script>
<template><div class="permission-node" :style="{ '--depth': depth }"><div class="permission-row" :class="{ 'permission-row--module': depth===0 }"><button v-if="node.children?.length" class="tree-arrow" :aria-label="`Toggle ${node.label}`" @click="expanded=!expanded"><ChevronDown v-if="expanded" :size="16"/><ChevronRight v-else :size="16"/></button><span v-else class="tree-arrow"></span><input :id="`permission-${node.key}`" ref="checkbox" type="checkbox" :checked="checked" @change="toggle"/><label :for="`permission-${node.key}`">{{ node.label }}</label></div><div v-if="node.children?.length && expanded" class="permission-children"><PermissionTreeNode v-for="child in node.children" :key="child.key" :node="child" :selected="selected" :depth="depth+1" @change="emit('change',$event)"/></div></div></template>
