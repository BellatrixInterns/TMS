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
    <aside class="sidebar">
      <NuxtLink to="/" class="brand">Trading Demo<span>Business App</span></NuxtLink>

      <nav class="navlist">
        <NuxtLink to="/" class="navlink" exact-active-class="active">
          <span class="icon">🏠</span><span>Home</span>
        </NuxtLink>

        <div class="section">Modules</div>
        <NuxtLink
          v-for="m in allModules"
          :key="m.key"
          :to="m.path"
          class="navlink"
          :class="{ 'mod-active': current?.key === m.key }"
        >
          <span class="icon">{{ m.icon }}</span><span>{{ m.title }}</span>
        </NuxtLink>

        <template v-if="current">
          <template v-for="g in current.groups" :key="g.title">
            <div class="section">{{ g.title }}</div>
            <NuxtLink
              v-for="l in g.links"
              :key="l.to"
              :to="l.to"
              class="navlink sub"
              active-class="active"
            >
              <span class="icon">{{ l.icon }}</span><span>{{ l.label }}</span>
            </NuxtLink>
            <div v-if="!g.links.length" class="empty-note">Nothing here yet</div>
          </template>
        </template>
      </nav>

      <div class="sidebar-footer">Laravel + Nuxt</div>
    </aside>

    <div class="main">
      <div class="topbar">
        <div>
          <h1>{{ route.meta.title }}</h1>
          <p>{{ route.meta.subtitle }}</p>
        </div>
        <div class="userbox">
          👤 {{ user?.full_name }} &nbsp;|&nbsp;
          <a href="#" @click.prevent="logout">Logout</a>
        </div>
      </div>
      <div class="content">
        <slot />
      </div>
    </div>
  </div>
</template>