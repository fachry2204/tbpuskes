<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import AdminIcon from './AdminIcon.vue'

type CountRow = { total: number; status?: string; rw?: string; rt?: string; name?: string }
type Statistics = {
  generated_at: string
  summary: { total:number; active:number; completed:number; paused:number; moved:number; deceased:number }
  gender: { male:number; female:number }
  age: { under_40:number; age_40_plus:number }
  cadre: { assigned:number; unassigned:number }
  today: { controls:number; taken:number; not_taken:number; reported:number; not_reported:number; side_effects:number }
  by_status: CountRow[]
  by_rw: CountRow[]
  by_rt: CountRow[]
  by_treatment_place: CountRow[]
  adherence_history: { date:string; total:number; percent:number }[]
}

const data = ref<Statistics | null>(null)
const error = ref('')
const loading = ref(true)

const statusLabels: Record<string, string> = {
  active: 'Aktif', completed: 'Selesai pengobatan', paused: 'Ditunda', moved: 'Pindah', deceased: 'Meninggal',
}

const cards = computed(() => {
  if (!data.value) return []
  const report = data.value
  return [
    { label: 'Total pasien', value: report.summary.total, detail: 'Seluruh data pasien', icon: 'users', tone: 'teal' },
    { label: 'Pasien aktif', value: report.summary.active, detail: 'Sedang menjalani pengobatan', icon: 'monitoring', tone: 'green' },
    { label: 'Laki-laki', value: report.gender.male, detail: 'Berdasarkan jenis kelamin', icon: 'users', tone: 'blue' },
    { label: 'Perempuan', value: report.gender.female, detail: 'Berdasarkan jenis kelamin', icon: 'users', tone: 'violet' },
    { label: 'Usia di bawah 40', value: report.age.under_40, detail: 'Usia pada hari ini', icon: 'chart', tone: 'orange' },
    { label: 'Usia 40 tahun ke atas', value: report.age.age_40_plus, detail: 'Usia pada hari ini', icon: 'chart', tone: 'rose' },
    { label: 'Sudah memiliki kader', value: report.cadre.assigned, detail: 'Pasien dengan pendamping', icon: 'users', tone: 'mint' },
    { label: 'Belum memiliki kader', value: report.cadre.unassigned, detail: 'Perlu penugasan kader', icon: 'alert', tone: 'yellow' },
    { label: 'Kontrol hari ini', value: report.today.controls, detail: 'Jadwal kontrol tercatat', icon: 'calendar', tone: 'sky' },
    { label: 'Laporan obat hari ini', value: report.today.reported, detail: 'Pasien aktif sudah melapor', icon: 'pill', tone: 'indigo' },
  ]
})

