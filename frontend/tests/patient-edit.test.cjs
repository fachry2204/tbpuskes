const { test } = require('node:test')
const assert = require('node:assert/strict')
const fs = require('node:fs')
const vm = require('node:vm')
const { parse, compileScript } = require('@vue/compiler-sfc')
const ts = require('typescript')
const vue = require('vue')

function loadForm(fetch, patientId = 7) {
  const source = fs.readFileSync('src/AdminPatientCreate.vue', 'utf8')
  const { descriptor } = parse(source)
  const script = compileScript(descriptor, { id: 'patient-test' }).content.replaceAll('import.meta.env.VITE_API_BASE_URL', "'/api/v1'")
  const code = ts.transpileModule(script, { compilerOptions: { module: ts.ModuleKind.CommonJS } }).outputText
  const hooks = []
  const exports = {}
  vm.runInNewContext(code, { exports, require: () => ({ ...vue, onMounted: fn => hooks.push(fn) }), fetch, FormData, sessionStorage: { getItem: () => 'test' }, URL })
  const state = exports.default.setup({ patientId: patientId === null ? undefined : patientId }, { expose() {}, emit() {} })
  return { state, hooks, template: descriptor.template.content }
}

test('edit patient loads dates and submits existing data through shared creation form', async () => {
  let submitted
  const { state, hooks, template } = loadForm(async (url, options) => {
    if (options?.method === 'POST') { submitted = options.body; return { ok: true, json: async () => ({ data: { id: 7 } }) } }
    const data = url.endsWith('/patients/7') ? {
      id: 7, full_name: 'Test', nik: '1234567890123456', birth_date: '1990-12-24T00:00:00.000000Z',
      treatment_start_date: '2026-01-15T00:00:00.000000Z', treatment_place_id: 2, cadre_id: 3,
      phone: '6281234567890', gender: 'P', rt: '001', rw: '002', full_address: 'Test',
      tb_diagnosis: 'TB Paru', diagnosis_type: 'Klinis', daily_dose_frequency: 1, status: 'active', photo_path: 'patients/test.jpg',
    } : url.includes('/treatment-places') ? [{ id: 2, name: 'Puskesmas Melati' }] : []
    return { ok: true, json: async () => ({ data }) }
  })
  await Promise.all(hooks.map(fn => fn()))
  assert.equal(state.form.value.birth_date, '24/12/1990')
  assert.equal(state.form.value.treatment_start_date, '2026-01-15')
  assert.equal(state.form.value.password, '')
  assert.equal(state.photoPreview.value, '/media/profile/patients/test.jpg')
  await state.submit()
  assert.equal(submitted.get('_method'), 'PUT')
  assert.equal(submitted.get('birth_date'), '24/12/1990')
  assert.equal(submitted.get('treatment_start_date'), '2026-01-15')
  assert.equal(submitted.get('password'), null)
  assert.equal(submitted.get('birth_place'), null)
  assert.ok(template.includes('form.cadre_id'))
  assert.ok(template.includes('form.treatment_place_id'))
  assert.match(template, /<option v-for="p in places"[^>]*>{{p.name}}<\/option>/)
  assert.equal(state.places.value.find(p => p.id === state.form.value.treatment_place_id)?.name, 'Puskesmas Melati')
  const list = fs.readFileSync('src/AdminPatients.vue', 'utf8')
  assert.ok(!list.includes('v-model="selected.treatment_place_id" type="number"'))
  assert.match(list, /<dt>Tempat pengobatan<\/dt><dd>{{treatmentPlaceName/)
  assert.match(list, /fetch\(`\$\{base\}\/treatment-places\/\$\{p.treatment_place_id\}`/)
  assert.match(list, /<AdminPatientCreate[^>]*:patient-id="selected.id"/)
  assert.ok(!list.includes('v-model="selected.birth_date"'))
  assert.ok(!template.includes('birth_place'))
})


test('failed patient loading prevents saving an empty edit form', async () => {
  let writes = 0
  const { state, hooks } = loadForm(async (_url, options) => {
    if (options?.method === 'POST') writes++
    return { ok: false, json: async () => ({ message: 'Tidak ditemukan' }) }
  })
  await Promise.all(hooks.map(fn => fn()))
  await state.submit()
  assert.equal(writes, 0)
  assert.equal(state.loadFailed.value, true)
})

test('patient creation keeps create endpoint and password', async () => {
  let submitted, endpoint
  const { state, hooks } = loadForm(async (url, options) => {
    if (options?.method === 'POST') { endpoint = url; submitted = options.body }
    return { ok: true, json: async () => ({ data: [] }) }
  }, null)
  await Promise.all(hooks.map(fn => fn()))
  await state.submit()
  assert.equal(endpoint, '/api/v1/patients')
  assert.equal(submitted.get('_method'), null)
  assert.equal(submitted.get('password'), '12345678')
  assert.equal(submitted.get('status'), null)
})
