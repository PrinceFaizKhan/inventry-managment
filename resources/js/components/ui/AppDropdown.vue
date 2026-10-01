<script setup>import { ref } from 'vue'; const open=ref(false)</script>
<template><div class="dropdown"><button class="dropdown-trigger" @click="open=!open"><slot name="trigger"/></button><div v-if="open" class="dropdown-menu"><slot/></div></div></template>