const maximumTerritory = computed(() => Math.max(1, ...(data.value?.by_rw.map(item => Number(item.total)) ?? [1])))
const maximumPlace = computed(() => Math.max(1, ...(data.value?.by_treatment_place.map(item => Number(item.total)) ?? [1])))
const updatedAt = computed(() => data.value ? new Date(data.value.generated_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : '')
const medicationTotal = computed(() => data.value ? Math.max(1, data.value.today.taken + data.value.today.not_taken + data.value.today.not_reported) : 1)

async function loadStatistics() {
  loading.value = true
  error.value = ''
  try {
    const response = await fetch('/api/v1/patients/statistics', {
      headers: { Authorization: 'Bearer ' + sessionStorage.getItem('tb_token'), Accept: 'application/json' },
    })
    const payload = await response.json()
    if (!response.ok) throw new Error(payload.message || 'Statistik pasien tidak dapat dimuat.')
    data.value = payload.data
  } catch (exception) {
    error.value = exception instanceof Error ? exception.message : 'Statistik pasien tidak dapat dimuat.'
  } finally {
    loading.value = false
  }
}

onMounted(loadStatistics)
</script>

<template>
  <main class="statistics-page">
    <header class="statistics-heading">
      <div>
        <p>Monitoring pasien / Statistik</p>
        <h2>Laporan Data Statistik Pasien</h2>
        <span>Ringkasan lengkap pasien TB untuk memantau pendampingan, pengobatan, dan wilayah kerja.</span>
      </div>
      <button type="button" class="refresh-button" :disabled="loading" @click="loadStatistics">
        <AdminIcon name="chart" /> {{ loading ? 'Memuat…' : 'Perbarui data' }}
      </button>
    </header>

    <p v-if="error" class="statistics-error" role="alert">{{ error }}</p>
    <p v-else-if="loading && !data" class="statistics-loading" role="status">Memuat laporan statistik pasien…</p>

    <template v-if="data">
      <section class="report-overview" aria-label="Ringkasan laporan statistik">
        <div class="report-overview-copy">
          <span class="report-chip"><AdminIcon name="report" /> Laporan terkini</span>
          <h3>{{ data.summary.active }} pasien aktif sedang dipantau</h3>
          <p>Dari {{ data.summary.total }} pasien terdaftar, {{ data.cadre.unassigned }} pasien masih perlu ditetapkan kader pendamping.</p>
          <div class="report-overview-notes">
            <span><b>{{ data.today.controls }}</b> kontrol hari ini</span>
            <span><b>{{ data.today.side_effects }}</b> laporan efek samping</span>
          </div>
        </div>
        <div class="report-overview-stat">
          <span>Kepatuhan laporan hari ini</span>
          <strong>{{ data.summary.active ? Math.round(data.today.reported / data.summary.active * 100) : 0 }}%</strong>
          <small>{{ data.today.reported }} dari {{ data.summary.active }} pasien aktif sudah mengirim laporan.</small>
        </div>
      </section>

      <section class="statistics-cards" aria-label="Kartu statistik pasien">
        <article v-for="card in cards" :key="card.label" :class="['statistics-card', `statistics-card--${card.tone}`]">
          <span class="statistics-card-icon"><AdminIcon :name="card.icon" /></span>
          <div>
            <small>{{ card.label }}</small>
            <strong>{{ card.value }}</strong>
            <p>{{ card.detail }}</p>
          </div>
        </article>
      </section>

      <section class="statistics-grid">
        <article class="statistics-panel status-panel">
          <div class="panel-heading">
            <div><span class="panel-eyebrow">STATUS PERAWATAN</span><h3>Status pasien</h3></div>
            <span class="panel-total">{{ data.summary.total }} pasien</span>
          </div>
          <div class="status-list">
            <div v-for="row in data.by_status" :key="row.status" class="status-row">
              <span class="status-dot" :class="`status-dot--${row.status}`"></span>
              <b>{{ statusLabels[row.status || ''] || row.status }}</b>
              <div class="status-track"><i :style="{ width: `${data.summary.total ? Math.round(row.total / data.summary.total * 100) : 0}%` }"></i></div>
              <strong>{{ row.total }}</strong>
            </div>
          </div>
        </article>

        <article class="statistics-panel medication-panel">
          <div class="panel-heading"><div><span class="panel-eyebrow">HARI INI</span><h3>Status laporan minum obat</h3></div><AdminIcon name="pill" /></div>
          <div class="medication-summary">
            <div class="medication-donut" :style="{ background: `conic-gradient(#14a98b 0 ${data.today.taken / medicationTotal * 100}%, #edaf35 0 ${(data.today.taken + data.today.not_taken) / medicationTotal * 100}%, #dcebe8 0 100%)` }">
              <div><strong>{{ data.today.reported }}</strong><small>melapor</small></div>
            </div>
            <ul>
              <li><i class="legend-good"></i><span>Sudah minum</span><b>{{ data.today.taken }}</b></li>
              <li><i class="legend-warning"></i><span>Belum minum</span><b>{{ data.today.not_taken }}</b></li>
              <li><i class="legend-empty"></i><span>Belum lapor</span><b>{{ data.today.not_reported }}</b></li>
            </ul>
          </div>
          <p class="panel-note">Perhitungan laporan minum obat hanya untuk pasien dengan status aktif.</p>
        </article>

        <article class="statistics-panel adherence-panel">
          <div class="panel-heading"><div><span class="panel-eyebrow">7 HARI TERAKHIR</span><h3>Kepatuhan minum obat</h3></div><span class="panel-total">Persentase pasien aktif</span></div>
          <div class="adherence-bars" aria-label="Grafik kepatuhan minum obat tujuh hari terakhir">
            <div v-for="day in data.adherence_history" :key="day.date" class="adherence-day">
              <b>{{ day.percent }}%</b>
              <div class="adherence-bar"><i :style="{ height: `${day.percent}%` }"></i></div>
              <small>{{ new Date(`${day.date}T00:00:00`).toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) }}</small>
            </div>
          </div>
        </article>

        <article class="statistics-panel territory-panel">
          <div class="panel-heading"><div><span class="panel-eyebrow">SEBARAN WILAYAH</span><h3>Pasien berdasarkan RW</h3></div><AdminIcon name="monitoring" /></div>
          <div v-if="data.by_rw.length" class="territory-list">
            <div v-for="row in data.by_rw" :key="row.rw" class="territory-row">
              <b>RW {{ row.rw }}</b><div><i :style="{ width: `${row.total / maximumTerritory * 100}%` }"></i></div><span>{{ row.total }} pasien</span>
            </div>
          </div>
          <p v-else class="empty-state">Belum ada data wilayah pasien.</p>
        </article>

        <article class="statistics-panel place-panel">
          <div class="panel-heading"><div><span class="panel-eyebrow">LAYANAN PENGOBATAN</span><h3>Tempat pengobatan</h3></div><AdminIcon name="home" /></div>
          <div v-if="data.by_treatment_place.length" class="place-list">
            <div v-for="row in data.by_treatment_place" :key="row.name" class="place-row">
              <div><b>{{ row.name }}</b><span>{{ row.total }} pasien</span></div><i :style="{ width: `${row.total / maximumPlace * 100}%` }"></i>
            </div>
          </div>
          <p v-else class="empty-state">Belum ada tempat pengobatan yang tercatat.</p>
        </article>

        <article class="statistics-panel rt-panel">
          <div class="panel-heading"><div><span class="panel-eyebrow">RINCIAN WILAYAH</span><h3>10 RT dengan pasien terbanyak</h3></div><span class="panel-total">RT / RW</span></div>
          <div class="table-scroll"><table><thead><tr><th>Wilayah</th><th>Jumlah pasien</th></tr></thead><tbody><tr v-for="row in data.by_rt" :key="`${row.rt}-${row.rw}`"><td><b>RT {{ row.rt }}</b><small>RW {{ row.rw }}</small></td><td>{{ row.total }} pasien</td></tr><tr v-if="!data.by_rt.length"><td colspan="2" class="empty-state">Belum ada rincian RT/RW.</td></tr></tbody></table></div>
        </article>
      </section>

      <p class="report-updated">Data dihitung dari seluruh pasien aktif dan arsip status pasien. Terakhir diperbarui: {{ updatedAt }}.</p>
    </template>
  </main>
