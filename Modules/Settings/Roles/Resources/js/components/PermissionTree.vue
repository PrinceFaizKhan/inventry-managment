<script setup>
import { computed, ref, watchEffect } from 'vue'
import PermissionTreeNode from './PermissionTreeNode.vue'
const props = defineProps({ nodes: Array, modelValue: Array }); const emit = defineEmits(['update:modelValue'])
function leaves(nodes) { return nodes.flatMap(node => node.children?.length ? leaves(node.children) : [node.key]) }
const keys = computed(() => leaves(props.nodes || [])); const allSelected = computed(() => keys.value.length > 0 && keys.value.every(key => props.modelValue.includes(key))); const partial = computed(() => props.modelValue.length > 0 && !allSelected.value)
const selectAll = ref(null); watchEffect(() => { if (selectAll.value) selectAll.value.indeterminate = partial.value })
function toggleAll(){emit('update:modelValue',allSelected.value?[]:keys.value)}
</script>
<template><section class="permissions"><div class="permission-section-head"><div><h2>Custom permissions</h2><p>Select modules and the actions this role can perform.</p></div><span>{{ modelValue.length }} of {{ keys.length }} selected</span></div><div class="permission-tools"><label><input ref="selectAll" type="checkbox" :checked="allSelected" @change="toggleAll"/>Select all permissions</label><button type="button" @click="emit('update:modelValue',[])">Clear all</button></div><div class="permission-tree"><PermissionTreeNode v-for="node in nodes" :key="node.key" :node="node" :selected="modelValue" @change="emit('update:modelValue',$event)"/></div><p class="permission-help">Unchecked permissions are denied. New permissions remain unchecked for Custom roles.</p></section></template>
