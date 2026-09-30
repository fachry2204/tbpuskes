const { test } = require('node:test')
const assert = require('node:assert/strict')
const fs = require('node:fs')
const { parse, compileScript, compileTemplate } = require('@vue/compiler-sfc')

const source = fs.readFileSync('src/RoleDashboard.vue', 'utf8')
const { descriptor } = parse(source)

test('patient dashboard compiles after informative report-state changes', () => {
  assert.doesNotThrow(() => compileScript(descriptor, { id: 'patient-dashboard-state' }))
  const result = compileTemplate({ source: descriptor.template.content, filename: 'RoleDashboard.vue', id: 'patient-dashboard-state' })
  assert.deepEqual(result.errors, [])
})

test('reported-today card is informative and cannot submit duplicate report', () => {
  assert.match(source, /const hasReportedMedicationToday = computed/)
  assert.match(source, /Anda Sudah Lapor Minum Obat Hari Ini/)
  assert.match(source, /Terima kasih sudah melaporkan minum obat hari ini/)
  assert.match(source, /const nextMedicationDateLabel = computed/)
  assert.match(source, /nextMedicationDateLabel} · \$\{nextMedicationTime\}/)
  assert.match(source, /v-if="!hasReportedMedicationToday"[^>]*@click="nextMedication\?openMedicationReport/)
})

test('history uses safe dates and Indonesian status labels', () => {
  assert.match(source, /function formatDate\(value\?:string\|null\)/)
  assert.match(source, /statusLabels:Record<string,string>/)
  assert.match(source, /statusLabel\(item\.status\)/)
  assert.doesNotMatch(source, /new Date\(`\$\{value\}T00:00:00`\)/)
  assert.match(source, /<b>{{formatDate\(item\.report_date\)}}<\/b>/)
})

test('side effects panel uses safe date fallback and translated status', () => {
  assert.match(source, /Status {{statusLabel\(item\.status\)}}/)
  assert.doesNotMatch(source, /Status {{item\.status}}/)
  assert.match(source, /function formatDate\(value\?:string\|null\).*Tanggal tidak tersedia/)
  assert.match(source, /item\.side_effect_category/)
  assert.match(source, /item\.side_effect_description/)
  assert.match(source, /patient-side-effect-reason/)
})

test('reported medication history opens side effect modal with add or edit state', () => {
  assert.match(source, /function openHistorySideEffect\(item:PatientReport\)/)
  assert.match(source, /historySideEffectDialog/)
  assert.match(source, /item\.has_side_effect\?'Ubah Efek Samping':'\+ Tambah Efek Samping'/)
  assert.match(source, /historySideEffectCategory\.value=item\.side_effect_category/)
  assert.match(source, /historySideEffectDescription\.value=item\.side_effect_description/)
  assert.match(source, /role="dialog"[^>]*aria-modal="true"[^>]*aria-label="Form efek samping"/)
  assert.match(source, /\/me\/medication\/reports\/\$\{sideEffectReportId\.value\}\/side-effect/)
  assert.match(source, /submitHistorySideEffect/)
})

test('patient dashboard uses patient uploaded photo, never cadre photo', () => {
  assert.match(source, /const patientPhoto = computed\(\(\) => patient\.value\.photo_path/)
  assert.match(source, /class="patient-avatar-photo"[^>]*:src="patientPhoto"[^>]*alt="Foto pasien"/)
  assert.match(source, /class="patient-welcome-figure patient-welcome-photo"[^>]*:src="patientPhoto \|\| patientHero"/)
  assert.match(source, /class="patient-profile-photo"[^>]*:src="patientPhoto"[^>]*alt="Foto pasien"/)
  assert.match(source, /v-else>{{ initials }}<\/template>/)
})

test('cadre dashboard uses uploaded cadre photo in header and hero', () => {
  assert.match(source, /const cadrePhoto = computed\(\(\) => data\.value\.cadre\?\.photo_path/)
  assert.match(source, /class="kader-avatar-photo"[^>]*:src="cadrePhoto"[^>]*alt="Foto kader"/)
  assert.match(source, /class="kader-hero-photo"[^>]*:src="cadrePhoto"[^>]*alt="Foto profil kader"/)
  assert.doesNotMatch(source, /Tetap semangat mendampingi pasien TB di wilayah kita/)
})

test('patient and cadre dashboards use readable typography', () => {
  assert.match(source, /\.kader-reference,\.patient-dashboard\{font-size:15px/)
  assert.match(source, /:is\(p,small,span,time,em,a,button,label,input,select,textarea\)[^}]*font-size:13px/)
  assert.match(source, /:is\(b,strong\)[^}]*font-size:14px/)
  assert.match(source, /\.kader-menu-item[^}]*\.patient-bottom-nav button\{font-size:12px/)
})

test('cadre monitoring shows dated status page with filters and patient detail popup', () => {
  assert.match(source, /\/kader\/medication-reports\/daily\?date=/)
  assert.match(source, /panel==='monitoring'/)
  assert.match(source, /type="date"[^>]*v-model="cadreReportDate"/)
  assert.match(source, /cadreMonitoringFilter/)
  assert.match(source, /filteredCadreDailyPatients/)
  assert.match(source, /Sudah Minum/)
  assert.match(source, /Tidak Minum/)
  assert.match(source, /Belum Lapor/)
  assert.match(source, /openCadrePatientDetail\(item\)/)
  assert.match(source, /v-if="selectedCadreDailyPatient"[^>]*class="cadre-patient-detail-backdrop"/)
  assert.match(source, /role="dialog"[^>]*aria-modal="true"[^>]*aria-label="Detail monitoring pasien"/)
  assert.doesNotMatch(source, /Pantau laporan minum obat pasien binaan untuk hari ini/)
})
