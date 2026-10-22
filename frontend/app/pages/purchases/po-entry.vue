<script setup>
definePageMeta({ title: 'Purchase Order Entry', subtitle: 'Create a new purchase order' })

const api = useApi()

// Today's date in local time, as YYYY-MM-DD
const today = () => {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}

let uid = 0
const newRow = () => ({ uid: ++uid, item_id: '', quantity: '', rate: '' })

const suppliers = ref([])
const locations = ref([])
const items = ref([])
const rows = ref([newRow()])
const saving = ref(false)
const msg = ref({ type: '', text: '' })

const form = reactive({ supplier_id: '', reference: '', order_date: today(), location_id: '', deliver_to: '' })

const say = (type, text) => { msg.value = { type, text } }
const itemOf = (r) => items.value.find((i) => i.id == r.item_id)
const amount = (r) => (Number(r.quantity) || 0) * (Number(r.rate) || 0)
const total = computed(() => rows.value.reduce((sum, r) => sum + amount(r), 0))

onMounted(async () => {
  try {
    const [s, l, i] = await Promise.all([api.get('/suppliers'), api.get('/locations'), api.get('/items')])
    suppliers.value = s.data.filter((x) => x.is_active == 1)
    locations.value = l.data.filter((x) => x.is_active == 1)
    items.value = i.data.filter((x) => x.is_active == 1)
  } catch (e) {
    say('err', errMsg(e, 'Could not load data. Is Laravel running?'))
  }
})

// Picking a location fills the delivery address (still editable)
watch(() => form.location_id, (id) => {
  const loc = locations.value.find((l) => l.id == id)
  form.deliver_to = loc ? loc.address : ''
})

// Always keep one blank row at the bottom
watch(rows, () => {
  const last = rows.value[rows.value.length - 1]
  if (!last || last.item_id) rows.value.push(newRow())
}, { deep: true })

function removeRow(i) {
  rows.value.splice(i, 1)
}

function resetForm() {
  form.supplier_id = ''
  form.reference = ''
  form.order_date = today()
  form.location_id = ''
  form.deliver_to = ''
  rows.value = [newRow()]
}
function onReset() {
  resetForm()
  msg.value = { type: '', text: '' }
}

function validate() {
  if (!form.supplier_id) return 'Please select a Supplier.'
  if (!form.order_date) return 'Order Date is required.'
  if (!form.location_id) return 'Please select a Location.'
  if (!form.deliver_to.trim()) return 'Deliver To is required.'

  const filled = rows.value.filter((r) => r.item_id || r.quantity !== '' || r.rate !== '')
  if (!filled.length) return 'Add at least one item to the order.'
  for (const r of filled) {
    if (!r.item_id) return 'Select an Item for every line you filled in.'
    if (!(Number(r.quantity) > 0)) return 'Quantity must be greater than 0 for every item.'
    if (r.rate === '' || !(Number(r.rate) >= 0)) return 'Rate must be 0 or more for every item.'
  }
  return ''
}

async function save() {
  if (saving.value) return
  const problem = validate()
  if (problem) return say('err', problem)

  saving.value = true
  const payload = {
    supplier_id: form.supplier_id,
    order_date: form.order_date,
    location_id: form.location_id,
    deliver_to: form.deliver_to.trim(),
    items: rows.value
      .filter((r) => r.item_id)
      .map((r) => ({ item_id: r.item_id, quantity: r.quantity, rate: r.rate })),
  }
  try {
    const res = await api.post('/purchase-orders', payload)
    const reference = res.data.po_number
    say('ok', res.message)
    resetForm()
    form.reference = reference // show the generated reference
  } catch (e) {
    say('err', errMsg(e)) // form values are kept
  }
  saving.value = false
}
</script>

<template>
  <div class="card">
    <h2>New Purchase Order</h2>
    <p class="hint">Reference is generated automatically. Only active suppliers, items and locations can be selected.</p>
    <div v-if="msg.text" class="msg" :class="msg.type">
      {{ msg.type === 'ok' ? '✓' : '⚠' }} {{ msg.text }}
    </div>

    <div class="row">
      <div>
        <label>Supplier *</label>
        <select v-model="form.supplier_id">
          <option value="">Select Supplier</option>
          <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div>
        <label>Reference</label>
        <input v-model="form.reference" readonly placeholder="Generated after saving" />
      </div>
      <div>
        <label>Order Date *</label>
        <input v-model="form.order_date" type="date" />
      </div>
      <div>
        <label>Location *</label>
        <select v-model="form.location_id">
          <option value="">Select Location</option>
          <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
        </select>
      </div>
      <div class="full">
        <label>Deliver to *</label>
        <textarea
          v-model="form.deliver_to"
          maxlength="255"
          placeholder="Delivery address (filled from the selected location, editable)"
        />
      </div>
    </div>

    <h2 style="margin-top: 22px">Order Items</h2>
    <div class="po-wrap">
      <div class="po-head">
        <div>Item</div><div>UOM</div><div>Quantity</div><div>Rate</div><div>Amount</div><div></div>
      </div>

      <div v-for="(r, i) in rows" :key="r.uid" class="po-row">
        <select v-model="r.item_id">
          <option value="">Select Item</option>
          <option v-for="it in items" :key="it.id" :value="it.id">{{ it.item_name }} ({{ it.item_code }})</option>
        </select>
        <input :value="itemOf(r)?.uom_name || '-'" readonly />
        <input v-model.number="r.quantity" type="number" min="0.001" step="0.001" />
        <input v-model.number="r.rate" type="number" min="0" step="0.01" />
        <span class="amt">{{ amount(r).toFixed(2) }}</span>
        <button type="button" class="rm-x" title="Remove row" @click="removeRow(i)">✕</button>
      </div>

      <div class="po-total"><span>Total:</span><span>{{ total.toFixed(2) }}</span></div>
    </div>

    <div class="actions">
      <button type="button" class="btn primary" :disabled="saving" @click="save">
        {{ saving ? 'Saving...' : 'Save Purchase Order' }}
      </button>
      <button type="button" class="btn secondary" @click="onReset">Reset</button>
    </div>
  </div>
</template>