<script setup>
definePageMeta({ title: 'Purchase Orders', subtitle: 'All saved purchase orders' })

const api = useApi()
const orders = ref([])
const loading = ref(true)
const error = ref('')
const open = ref({})

const fmtDate = (d) => (d ? d.split('-').reverse().join('-') : '') // 2026-10-01 -> 01-10-2026
const money = (v) => Number(v).toFixed(2)
const num = (v) => Number(v) // 2.000 -> 2

function toggle(id) {
  open.value[id] = !open.value[id]
}

onMounted(async () => {
  try {
    const res = await api.get('/purchase-orders')
    orders.value = res.data
  } catch (e) {
    error.value = errMsg(e, 'Could not load data. Is Laravel running?')
  }
  loading.value = false
})
</script>

<template>
  <div class="card">
    <h2>Purchase Orders</h2>
    <p class="hint">Click a row to see the items in that order.</p>
    <div v-if="error" class="msg err">⚠ {{ error }}</div>

    <div class="scroll">
      <table>
        <thead>
          <tr>
            <th>Reference</th><th>Supplier</th><th>Order Date</th>
            <th>Location</th><th>Deliver To</th><th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="empty">Loading...</td></tr>
          <tr v-else-if="!orders.length"><td colspan="6" class="empty">No purchase orders yet.</td></tr>

          <template v-for="o in orders" :key="o.id">
            <tr class="clickable" @click="toggle(o.id)">
              <td>
                <code class="badge">{{ o.po_number }}</code>
                <span class="toggle-hint">{{ open[o.id] ? '▴' : '▾' }} items</span>
              </td>
              <td>{{ o.supplier_name }}</td>
              <td>{{ fmtDate(o.order_date) }}</td>
              <td>{{ o.location_name }}</td>
              <td>{{ o.deliver_to }}</td>
              <td>{{ money(o.total_amount) }}</td>
            </tr>
            <tr v-if="open[o.id]">
              <td colspan="6" class="detail-cell">
                <table v-if="o.items.length">
                  <thead>
                    <tr>
                      <th>Item Code</th><th>Item Name</th><th>UOM</th>
                      <th>Quantity</th><th>Rate</th><th>Amount</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(l, i) in o.items" :key="i">
                      <td>{{ l.item_code }}</td>
                      <td>{{ l.item_name }}</td>
                      <td>{{ l.uom_name }}</td>
                      <td>{{ num(l.quantity) }}</td>
                      <td>{{ money(l.rate) }}</td>
                      <td>{{ money(l.amount) }}</td>
                    </tr>
                  </tbody>
                </table>
                <span v-else>No items recorded for this order.</span>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>