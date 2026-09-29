<script setup lang="ts">
import { ref, onMounted } from 'vue'

type Report = {
  id: number
  patient: { full_name: string }
  medication_taken: boolean
  has_side_effect: boolean
  status: string
  server_received_at: string
  formatted_address?: string
  verification_note?: string
  verified_at?: string
  photo_path?: string
}

type UnreportedPatient = {
  id: number
  full_name: string
  phone: string
  cadre?: { full_name: string }
}

const activeTab = ref<'reported' | 'unreported'>('reported')
const reports = ref<Report[]>([])
const unreported = ref<UnreportedPatient[]>([])
const loading = ref(true)
const error = ref('')
const selectedReport = ref<Report | null>(null)
const reportPhoto = ref<string | null>(null)
const verificationStatus = ref('')
const verificationNote = ref('')
const verifying = ref(false)

const formatTime = (value?: string | null) => {
  if (!value) return ''
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '' : date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}
const formatFullDate = (value?: string | null) => {
  if (!value) return ''
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '' : date.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' })
}

const statusLabels: Record<string, string> = { pending: 'Menunggu Verifikasi', verified: 'Terverifikasi', rejected: 'Ditolak', follow_up: 'Perlu tindak lanjut' }

async function loadData() {
  loading.value = true
  error.value = ''
  try {
    const headers = { Authorization: `Bearer ${sessionStorage.getItem('tb_token')}`, Accept: 'application/json' }
    const base = import.meta.env.VITE_API_BASE_URL ?? '/api/v1'
    
    const [resReports, resUnreported] = await Promise.all([
      fetch(`${base}/medication-monitoring`, { headers }),
      fetch(`${base}/medication-monitoring/unreported`, { headers })
    ])
    
    if (!resReports.ok) throw new Error('Gagal memuat laporan')
    if (!resUnreported.ok) throw new Error('Gagal memuat data belum lapor')

    const pReports = await resReports.json()
    const pUnreported = await resUnreported.json()
    
    reports.value = pReports.data
    unreported.value = pUnreported.data
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Terjadi kesalahan.'
  } finally {
    loading.value = false
  }
}

async function openReport(report: Report) {
  selectedReport.value = report
  reportPhoto.value = null
  verificationStatus.value = report.status === 'pending' ? 'verified' : report.status
  verificationNote.value = report.verification_note ?? ''
  
  try {
    const res = await fetch(`/api/v1/medication-monitoring/${report.id}/photo`, {
      headers: { Authorization: `Bearer ${sessionStorage.getItem('tb_token')}` }
    })
    if (res.ok) {
      const blob = await res.blob()
      reportPhoto.value = URL.createObjectURL(blob)
    }
  } catch (err) {
    // Abaikan gagal muat foto
  }
}

function closeReport() {
  selectedReport.value = null
  if (reportPhoto.value) URL.revokeObjectURL(reportPhoto.value)
  reportPhoto.value = null
}

