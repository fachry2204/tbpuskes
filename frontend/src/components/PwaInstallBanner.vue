<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
type PromptEvent=Event&{prompt:()=>Promise<void>}
const deferred=ref<PromptEvent|null>(null),showIos=ref(false),dismissed=ref(false)
const ios=computed(()=>/iPhone|iPad|iPod/.test(navigator.userAgent)&&!(window.navigator as Navigator&{standalone?:boolean}).standalone)
onMounted(()=>{dismissed.value=Boolean(localStorage.getItem('tb_pwa_dismissed_at'));window.addEventListener('beforeinstallprompt',(e)=>{e.preventDefault();deferred.value=e as PromptEvent});showIos.value=ios.value&&!dismissed.value})
async function install(){if(deferred.value)await deferred.value.prompt()}function close(){dismissed.value=true;showIos.value=false;localStorage.setItem('tb_pwa_dismissed_at',new Date().toISOString())}
</script>
<template><div v-if="!dismissed&&(deferred||showIos)" class="pwa-banner"><img src="/icons/monitoring-tb.png" alt="Ikon Empati TB"><div><b>{{showIos?'Tambahkan ke Home Screen':'Install Aplikasi Empati TB'}}</b><p v-if="showIos">Tekan Share, pilih Add to Home Screen, lalu Add.</p><p v-else>Tambahkan aplikasi agar lebih mudah digunakan.</p></div><button v-if="deferred" @click="install">Install</button><button class="pwa-close" @click="close">×</button></div></template>
