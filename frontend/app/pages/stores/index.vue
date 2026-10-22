<script setup>
definePageMeta({ title: 'Stores', subtitle: 'Items, categories, brands, UOM and locations' })

const api = useApi()
const counts = reactive({ categories: '–', brands: '–', uoms: '–', locations: '–', items: '–' })
const recent = ref([])
const loading = ref(true)
const error = ref('')

onMounted(async () => {
  try {
    const [cat, br, uo, loc, it] = await Promise.all([
      api.get('/categories'), api.get('/brands'), api.get('/uoms'), api.get('/locations'), api.get('/items'),
    ])
    counts.categories = cat.data.length
    counts.brands = br.data.length
    counts.uoms = uo.data.length
    counts.locations = loc.data.length
    counts.items = it.data.length
    recent.value = it.data.slice(-5).reverse()
  } catch (e) {
    error.value = errMsg(e, 'Could not load data. Is Laravel running?')
  }
  loading.value = false
})
</script>

<template>
  <div>
    <div v-if="error" class="msg err">⚠ {{ error }}</div>

    <div class="stats">
      <div class="stat-card"><div class="icon">🗂️</div><div class="n">{{ counts.categories }}</div><div class="l">Categories</div></div>
      <div class="stat-card"><div class="icon">🏷️</div><div class="n">{{ counts.brands }}</div><div class="l">Brands</div></div>
      <div class="stat-card"><div class="icon">📏</div><div class="n">{{ counts.uoms }}</div><div class="l">UOMs</div></div>
      <div class="stat-card"><div class="icon">📦</div><div class="n">{{ counts.items }}</div><div class="l">Items</div></div>
      <div class="stat-card"><div class="icon">📍</div><div class="n">{{ counts.locations }}</div><div class="l">Locations</div></div>
    </div>

    <div class="card">
      <h2>Recently Added Items</h2>
      <p class="hint">Latest items saved to the database</p>
      <div class="scroll">
        <table>
          <thead>
            <tr><th>Item Code</th><th>Item Name</th><th>Category</th><th>Brand</th><th>UOM</th><th>Status</th></tr>
          </thead>
          <tbody>
            <tr v-if="loading"><td colspan="6" class="empty">Loading...</td></tr>
            <tr v-else-if="!recent.length"><td colspan="6" class="empty">No items saved yet.</td></tr>
            <tr v-for="it in recent" :key="it.id">
              <td><code class="badge">{{ it.item_code }}</code></td>
              <td>{{ it.item_name }}</td>
              <td>{{ it.category_name }}</td>
              <td>{{ it.brand_name }}</td>
              <td>{{ it.uom_name }}</td>
              <td>
                <span class="status" :class="it.is_active == 1 ? 'active' : 'inactive'">
                  {{ it.is_active == 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ModuleHome module="stores" />
  </div>
</template>