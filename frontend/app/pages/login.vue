<script setup>
definePageMeta({ layout: false })

const api = useApi()
const token = useCookie('token', { maxAge: 60 * 60 * 24 * 7 })
const user = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })

const username = ref('')
const password = ref('')
const error = ref('')
const busy = ref(false)

async function login() {
  if (busy.value) return
  error.value = ''
  busy.value = true
  try {
    const res = await api.post('/login', { username: username.value, password: password.value })
    token.value = res.token
    user.value = res.user
    await navigateTo('/')
  } catch (e) {
    error.value = errMsg(e, 'Could not reach the server. Is Laravel running?')
  }
  busy.value = false
}
</script>

<template>
  <div class="login-wrap">
    <form class="login-box" @submit.prevent="login">
      <h1>Trading Demo</h1>
      <p class="sub">Sign in to continue</p>
      <label>Username</label>
      <input v-model="username" autofocus />
      <label>Password</label>
      <input v-model="password" type="password" />
      <button class="btn primary" style="width: 100%; margin-top: 20px" :disabled="busy">
        {{ busy ? 'Signing in...' : 'Log In' }}
      </button>
      <div v-if="error" class="msg err" style="margin-top: 16px">⚠ {{ error }}</div>
    </form>
  </div>
</template>