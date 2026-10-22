<script setup>
const props = defineProps({ cfg: { type: Object, required: true } })
const api = useApi()

const NAME_RE = /^[A-Za-z0-9_]+$/
const NAME_HINT = 'Letters, numbers and underscore only. No spaces or symbols.'
const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

const rows = ref([])
const loading = ref(true)
const saving = ref(false)
const editingId = ref(null)
const msg = ref({ type: '', text: '' })

const blank = () => {
  const f = {}
  props.cfg.fields.forEach((x) => { f[x.key] = '' })
  if (props.cfg.hasActive) f.is_active = true
  return f
}
const form = reactive(blank())

const colspan = computed(() => props.cfg.columns.length + (props.cfg.hasActive ? 1 : 0) + 1)
const badName = (f) => f.isName && form[f.key] !== '' && !NAME_RE.test(form[f.key])
const say = (type, text) => { msg.value = { type, text } }

async function load() {
  if (!rows.value.length) loading.value = true
  try {
    const res = await api.get('/' + props.cfg.resource)
    rows.value = res.data
  } catch (e) {
    say('err', errMsg(e, 'Could not load data. Is Laravel running?'))
  }
  loading.value = false
}

function resetForm() {
  Object.assign(form, blank())
  editingId.value = null
}
function onReset() {
  resetForm()
  msg.value = { type: '', text: '' }
}

function validate() {
  for (const f of props.cfg.fields) {
    const v = String(form[f.key] ?? '').trim()
    if (f.required && v === '') return `${f.label} is required.`
    if (f.isName && !NAME_RE.test(v)) return `${f.label}: ${NAME_HINT}`
    if (f.type === 'email' && !EMAIL_RE.test(v)) return 'A valid Email Address is required.'
  }
  return ''
}

async function save() {
  if (saving.value) return
  const problem = validate()
  if (problem) return say('err', problem)

  saving.value = true
  const payload = {}
  props.cfg.fields.forEach((f) => { payload[f.key] = String(form[f.key]).trim() })
  if (props.cfg.hasActive) payload.is_active = form.is_active ? 1 : 0

  try {
    const res = editingId.value
      ? await api.put(`/${props.cfg.resource}/${editingId.value}`, payload)
      : await api.post('/' + props.cfg.resource, payload)
    say('ok', res.message)
    resetForm()
    await load()
  } catch (e) {
    say('err', errMsg(e)) // form values are kept
  }
  saving.value = false
}

function edit(row) {
  props.cfg.fields.forEach((f) => { form[f.key] = row[f.key] ?? '' })
  if (props.cfg.hasActive) form.is_active = row.is_active == 1
  editingId.value = row.id
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function remove(row) {
  if (!confirm(`Delete ${props.cfg.label} "${row.name}"?`)) return
  try {
    const res = await api.del(`/${props.cfg.resource}/${row.id}`)
    say('ok', res.message)
    if (editingId.value === row.id) resetForm()
    await load()
  } catch (e) {
    say('err', errMsg(e))
  }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="card">
      <h2>{{ editingId ? 'Edit' : 'Add' }} {{ cfg.label }}</h2>
      <p class="hint">{{ cfg.hint }}</p>
      <div v-if="msg.text" class="msg" :class="msg.type">
        {{ msg.type === 'ok' ? '✓' : '⚠' }} {{ msg.text }}
      </div>

      <form novalidate @submit.prevent="save">
        <div class="row">
          <div v-for="f in cfg.fields" :key="f.key" :class="{ full: f.type === 'textarea' }">
            <label>{{ f.label }}<span v-if="f.required"> *</span></label>
            <textarea v-if="f.type === 'textarea'" v-model="form[f.key]" maxlength="255" />
            <input
              v-else
              v-model="form[f.key]"
              :type="f.type || 'text'"
              :maxlength="f.maxlength"
              :class="{ invalid: badName(f) }"
            />
            <div v-if="f.isName" class="field-hint">{{ badName(f) ? '✗ ' : '' }}{{ NAME_HINT }}</div>
          </div>
        </div>

        <div v-if="cfg.hasActive" class="checkrow">
          <input id="active" v-model="form.is_active" type="checkbox" />
          <label for="active" style="margin: 0">Active</label>
        </div>

        <div class="actions">
          <button type="submit" class="btn primary" :disabled="saving">
            {{ saving ? 'Saving...' : editingId ? 'Update ' + cfg.label : 'Save ' + cfg.label }}
          </button>
          <button type="button" class="btn secondary" @click="onReset">Reset</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>Saved {{ cfg.label }} Records</h2>
      <div class="scroll">
        <table>
          <thead>
            <tr>
              <th v-for="c in cfg.columns" :key="c.key">{{ c.label }}</th>
              <th v-if="cfg.hasActive">Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td :colspan="colspan" class="empty">Loading...</td></tr>
            <tr v-else-if="!rows.length"><td :colspan="colspan" class="empty">No records yet.</td></tr>
            <tr v-for="row in rows" :key="row.id">
              <td v-for="c in cfg.columns" :key="c.key">{{ row[c.key] }}</td>
              <td v-if="cfg.hasActive">
                <span class="status" :class="row.is_active == 1 ? 'active' : 'inactive'">
                  {{ row.is_active == 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td>
                <button type="button" class="btn-sm btn-edit" @click="edit(row)">Edit</button>
                <button type="button" class="btn-sm btn-del" @click="remove(row)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>