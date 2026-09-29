<script setup lang="ts">
import { ref, onMounted } from 'vue'
import AdminIcon from './AdminIcon.vue'
import './admin-shell.css'
const user = JSON.parse(sessionStorage.getItem('tb_user') || '{}')
const notifications = ref<{id:number;title:string;message:string;is_read:boolean}[]>([])
const notificationTotal=ref<number|null>(null), notificationError=ref(''), showNotifications=ref(false)
onMounted(async()=>{try{const response=await fetch('/api/v1/notifications?per_page=20',{headers:{Authorization:'Bearer '+sessionStorage.getItem('tb_token'),Accept:'application/json'}});const payload=await response.json();if(!response.ok)throw new Error(payload.message || 'Impossible de charger');notifications.value=payload.data;notificationTotal.value=payload.meta.total}catch{notificationError.value='Tidak dapat memuat notifikasi. Silakan muat ulang halaman.'}})
const date = new Date().toLocaleDateString('id-ID', {weekday:'long',day:'numeric',month:'long',year:'numeric'})
const menus = [['/admin','Dashboard','home'],['/admin/patients','Data Pasien','users'],['/admin/schedules','Jadwal Kontrol','calendar'],['/admin/cadres','Data Kader','users'],['/admin/monitoring','Monitoring Pasien','monitoring'],['/admin/statistics','Statistik','chart'],['/admin/reports','Laporan','report'],['/admin/treatment-places','Tempat Pengobatan','home'],['/admin/users','Manajemen User','settings']]
function active(url: string) { return url === '/admin' ? location.pathname === url : location.pathname.startsWith(url) }
function logout() { sessionStorage.removeItem('tb_token'); sessionStorage.removeItem('tb_user'); location.href='/' }
</script>
<style scoped>
.shared-sidebar { scrollbar-width:thin; scrollbar-color:#15958b26 transparent; }
.shared-sidebar:hover { scrollbar-color:#15958b60 transparent; }
.shared-sidebar::-webkit-scrollbar { width:4px; height:4px; }
.shared-sidebar::-webkit-scrollbar-track { background:transparent; }
.shared-sidebar::-webkit-scrollbar-thumb { background:#15958b26; border-radius:10px; }
.shared-sidebar:hover::-webkit-scrollbar-thumb { background:#15958b60; }
.shared-sidebar::-webkit-scrollbar-button { display:none; width:0; height:0; }
.sidebar-art { position:relative; isolation:isolate; flex-shrink:0; margin: auto 10px 10px; padding:22px 8px 0; border:1px solid #fff; border-radius:22px; background:radial-gradient(circle at 65% 38%,#ffffffef,transparent 55%),linear-gradient(145deg,#e6fbfa,#bfe9dd); box-shadow:0 10px 20px -12px #168e8070,inset 0 1px 0 #fff; }
.sidebar-art::before { content:''; position:absolute; inset:0; z-index:-1; pointer-events:none; background:url('/illustrations/banner-leaves.svg') center bottom / auto 200px no-repeat; opacity:.7; }
.sidebar-art::after { content:''; position:absolute; width:130px; height:130px; border:1px solid #fff9; border-radius:50%; top:85px; left:25px; box-shadow:0 0 0 15px #ffffff24,0 0 0 30px #ffffff12; z-index:-1; pointer-events:none; }
.sidebar-art b { position:relative; font-size:18px; line-height:1.55; color:#10868a; text-shadow:0 1px 0 #fff; }
.sidebar-art img { position:relative; display:block; width:100%; max-height:260px; object-fit:contain; object-position:center bottom; margin:14px auto 0; filter:drop-shadow(0 5px 5px #20776128); }
@media(max-width:700px) { .sidebar-art { display:none; } }
.shared-header { position:relative; isolation:isolate; z-index:20; padding:22px 24px; margin:14px 0 24px; gap:16px; border:1px solid white; border-radius:20px; background:linear-gradient(115deg,#fff 20%,#eefbff 65%,#e3f8ef); box-shadow:0 12px 26px -16px #146d8660,inset 0 1px 0 white; }
.shared-header::before { content:''; position:absolute; inset:0; border-radius:inherit; z-index:-1; pointer-events:none; background:url('/illustrations/banner-leaves.svg') right bottom/240px auto no-repeat; opacity:.2; }
.header-title{flex:1 1 390px}.header-kicker{display:block;font-size:9px;font-weight:bold;color:#249b9c;letter-spacing:1.4px;margin-bottom:7px}
.shared-header h1{font-size:23px}.shared-header p{line-height:1.5}
.header-date,.notification-trigger{display:flex;align-items:center;gap:9px;padding:10px 12px;background:#ffffffb8;border:1px solid white;border-radius:13px;box-shadow:0 4px 12px #1e819410}
.header-date img,.notification-trigger img{width:40px;height:40px;object-fit:contain}
.header-date small{font-size:10px;color:#6b929e}.shared-header .header-date time{display:block;padding:0;margin:0;background:none;max-width:145px;font-size:12px;color:#22556a}
.header-notifications{position:relative;z-index:100}.notification-trigger{cursor:pointer;color:#22556a;font:600 12px Arial;text-align:left}.notification-trigger small{display:block;font-weight:normal;font-size:10px;margin-top:4px;color:#718f98}
.notification-trigger:focus-visible{outline:3px solid #36b5c5;outline-offset:3px}.notification-dropdown{position:absolute;right:0;top:calc(100% + 12px);width:min(340px,80vw);max-height:380px;overflow:auto;background:white;border:1px solid #d9ece9;border-radius:14px;padding:18px;box-shadow:0 15px 40px #163a5430;z-index:200}.notification-dropdown h2{font-size:16px;margin:0 0 12px}.notification-dropdown article{padding:12px 0;border-bottom:1px solid #e5efec}.notification-dropdown article small{display:block;color:#188b85;font-size:10px}
.shared-account{padding:10px 12px;background:#ffffff9e;border:1px solid white;border-radius:13px}.shared-account>span{box-shadow:0 3px 8px #138c8920;border:2px solid white}
@media(max-width:700px){.shared-header{padding:16px;margin-top:12px;gap:10px}.header-title{flex-basis:100%}.shared-header h1{font-size:19px}.header-date{flex:1}.shared-account{width:100%}.header-kicker{font-size:8px;letter-spacing:1px}}
.header-logout { margin-left:auto; align-self:center; padding:11px 16px; border:1px solid #f2c7c7; border-radius:11px; background:#fff5f4; color:#b33b36; font:600 12px Arial,sans-serif; cursor:pointer; box-shadow:0 3px 9px #a6363610; }
.header-logout:hover { background:#ffe4e1; border-color:#db8d86; }
.header-logout:focus-visible { outline:3px solid #dc8279; outline-offset:3px; }
@media(max-width:700px) { .shared-header { padding-top:56px; } .header-logout { position:absolute; top:12px; right:16px; } }
</style>
<template><div class="admin-shell"><aside class="shared-sidebar"><a class="shared-brand" href="/admin"><span class="health-mark">✚</span><span><b>Empati TB</b><small>Puskesmas Sehat Bersama<br>Unit Indonesia Bebas TB</small></span></a><nav aria-label="Menu administrasi"><a v-for="menu in menus" :key="menu[0]" :href="menu[0]" :class="{active:active(menu[0]!)}" :aria-current="active(menu[0]!)?'page':undefined"><AdminIcon :name="menu[2]"/><span>{{menu[1]}}</span></a></nav><div class="sidebar-art"><b>Akhiri TB<br>Mulai dari Kita ♡</b><img src="/illustrations/sidebar-team.png" alt="Dua petugas kesehatan mendampingi pengobatan TB"/></div></aside><div class="shared-main"><header class="shared-header"><div class="header-title"><span class="header-kicker">EMPATI TB • PELAYANAN TB</span><h1>Sistem Informasi <em>Empati TB</em></h1><p>Pantau pengobatan, dampingi pasien, wujudkan Indonesia bebas TB.</p></div><div class="header-date"><img src="/icons/calendar-color.png" alt=""/><div><small>Hari ini</small><time>{{date}}</time></div></div><div class="header-notifications" @keydown.esc="showNotifications=false"><button class="notification-trigger" type="button" :aria-expanded="showNotifications" aria-controls="header-notification-list" aria-label="Buka notifikasi" @click="showNotifications=!showNotifications"><img src="/icons/notification-color.png" alt=""/><span>Notifikasi<small>{{notificationTotal===null?'—':notificationTotal+' pesan'}}</small></span></button><section v-if="showNotifications" id="header-notification-list" class="notification-dropdown" aria-label="Daftar notifikasi"><h2>Notifikasi Anda</h2><p v-if="notificationError" role="alert">{{notificationError}}</p><p v-else-if="notificationTotal===null">Memuat notifikasi…</p><p v-else-if="!notifications.length">Belum ada notifikasi.</p><article v-for="item in notifications" :key="item.id"><b>{{item.title}}</b><small>{{item.is_read?'Sudah dibaca':'Belum dibaca'}}</small><p>{{item.message}}</p></article><p v-if="notificationTotal && notificationTotal>notifications.length">Menampilkan {{notifications.length}} pesan terbaru dari {{notificationTotal}}.</p></section></div><div class="shared-account"><span>{{user.name?.charAt(0) || 'A'}}</span><div><b>{{user.name}}</b><small>{{user.role === 'admin'?'Administrator':'Petugas'}}</small></div></div><button class="header-logout" type="button" @click="logout" aria-label="Logout dari akun">Logout</button></header><slot/><footer class="shared-footer"><span>Sistem Informasi Empati TB</span><b>Sehat Bersama, Indonesia Bebas TB ♡</b></footer></div></div></template>