</template>

<style scoped>
.statistics-page{padding:8px 18px 38px;color:#143f59}.statistics-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin:10px 0 24px}.statistics-heading p{margin:0 0 5px;color:#63889a;font-size:14px}.statistics-heading h2{margin:0;color:#103b5e;font-size:29px;letter-spacing:-.6px}.statistics-heading>div>span{display:block;margin-top:9px;color:#5e8194;font-size:14px}.refresh-button{display:inline-flex;align-items:center;gap:8px;flex:none;border:0;border-radius:11px;background:#0b877b;color:#fff;padding:12px 17px;font:700 14px Arial,sans-serif;cursor:pointer;box-shadow:0 7px 16px #087e7240}.refresh-button:disabled{opacity:.65;cursor:wait}.refresh-button :deep(.admin-png-icon){width:21px;height:21px;filter:brightness(0) invert(1)}.statistics-error,.statistics-loading{padding:14px 16px;border-radius:12px;background:#fff;color:#567989}.statistics-error{color:#a7342e;background:#fff0ef;border:1px solid #f2c5c1}.report-overview{display:flex;align-items:stretch;gap:24px;overflow:hidden;margin-bottom:20px;padding:27px 30px;border:1px solid #fff;border-radius:20px;background:linear-gradient(115deg,#e0f8f5,#f4fbff 60%,#d8f2ee);box-shadow:0 12px 25px -18px #0b6c7166}.report-overview-copy{flex:1}.report-chip{display:inline-flex;align-items:center;gap:7px;border-radius:999px;background:#fff;padding:7px 10px;color:#11867f;font-size:11px;font-weight:700}.report-chip :deep(.admin-png-icon){width:18px;height:18px}.report-overview h3{margin:13px 0 7px;font-size:24px}.report-overview p{max-width:660px;margin:0;color:#63818b;font-size:14px;line-height:1.55}.report-overview-notes{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.report-overview-notes span{border-radius:8px;background:#ffffffb8;padding:9px 12px;color:#52727c;font-size:12px}.report-overview-notes b{color:#0b837a;font-size:16px}.report-overview-stat{width:240px;display:flex;flex-direction:column;justify-content:center;padding:16px 0 16px 28px;border-left:1px solid #b8ddd7}.report-overview-stat span,.report-overview-stat small{color:#60808b;font-size:12px;line-height:1.45}.report-overview-stat strong{margin:5px 0;color:#0a8279;font-size:42px}.statistics-cards{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:20px}.statistics-card{display:flex;align-items:flex-start;gap:11px;min-height:124px;padding:16px;border:1px solid #e0eceb;border-radius:15px;background:#fff;box-shadow:0 7px 16px -15px #174e5b80}.statistics-card-icon{display:grid;place-items:center;flex:none;width:40px;height:40px;border-radius:12px}.statistics-card-icon :deep(.admin-png-icon){width:26px;height:26px}.statistics-card small{display:block;color:#5c7e89;font-size:11px;font-weight:700;line-height:1.25}.statistics-card strong{display:block;margin:5px 0 3px;color:#153f58;font-size:27px;line-height:1}.statistics-card p{margin:0;color:#78929a;font-size:10px;line-height:1.35}.statistics-card--teal .statistics-card-icon{background:#dcf7f2}.statistics-card--green .statistics-card-icon{background:#ddf5e8}.statistics-card--blue .statistics-card-icon{background:#e0f4ff}.statistics-card--violet .statistics-card-icon{background:#eee8ff}.statistics-card--orange .statistics-card-icon{background:#fff1db}.statistics-card--rose .statistics-card-icon{background:#ffe8ed}.statistics-card--mint .statistics-card-icon{background:#e1f7ed}.statistics-card--yellow .statistics-card-icon{background:#fff3d9}.statistics-card--sky .statistics-card-icon{background:#e3f5ff}.statistics-card--indigo .statistics-card-icon{background:#e9ecff}.statistics-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.statistics-panel{min-width:0;padding:20px;border:1px solid #e0eceb;border-radius:16px;background:#fff;box-shadow:0 9px 19px -18px #174e5b99}.panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:18px}.panel-heading h3{margin:3px 0 0;color:#173f57;font-size:17px}.panel-heading>:last-child:not(div){width:26px;height:26px;object-fit:contain}.panel-eyebrow{color:#15958b;font-size:9px;font-weight:800;letter-spacing:1px}.panel-total{color:#6c8994;font-size:10px;white-space:nowrap}.status-panel{grid-column:span 2}.status-list{display:grid;gap:13px}.status-row{display:grid;grid-template-columns:10px minmax(85px,1fr) minmax(80px,2.5fr) 26px;align-items:center;gap:8px;font-size:12px}.status-row b{font-weight:600}.status-row strong{font-size:13px;text-align:right}.status-dot{width:9px;height:9px;border-radius:50%;background:#7bbcc1}.status-dot--active{background:#14a98b}.status-dot--completed{background:#4d93d1}.status-dot--paused{background:#ecb238}.status-dot--moved{background:#9a85cf}.status-dot--deceased{background:#d97575}.status-track{height:8px;overflow:hidden;border-radius:10px;background:#edf3f2}.status-track i{display:block;height:100%;min-width:2px;border-radius:inherit;background:#169e91}.medication-summary{display:flex;align-items:center;gap:20px}.medication-donut{width:120px;height:120px;flex:none;border-radius:50%;display:grid;place-items:center}.medication-donut>div{display:grid;place-content:center;width:84px;height:84px;border-radius:50%;background:#fff;text-align:center}.medication-donut strong{font-size:26px}.medication-donut small{display:block;color:#718993;font-size:10px}.medication-summary ul{display:grid;gap:12px;margin:0;padding:0;list-style:none;flex:1}.medication-summary li{display:grid;grid-template-columns:10px 1fr auto;align-items:center;gap:7px;color:#55747f;font-size:12px}.medication-summary li b{color:#173f57}.medication-summary li i{width:9px;height:9px;border-radius:50%}.legend-good{background:#14a98b}.legend-warning{background:#edaf35}.legend-empty{background:#dcebe8}.panel-note{margin:17px 0 0;color:#779097;font-size:10px;line-height:1.45}.adherence-panel{grid-column:span 3}.adherence-bars{display:grid;grid-template-columns:repeat(7,1fr);align-items:end;gap:12px;height:166px;padding:0 8px;border-bottom:1px solid #dfece9}.adherence-day{display:grid;grid-template-rows:18px 1fr 20px;align-items:end;height:100%;text-align:center}.adherence-day>b{font-size:11px;color:#1c817a}.adherence-bar{display:flex;align-items:end;justify-content:center;height:100%;padding-top:8px}.adherence-bar i{display:block;width:min(44px,80%);min-height:3px;border-radius:8px 8px 0 0;background:linear-gradient(#34bdac,#0b8b80)}.adherence-day small{padding-top:7px;color:#6c8994;font-size:10px;white-space:nowrap}.territory-list{display:grid;gap:13px}.territory-row{display:grid;grid-template-columns:52px 1fr 57px;align-items:center;gap:9px;font-size:12px}.territory-row>div{height:8px;overflow:hidden;border-radius:999px;background:#edf3f2}.territory-row i{display:block;height:100%;border-radius:inherit;background:#26b2a2}.territory-row span{color:#6d8992;text-align:right;font-size:10px}.place-list{display:grid;gap:15px}.place-row>div{display:flex;justify-content:space-between;gap:12px;margin-bottom:7px}.place-row b{font-size:12px}.place-row span{color:#6d8992;font-size:10px;white-space:nowrap}.place-row>i{display:block;height:8px;min-width:3px;border-radius:999px;background:#4aa4cc}.rt-panel{grid-column:span 2}.table-scroll{overflow:auto}.table-scroll table{width:100%;border-collapse:collapse}.table-scroll th,.table-scroll td{padding:10px 4px;border-bottom:1px solid #e8f0ef;text-align:left;font-size:12px}.table-scroll th{color:#66828d;font-size:10px;text-transform:uppercase}.table-scroll td:last-child{text-align:right;color:#187d78;font-weight:700}.table-scroll td b,.table-scroll td small{display:block}.table-scroll td small{margin-top:3px;color:#75909a;font-size:10px}.empty-state{margin:18px 0;color:#779099;font-size:12px;text-align:center}.report-updated{margin:18px 0 0;color:#6f8a95;font-size:11px;text-align:right}@media(max-width:1250px){.statistics-cards{grid-template-columns:repeat(3,minmax(0,1fr))}.statistics-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.adherence-panel{grid-column:span 2}.rt-panel{grid-column:span 2}}@media(max-width:780px){.statistics-page{padding:4px 4px 28px}.statistics-heading{display:block}.statistics-heading h2{font-size:23px}.statistics-heading>div>span{font-size:13px;line-height:1.4}.refresh-button{margin-top:15px}.report-overview{display:block;padding:20px}.report-overview h3{font-size:20px}.report-overview-stat{width:auto;margin-top:20px;padding:18px 0 0;border-top:1px solid #b8ddd7;border-left:0}.statistics-cards,.statistics-grid{grid-template-columns:1fr}.statistics-card{min-height:104px}.status-panel,.adherence-panel,.rt-panel{grid-column:auto}.medication-summary{gap:13px}.medication-donut{width:108px;height:108px}.adherence-bars{gap:5px;padding:0}.adherence-day small{font-size:8px}.report-updated{text-align:left;line-height:1.5}}@media(prefers-reduced-motion:reduce){.refresh-button{box-shadow:none}}
</style>
