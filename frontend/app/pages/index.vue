<script setup>
definePageMeta({ title: 'Home', subtitle: 'Your workspace at a glance' })

const api = useApi()
const user = useCookie('user')
const allModules = modules
const loading = ref(true)
const error = ref('')
const summary = reactive({
  items: '–',
  categories: '–',
  suppliers: '–',
  orders: '–',
})

async function loadSummary() {
  loading.value = true
  error.value = ''

  try {
    const [items, categories, suppliers, orders] = await Promise.all([
      api.get('/items'),
      api.get('/categories'),
      api.get('/suppliers'),
      api.get('/purchase-orders'),
    ])

    summary.items = items.data.length
    summary.categories = categories.data.length
    summary.suppliers = suppliers.data.length
    summary.orders = orders.data.length
  } catch (e) {
    error.value = errMsg(e, 'Could not load your workspace summary.')
  } finally {
    loading.value = false
  }
}

onMounted(loadSummary)
</script>

<template>
  <div class="home-dashboard">
    <section class="home-welcome">
      <div>
        <p class="home-eyebrow">BELLATRIX WORKSPACE</p>
        <h2>Welcome back<span v-if="user?.full_name">, {{ user.full_name }}</span></h2>
        <p>Here’s a quick overview of your operations. Choose a module to get started.</p>
      </div>
      <div class="home-welcome-mark" aria-hidden="true">✦</div>
    </section>

    <section aria-label="Workspace summary">
      <div class="home-section-heading">
        <div>
          <h2>Workspace summary</h2>
          <p>Live totals from your workspace</p>
        </div>
        <button class="home-refresh" type="button" :disabled="loading" @click="loadSummary">
          {{ loading ? 'Refreshing…' : '↻ Refresh' }}
        </button>
      </div>
      <div v-if="error" class="msg err" role="alert">
        ⚠ {{ error }}
        <button class="home-retry" type="button" @click="loadSummary">Try again</button>
      </div>
      <div class="home-summary-grid">
        <div class="home-summary-card">
          <span class="home-summary-icon">📦</span>
          <span class="home-summary-value">{{ loading ? '…' : summary.items }}</span>
          <span class="home-summary-label">Items</span>
          <NuxtLink to="/stores/items" aria-label="View items">View items <span aria-hidden="true">→</span></NuxtLink>
        </div>
        <div class="home-summary-card">
          <span class="home-summary-icon">🗂️</span>
          <span class="home-summary-value">{{ loading ? '…' : summary.categories }}</span>
          <span class="home-summary-label">Categories</span>
          <NuxtLink to="/stores/categories" aria-label="View categories">View categories <span aria-hidden="true">→</span></NuxtLink>
        </div>
        <div class="home-summary-card">
          <span class="home-summary-icon">🚚</span>
          <span class="home-summary-value">{{ loading ? '…' : summary.suppliers }}</span>
          <span class="home-summary-label">Suppliers</span>
          <NuxtLink to="/purchases/suppliers" aria-label="View suppliers">View suppliers <span aria-hidden="true">→</span></NuxtLink>
        </div>
        <div class="home-summary-card">
          <span class="home-summary-icon">📋</span>
          <span class="home-summary-value">{{ loading ? '…' : summary.orders }}</span>
          <span class="home-summary-label">Purchase orders</span>
          <NuxtLink to="/purchases/orders" aria-label="View purchase orders">View orders <span aria-hidden="true">→</span></NuxtLink>
        </div>
      </div>
    </section>

    <section aria-label="Modules">
      <div class="home-section-heading">
        <div>
          <h2>Your modules</h2>
          <p>Open a module to see its pages in the left menu</p>
        </div>
      </div>
      <div class="mod-grid">
        <NuxtLink v-for="m in allModules" :key="m.key" :to="m.path" class="mod-card">
          <div class="big">{{ m.icon }}</div>
          <h3>{{ m.title }}<span class="home-card-arrow" aria-hidden="true">→</span></h3>
          <p>{{ m.description }}</p>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>