<script setup>
const props = defineProps({ module: { type: String, required: true } })
const allModules = modules
const mod = computed(() => allModules.find((m) => m.key === props.module))
</script>

<template>
  <div v-if="mod">
    <div v-for="g in mod.groups" :key="g.title" class="card">
      <h2>{{ g.title }}</h2>
      <p v-if="!g.links.length" class="hint" style="margin: 8px 0 0">Nothing here yet.</p>
      <div v-else class="link-grid">
        <NuxtLink v-for="l in g.links" :key="l.to" :to="l.to" class="link-card">
          <span>{{ l.icon }}</span>{{ l.label }}
        </NuxtLink>
      </div>
    </div>
  </div>
</template>