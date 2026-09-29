const { test } = require('node:test')
const assert = require('node:assert/strict')
const fs = require('node:fs')
const vm = require('node:vm')
const { parse, compileScript } = require('@vue/compiler-sfc')
const ts = require('typescript')
const vue = require('vue')

function loadSchedule(fetch, confirm = () => true) {
  const { descriptor } = parse(fs.readFileSync('src/AdminSchedules.vue', 'utf8'))
  const script = compileScript(descriptor, { id: 'schedule-actions' }).content.replaceAll('import.meta.env.VITE_API_BASE_URL', "'/api/v1'")
  const code = ts.transpileModule(script, { compilerOptions: { module: ts.ModuleKind.CommonJS } }).outputText
  const hooks = [], exports = {}
  vm.runInNewContext(code, { exports, require: () => ({ ...vue, onMounted: fn => hooks.push(fn) }), fetch, confirm, sessionStorage: { getItem: () => 'test' } })
  return { state: exports.default.setup({}, { expose() {} }), hooks, template: descriptor.template.content }
}

const schedule = { id: 8, patient_id: 1, full_name: 'Pasien', treatment_place_id: 2, treatment_place: 'Puskesmas Melati', control_date: '2026-10-08', start_time: '10:00:00', purpose: 'Kontrol', notes: 'Catatan', status: 'scheduled' }

test('schedule edit displays place name and sends updated visit fields', async () => {
  let method, body
  const { state, hooks, template } = loadSchedule(async (url, options) => {
    if (options?.method === 'PUT') { method = options.method; body = JSON.parse(options.body); return { ok: true, json: async () => ({ data: schedule }) } }
    if (url.includes('/treatment-places')) return { ok: true, json: async () => ({ data: [{ id: 2, name: 'Puskesmas Melati' }] }) }
    return { ok: true, json: async () => ({ data: [schedule], meta: { current_page: 1, last_page: 1, total: 1 } }) }
  })
  await Promise.all(hooks.map(fn => fn()))
  await state.edit(schedule)
  assert.equal(state.form.value.treatment_place_id, '2')
  assert.equal(state.form.value.start_time, '10:00')
  assert.equal(state.places.value[0].name, 'Puskesmas Melati')
  state.form.value.purpose = 'Kontrol lanjutan'
  await state.saveEdit()
  assert.equal(method, 'PUT')
  assert.equal(body.purpose, 'Kontrol lanjutan')
  assert.equal(body.treatment_place_id, 2)
  assert.match(template, /<th>Aksi<\/th>/)
  assert.match(template, /<option v-for="place in places"[^>]*>{{place.name}}<\/option>/)
})

test('schedule deletion needs confirmation and refreshes list', async () => {
  let deleted = 0, refreshed = 0, allow = false
  const { state } = loadSchedule(async (url, options) => {
    if (options?.method === 'DELETE') { deleted++; return { ok: true, json: async () => ({}) } }
    if (url.includes('/control-schedules')) refreshed++
    return { ok: true, json: async () => ({ data: [], meta: { current_page: 1, last_page: 1, total: 0 } }) }
  }, () => allow)
  await state.remove(schedule)
  assert.equal(deleted, 0)
  allow = true
  await state.remove(schedule)
  assert.equal(deleted, 1)
  assert.equal(refreshed, 1)
})
