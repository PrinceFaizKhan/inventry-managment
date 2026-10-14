<script setup>defineProps({variant:{type:String,default:'primary'},type:{type:String,default:'button'}})</script>
<template><button :type="type" class="button" :class="`button--${variant}`"><slot/></button></template>
