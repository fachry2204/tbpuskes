<script setup lang="ts">
import { ref } from 'vue'
const login=ref(''),password=ref(''),error=ref('')
async function submit(){try{const r=await fetch(`${import.meta.env.VITE_API_BASE_URL??'http://127.0.0.1:8010/api/v1'}/auth/login`,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify({login:login.value,password:password.value})});const p=await r.json();if(!r.ok)throw new Error(p.message??'Login gagal.');sessionStorage.setItem('tb_token',p.data.token);sessionStorage.setItem('tb_user',JSON.stringify(p.data.user));window.location.href=p.data.user.role==='kader'?'/kader':'/app'}catch(e){error.value=e instanceof Error?e.message:'Login gagal.'}}
</script>
<template><main class="login-page"><form @submit.prevent="submit"><div class="brand">TB<span>Care</span></div><h1>Masuk Aplikasi</h1><p>Untuk Pasien atau Kader PMO.</p><label>No. HP<input v-model="login" required></label><label>Password/PIN<input v-model="password" type="password" required></label><p v-if="error" class="login-error">{{error}}</p><button>Masuk</button></form></main></template>
