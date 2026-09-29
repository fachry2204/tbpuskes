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
})

test('cadre uploaded photo fills all highlighted patient-dashboard images', () => {
  assert.match(source, /const cadrePhoto = computed/)
  assert.match(source, /class="patient-avatar-photo"[^>]*:src="cadrePhoto"/)
  assert.match(source, /class="patient-welcome-figure patient-welcome-photo"[^>]*:src="cadrePhoto \|\| patientHero"/)
  assert.match(source, /class="patient-profile-photo"[^>]*:src="cadrePhoto"/)
  assert.match(source, /v-else>{{ initials }}<\/template>/)
})
