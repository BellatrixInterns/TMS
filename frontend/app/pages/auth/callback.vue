<script setup lang="ts">
definePageMeta({ layout: false })

const config = useRuntimeConfig()
const token = useCookie('token', { maxAge: 60 * 60 * 24 * 7 })
const user = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })
const error = ref('')

onMounted(async () => {
  const accessToken = new URLSearchParams(window.location.hash.slice(1)).get('token')
  window.history.replaceState(null, '', '/auth/callback')

  if (!accessToken) {
    error.value = 'The sign-in link was incomplete. Please try again.'
    return
  }

  token.value = accessToken
  try {
    const response = await $fetch<any>((config.public.apiBase as string) + '/me', {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${accessToken}`,
      },
    })
    user.value = response.user
    await navigateTo('/')
  } catch (e: any) {
    token.value = null
    const apiMessage = e?.data?.message
    error.value = apiMessage
      ? `Your Google account was accepted, but the app could not start your session: ${apiMessage}`
      : 'Your Google account was accepted, but the app could not verify your session. Check that the backend is running, then try again.'
  }
})
</script>

<template>
  <main class="auth-callback">
    <div class="auth-callback-card" role="status" aria-live="polite">
      <div v-if="!error" class="auth-spinner" aria-hidden="true"></div>
      <div v-else class="auth-callback-error" aria-hidden="true">!</div>
      <h1>{{ error ? 'Sign-in could not be completed' : 'Finishing sign-in' }}</h1>
      <p>{{ error || 'Securely connecting you to your Bellatrix workspace…' }}</p>
      <NuxtLink v-if="error" to="/login">Back to sign in</NuxtLink>
    </div>
  </main>
</template>
