<script setup>
definePageMeta({ title: 'Items', subtitle: 'Manage your item master records' })

const api = useApi()

const NAME_RE = /^[A-Za-z0-9_]+$/
const NAME_HINT = 'Letters, numbers and underscore only. No spaces or symbols.'

const items = ref([])
const categories = ref([])
const brands = ref([])
const uoms = ref([])
const loading = ref(true)
const saving = ref(false)
const editingId = ref(null)
const msg = ref({ type: '', text: '' })

const blank = () => ({ item_name: '', category_id: '', brand_id: '', uom_id: '', is_active: true })
const form = reactive(blank())

const say = (type, text) => { msg.value = { type, text } }
const badName = computed(() => form.item_name !== '' && !NAME_RE.test(form.item_name))

// Only active categories are offered (the item's current one stays visible while editing)
const categoryOptions = computed(() =>
  categories.value.filter((c) => c.is_active == 1 || c.id == form.category_id)
)

async function load() {
  if (!items.value.length) loading.value = true
  try {
    const [it, cat, br, uo] = await Promise.all([
      api.get('/items'), api.get('/categories'), api.get('/brands'), api.get('/uoms'),
    ])
    items.value = it.data
    categories.value = cat.data
    brands.value = br.data
    uoms.value = uo.data
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
  const name = form.item_name.trim()
  if (!name) return 'Item Name is required.'
  if (!NAME_RE.test(name)) return `Item Name: ${NAME_HINT}`
  if (!form.category_id) return 'Please select a Category.'
  if (!form.brand_id) return 'Please select a Brand.'
  if (!form.uom_id) return 'Please select a UOM.'
  return ''
}

async function save() {
  if (saving.value) return
  const problem = validate()
  if (problem) return say('err', problem)

  saving.value = true
  const payload = {
    item_name: form.item_name.trim(),
    category_id: form.category_id,
    brand_id: form.brand_id,
    uom_id: form.uom_id,
    is_active: form.is_active ? 1 : 0,
  }
  try {
    const res = editingId.value
      ? await api.put(`/items/${editingId.value}`, payload)
      : await api.post('/items', payload)
    say('ok', res.message)
    resetForm()
    await load()
  } catch (e) {
    say('err', errMsg(e)) // form values are kept
  }
  saving.value = false
}

function edit(row) {
  form.item_name = row.item_name
  form.category_id = row.category_id
  form.brand_id = row.brand_id
  form.uom_id = row.uom_id
  form.is_active = row.is_active == 1
  editingId.value = row.id
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function remove(row) {
  if (!confirm(`Delete item "${row.item_code}"?`)) return
  try {
    const res = await api.del(`/items/${row.id}`)
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
      <h2>{{ editingId ? 'Edit Item' : 'Add Item' }}</h2>
      <p class="hint">Item code is generated automatically. Only active Category records appear here.</p>
      <div v-if="msg.text" class="msg" :class="msg.type">
        {{ msg.type === 'ok' ? '✓' : '⚠' }} {{ msg.text }}
      </div>

      <form novalidate @submit.prevent="save">
        <div class="row">
          <div>
            <label>Item Name *</label>
            <input v-model="form.item_name" maxlength="150" :class="{ invalid: badName }" />
            <div class="field-hint">{{ badName ? '✗ ' : '' }}{{ NAME_HINT }}</div>
          </div>
          <div>
            <label>Item Category *</label>
            <select v-model="form.category_id">
              <option value="">Select Category</option>
              <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label>Brand *</label>
            <select v-model="form.brand_id">
              <option value="">Select Brand</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
          <div>
            <label>UOM *</label>
            <select v-model="form.uom_id">
              <option value="">Select UOM</option>
              <option v-for="u in uoms" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
        </div>

        <div class="checkrow">
          <input id="item-active" v-model="form.is_active" type="checkbox" />
          <label for="item-active" style="margin: 0">Active</label>
        </div>

        <div class="actions">
          <button type="submit" class="btn primary" :disabled="saving">
            {{ saving ? 'Saving...' : editingId ? 'Update Item' : 'Save Item & Generate Code' }}
          </button>
          <button type="button" class="btn secondary" @click="onReset">Reset</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>Saved Items</h2>
      <p class="hint">Sorted by Item Code in ascending order.</p>
      <div class="scroll">
        <table>
          <thead>
            <tr>
              <th>Item Code</th><th>Item Name</th><th>Category</th><th>Brand</th>
              <th>UOM</th><th>Status</th><th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="7" class="empty">Loading...</td></tr>
            <tr v-else-if="!items.length"><td colspan="7" class="empty">No records yet.</td></tr>
            <tr v-for="row in items" :key="row.id">
              <td><code class="badge">{{ row.item_code }}</code></td>
              <td>{{ row.item_name }}</td>
              <td>{{ row.category_name }}</td>
              <td>{{ row.brand_name }}</td>
              <td>{{ row.uom_name }}</td>
              <td>
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