async function verifyReport() {
  if (!selectedReport.value || verifying.value) return
  if (verificationStatus.value === 'rejected' && !verificationNote.value.trim()) {
    alert('Catatan wajib diisi bila laporan ditolak.')
    return
  }
  
  verifying.value = true
  try {
    const res = await fetch(`/api/v1/medication-monitoring/${selectedReport.value.id}/verify`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${sessionStorage.getItem('tb_token')}`,
        'Content-Type': 'application/json',
        Accept: 'application/json'
      },
      body: JSON.stringify({
        status: verificationStatus.value,
        verification_note: verificationNote.value
      })
    })
    
    const payload = await res.json()
    if (!res.ok) throw new Error(payload.message)
    
    selectedReport.value.status = payload.data.status
    selectedReport.value.verification_note = payload.data.verification_note
    const idx = reports.value.findIndex(r => r.id === selectedReport.value!.id)
    if (idx !== -1) reports.value[idx] = selectedReport.value
    
    closeReport()
  } catch (err) {
    alert(err instanceof Error ? err.message : 'Gagal verifikasi')
  } finally {
    verifying.value = false
  }
}

onMounted(loadData)
</script>

<template>
  <main class="admin-page">
    <section class="admin-content">
      <header>
        <div>
          <p>Pemantauan</p>
          <h1>Monitoring Pasien</h1>
        </div>
      </header>
      
      <div class="tabs">
        <button :class="{ active: activeTab === 'reported' }" @click="activeTab = 'reported'">
          Pasien Sudah Minum Obat
          <span class="badge">{{ reports.length }}</span>
        </button>
        <button :class="{ active: activeTab === 'unreported' }" @click="activeTab = 'unreported'">
          Pasien Belum Lapor
          <span class="badge unreported-badge">{{ unreported.length }}</span>
        </button>
      </div>

      <p v-if="loading">Memuat data...</p>
      <p v-else-if="error" class="login-error">{{ error }}</p>

      <section v-else-if="activeTab === 'reported'" class="panel table-container">
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Pasien</th>
                <th>Waktu Lapor</th>
                <th>Status Obat</th>
                <th>Efek Samping</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in reports" :key="row.id" @click="openReport(row)" class="clickable-row">
                <td><b>{{ row.patient.full_name }}</b></td>
                <td>{{ formatTime(row.server_received_at) }}</td>
                <td>{{ row.medication_taken ? 'Diminum' : 'Tidak diminum' }}</td>
                <td>
                  <span v-if="row.has_side_effect" class="badge-danger">Ada Keluhan</span>
                  <span v-else class="text-muted">Tidak ada</span>
                </td>
                <td>
                  <span :class="['status-pill', row.status]">
                    {{ statusLabels[row.status] || row.status }}
                  </span>
                </td>
              </tr>
              <tr v-if="!reports.length">
                <td colspan="5" class="data-message">Belum ada pasien yang melaporkan minum obat hari ini.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else-if="activeTab === 'unreported'" class="panel table-container">
        <div class="table-scroll">
          <table>
            <thead>
              <tr>
                <th>Pasien</th>
                <th>No. Telepon</th>
                <th>Kader Pendamping</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in unreported" :key="row.id">
                <td><b>{{ row.full_name }}</b></td>
                <td>{{ row.phone || '—' }}</td>
                <td>{{ row.cadre?.full_name || '—' }}</td>
              </tr>
              <tr v-if="!unreported.length">
                <td colspan="3" class="data-message">Semua pasien aktif sudah melapor hari ini.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </section>

    <!-- Modal Detail Laporan -->
    <div v-if="selectedReport" class="modal-overlay" @click.self="closeReport">
      <div class="modal-content report-detail-modal">
        <button class="modal-close" @click="closeReport">×</button>
        <h2>Detail Laporan Minum Obat</h2>
        
        <div class="report-split">
          <div class="report-info">
            <dl>
              <dt>Nama Pasien</dt><dd><b>{{ selectedReport.patient.full_name }}</b></dd>
              <dt>Waktu Lapor</dt><dd>{{ formatFullDate(selectedReport.server_received_at) }}</dd>
              <dt>Lokasi (GPS)</dt><dd>{{ selectedReport.formatted_address || 'Lokasi tidak tersedia' }}</dd>
              <dt>Status Obat</dt>
              <dd>
                <span v-if="selectedReport.medication_taken" class="status-pill verified">Obat diminum</span>
                <span v-else class="status-pill rejected">Tidak diminum</span>
              </dd>
              <dt>Efek Samping</dt>
              <dd>
                <span v-if="selectedReport.has_side_effect" class="badge-danger">Ada Keluhan</span>
                <span v-else>Tidak ada keluhan</span>
              </dd>
            </dl>

            <form @submit.prevent="verifyReport" class="verification-form">
              <h3>Verifikasi Laporan</h3>
              <div class="form-group">
                <label>Status Verifikasi</label>
                <select v-model="verificationStatus">
                  <option value="verified">Terima (Valid)</option>
                  <option value="rejected">Tolak (Ulangi Foto)</option>
                  <option value="follow_up">Perlu Tindak Lanjut</option>
                </select>
              </div>
              <div class="form-group" v-if="verificationStatus === 'rejected' || verificationStatus === 'follow_up'">
                <label>Catatan / Alasan</label>
                <textarea v-model="verificationNote" rows="3" placeholder="Tambahkan alasan penolakan..."></textarea>
              </div>
              <button type="submit" class="primary" :disabled="verifying">
                {{ verifying ? 'Menyimpan...' : 'Simpan Verifikasi' }}
              </button>
            </form>
          </div>
          <div class="report-photo">
            <h3>Foto Bukti</h3>
            <img v-if="reportPhoto" :src="reportPhoto" alt="Foto bukti lapor" />
            <div v-else class="photo-placeholder">Memuat foto...</div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<style scoped>
.tabs {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  border-bottom: 1px solid #dcefeb;
  padding-bottom: 10px;
}
.tabs button {
  background: none;
  border: none;
  font-size: 15px;
  font-weight: 600;
  color: #6a8992;
  cursor: pointer;
  padding: 8px 16px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: .2s;
}
.tabs button:hover { background: #f0faf8; }
.tabs button.active {
  color: #0b9385;
  background: #e1faf6;
}
.badge {
  background: #0b9385;
  color: white;
  padding: 2px 8px;
  border-radius: 20px;
  font-size: 12px;
}
.unreported-badge { background: #f4c437; color: #1a4959; }

.clickable-row { cursor: pointer; transition: background .2s; }
.clickable-row:hover { background: #f9fdfc; }

.status-pill {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
}
.status-pill.pending { background: #fff4d5; color: #b08200; }
.status-pill.verified { background: #e1faf6; color: #0b9385; }
.status-pill.rejected { background: #ffebeb; color: #c92a2a; }
.status-pill.follow_up { background: #f0f4ff; color: #3b5bdb; }

.badge-danger {
  background: #ffebeb;
  color: #c92a2a;
  padding: 2px 8px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: bold;
}
.text-muted { color: #8ba6ad; font-size: 12px; }

.report-detail-modal {
  width: min(900px, 95vw);
  max-height: 90vh;
  overflow-y: auto;
}
.report-split {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
  margin-top: 20px;
}
.report-info dl {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 12px;
  margin-bottom: 24px;
}
.report-info dt { color: #6a8992; font-size: 13px; }
.report-info dd { margin: 0; color: #1a4959; font-size: 14px; }

.verification-form {
  background: #f9fdfc;
  padding: 20px;
  border: 1px solid #dcefeb;
  border-radius: 12px;
}
.verification-form h3 { margin: 0 0 16px; font-size: 15px; color: #1a4959; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: bold; color: #3e5e6e; }
.form-group select, .form-group textarea {
  width: 100%;
  padding: 10px;
  border: 1px solid #dcefeb;
  border-radius: 8px;
  font-family: inherit;
}
.form-group textarea { resize: vertical; }
.primary { width: 100%; }

.report-photo {
  background: #f2f7f7;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
}
.report-photo h3 { margin: 0 0 12px; font-size: 14px; color: #1a4959; }
.report-photo img {
  max-width: 100%;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.photo-placeholder {
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #8ba6ad;
  border: 2px dashed #dcefeb;
  border-radius: 8px;
}

@media(max-width: 768px) {
  .report-split { grid-template-columns: 1fr; }
  .tabs button { flex: 1; justify-content: center; font-size: 13px; padding: 12px; }
}
</style>