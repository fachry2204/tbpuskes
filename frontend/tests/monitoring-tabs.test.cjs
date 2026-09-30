const test = require('node:test')
const assert = require('node:assert/strict')
const fs = require('node:fs')
const path = require('node:path')

test('admin monitoring page displays tabs for reported and unreported patients', () => {
  const vueFile = fs.readFileSync(path.join(__dirname, '../src/AdminMonitoring.vue'), 'utf8')
  
  // Memastikan ada dua tab
  assert.match(vueFile, /Pasien Sudah Minum Obat/)
  assert.match(vueFile, /Pasien Belum Lapor/)
  
  // Memastikan modal detail laporan dapat dibuka
  assert.match(vueFile, /openReport\(row\)/)
  assert.match(vueFile, /Detail Laporan Minum Obat/)
  assert.match(vueFile, /verifyReport/)

  // Memastikan filter tanggal dan kolom tanggal lapor serta nomor WA
  assert.match(vueFile, /type="date"/)
  assert.match(vueFile, /Tanggal Lapor/)
  assert.match(vueFile, /wa\.me/)
  assert.match(vueFile, /modal-overlay/)
  assert.match(vueFile, /side_effect_description/)
  assert.match(vueFile, /deleteReport/)
  assert.match(vueFile, /Edit Data/)
  assert.match(vueFile, /Hapus/)
})
