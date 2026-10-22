<script setup>
const route = useRoute()
const api = useApi()
const token = useCookie('token')
const user = useCookie('user')

const allModules = modules

// Which module are we in? (/stores/brands → stores)
const current = computed(() =>
  allModules.find((m) => route.path === m.path || route.path.startsWith(m.path + '/'))
)

async function logout() {
  try { await api.post('/logout') } catch (e) { /* ignore */ }
  token.value = null
  user.value = null
  await navigateTo('/login')
}
</script>

<template>
  <div class="app">
    <header class="app-header">
      <NuxtLink to="/" class="brand">Trading Demo<span>Business App</span></NuxtLink>
      <nav class="module-nav" aria-label="Main navigation">
        <NuxtLink to="/" class="module-nav-link" exact-active-class="active">
          <span class="icon">🏠</span><span>Home</span>
        </NuxtLink>
        <NuxtLink
          v-for="m in allModules"
          :key="m.key"
          :to="m.path"
          class="module-nav-link"
          :class="{ active: current?.key === m.key }"
        >
          <span class="icon">{{ m.icon }}</span><span>{{ m.title }}</span>
        </NuxtLink>
      </nav>
      <div class="userbox">
        👤 {{ user?.full_name }} &nbsp;|&nbsp;
        <a href="#" @click.prevent="logout">Logout</a>
      </div>
    </header>

    <div class="workspace-layout">
      <aside v-if="current" class="sidebar" :aria-label="`${current.title} pages`">
        <nav class="subnav">
          <template v-for="g in current.groups" :key="g.title">
            <div class="section">{{ g.title }}</div>
            <NuxtLink
              v-for="l in g.links"
              :key="l.to"
              :to="l.to"
              class="subnav-link"
              active-class="active"
            >
              <span class="icon">{{ l.icon }}</span><span>{{ l.label }}</span>
            </NuxtLink>
            <div v-if="!g.links.length" class="empty-note">Nothing here yet</div>
          </template>
        </nav>
        <div class="sidebar-footer">Laravel + Nuxt</div>
      </aside>

      <main class="main">
        <div class="page-heading">
          <h1>{{ route.meta.title }}</h1>
          <p>{{ route.meta.subtitle }}</p>
        </div>
        <div class="content">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>