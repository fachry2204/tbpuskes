<script setup lang="ts">
defineProps<{name?: string}>()
const icons:Record<string,string>={home:'home',users:'users',pill:'pill',calendar:'calendar',chart:'chart',monitoring:'monitoring-tb.png',report:'alert',settings:'settings',alert:'alert'}
const iconSrc=(name?:string)=>{const icon=icons[name||'home']||'home';return '/icons/'+(icon.includes('.')?icon:icon+'-color.png')}
</script>
<template><img class="admin-png-icon" :src="iconSrc(name)" alt="" aria-hidden="true"/></template>
<style>
.admin-shell .admin-png-icon{width:28px;height:28px;object-fit:contain;flex-shrink:0;filter:drop-shadow(0 2px 2px #16537520)}
.shared-sidebar nav .admin-png-icon{width:29px;height:29px}
.overview-metrics article>span .admin-png-icon{width:36px;height:36px}
.card h3 .admin-png-icon{width:25px;height:25px}
.location-empty>.admin-png-icon{width:56px;height:56px}
</style>
