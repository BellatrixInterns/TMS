<script setup>
definePageMeta({ title: 'Suppliers', subtitle: 'Manage supplier master records' })
</script>

<template>
  <MasterPage :cfg="masters.suppliers" />
</template>