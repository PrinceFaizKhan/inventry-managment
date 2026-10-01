<script setup>defineProps({label:String,modelValue:String,type:{type:String,default:'text'},placeholder:String}); defineEmits(['update:modelValue'])</script>
<template><label class="field"><span>{{label}}</span><input :type="type" :value="modelValue" :placeholder="placeholder" @input="$emit('update:modelValue',$event.target.value)"/><slot/></label></template>
