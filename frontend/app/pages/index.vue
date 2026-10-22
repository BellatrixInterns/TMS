<script setup>
definePageMeta({ title: 'Home', subtitle: 'Choose a module to get started' })
const allModules = modules
</script>

<template>
  <div class="mod-grid">
    <NuxtLink v-for="m in allModules" :key="m.key" :to="m.path" class="mod-card">
      <div class="big">{{ m.icon }}</div>
      <h3>{{ m.title }}</h3>
      <p>{{ m.description }}</p>
    </NuxtLink>
  </div>
</template>