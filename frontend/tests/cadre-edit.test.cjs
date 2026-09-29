const { test } = require('node:test')
const assert = require('node:assert/strict')
const fs = require('node:fs')
const vm = require('node:vm')
const { parse, compileScript } = require('@vue/compiler-sfc')
const ts = require('typescript')
const vue = require('vue')

function loadForm(fetch, cadreId = 7) {
  const source = fs.readFileSync('src/AdminCadreCreate.vue', 'utf8')
  const { descriptor } = parse(source)
  const script = compileScript(descriptor, { id: 'patient-test' }).content.replaceAll('import.meta.env.VITE_API_BASE_URL', "'/api/v1'")
  const code = ts.transpileModule(script, { compilerOptions: { module: ts.ModuleKind.CommonJS } }).outputText
  const hooks = []
  const exports = {}
  vm.runInNewContext(code, { exports, require: () => ({ ...vue, onMounted: fn => hooks.push(fn) }), fetch, FormData, sessionStorage: { getItem: () => 'test' }, URL })
  const state = exports.default.setup({ cadreId: cadreId === null ? undefined : cadreId }, { expose() {}, emit() {} })
  return { state, hooks, template: descriptor.template.content }
}

test('cadre edit uses creation form with existing date, photo and optional password', async () => {
  let body
  const { state, hooks } = loadForm(async (_url, options) => {
    if (options?.method === 'POST') body=options.body
    return { ok:true, json:async()=>({data:{full_name:'Test',birth_date:'1990-12-24T00:00:00Z',photo_path:'cadres/test.jpg',working_area:'Area',is_active:false}}) }
  })
  await Promise.all(hooks.map(fn=>fn()))
  assert.equal(state.form.value.birth_date,'24/12/1990')
  assert.equal(state.form.value.password,'')
  assert.equal(state.photoPreview.value,'/media/profile/cadres/test.jpg')
  await state.submit()
  assert.equal(body.get('_method'),'PUT')
  assert.equal(body.get('is_active'),'0')
  assert.equal(body.get('password'),null)
  assert.equal(body.get('working_area'),'Area')
  assert.match(fs.readFileSync('src/AdminCadres.vue','utf8'), /<AdminCadreCreate[^>]*:cadre-id="selected.id"/)
})
