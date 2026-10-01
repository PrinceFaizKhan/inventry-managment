<script setup>
import { Boxes, Circle, ClipboardList, LayoutDashboard, Package, Settings, ShieldCheck, SlidersHorizontal, Tags, Users } from 'lucide-vue-next'
import { computed, ref, watch } from 'vue'
const props=defineProps({item:Object,activeRoute:String,depth:{type:Number,default:0}}); const emit=defineEmits(['navigate'])
const icons={ 'layout-dashboard':LayoutDashboard, 'sliders-horizontal':SlidersHorizontal, tags:Tags, package:Package, boxes:Boxes, 'clipboard-list':ClipboardList, settings:Settings, users:Users, 'shield-check':ShieldCheck }
const expanded=ref(false); const Icon=computed(()=>icons[props.item.icon] || Circle)
const active=computed(()=>props.item.route===props.activeRoute); const branchActive=computed(()=>active.value || props.item.children?.some(c=>c.route===props.activeRoute)); watch(branchActive,v=>{if(v) expanded.value=true},{immediate:true})
function activate(){if(props.item.children?.length){expanded.value=!expanded.value; return} if(props.item.enabled===false)return; emit('navigate',props.item.route)}
</script>
<template><div class="menu-node"><button class="nav-item" :class="{active,disabled:item.enabled===false}" :style="{paddingLeft:(12+depth*14)+'px'}" @click="activate"><component :is="Icon" :size="17"/><span>{{item.label}}</span><small v-if="item.coming_soon">Soon</small></button><div v-if="item.children?.length && expanded" class="children"><RecursiveMenuItem v-for="child in item.children" :key="child.key" :item="child" :active-route="activeRoute" :depth="depth+1" @navigate="emit('navigate',$event)"/></div></div></template>
