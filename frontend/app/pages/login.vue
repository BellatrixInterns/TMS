<script setup lang="ts">
definePageMeta({ layout: false })

const config = useRuntimeConfig()
const route = useRoute()
const api = useApi()
const token = useCookie('token', { maxAge: 60 * 60 * 24 * 7 })
const user = useCookie('user', { maxAge: 60 * 60 * 24 * 7 })

const mode = ref<'signin' | 'signup'>('signin')
const username = ref('')
const password = ref('')
const error = ref('')
const busy = ref(false)
const googleBusy = ref(false)

const authErrors: Record<string, string> = {
  google_not_configured: 'Google sign-in has not been configured yet. Contact your administrator.',
  google_sign_in_failed: 'Google sign-in could not be completed. Please try again.',
  company_account_required: 'Please use a verified @gmail.com Google account.',
  account_already_exists: 'An account already exists for this Gmail address. Choose Log in instead.',
  account_not_found: 'No account exists for this Gmail address yet. Choose Create account first.',
}

onMounted(() => {
  const errorCode = route.query.error
  if (typeof errorCode === 'string') error.value = authErrors[errorCode] || 'Sign-in failed. Please try again.'
})

function signInWithGoogle(intent: 'signin' | 'signup') {
  if (googleBusy.value) return
  googleBusy.value = true
  error.value = ''
  const backendUrl = (config.public.apiBase as string).replace(/\/api\/?$/, '')
  window.location.assign(`${backendUrl}/auth/google?intent=${intent}`)
}

async function loginWithPassword() {
  if (busy.value) return
  error.value = ''
  busy.value = true
  try {
    const response = await api.post('/login', { username: username.value, password: password.value })
    token.value = response.token
    user.value = response.user
    await navigateTo('/')
  } catch (e) {
    error.value = errMsg(e, 'Could not reach the server. Is Laravel running?')
  } finally {
    busy.value = false
  }
}
</script>

<template>
  <main class="auth-page">
    <section class="auth-card" aria-labelledby="auth-title">
      <aside class="auth-brand">
        <div class="auth-brand-mark" aria-hidden="true">B</div>
        <p class="auth-eyebrow">BELLATRIX · OPERATIONS</p>
        <h1>Everything your team needs, in one place.</h1>
        <p class="auth-brand-copy">A simpler way to manage purchasing, inventory, and the work that keeps your business moving.</p>
        <div class="auth-brand-footer">
          <span class="auth-brand-dot" aria-hidden="true"></span>
          <span>Private workspace for Bellatrix</span>
        </div>
      </aside>

      <div class="auth-content">
        <div class="auth-mobile-mark" aria-hidden="true">B</div>
        <p class="auth-kicker">YOUR WORKSPACE AWAITS</p>
        <h2 id="auth-title">{{ mode === 'signin' ? 'Welcome back' : 'Join your team' }}</h2>
        <p class="auth-subtitle">
          {{ mode === 'signin' ? 'Log in to your existing Bellatrix account.' : 'Create a new account with your Gmail address.' }}
        </p>

        <div class="auth-tabs" role="tablist" aria-label="Account access">
          <button
            class="auth-tab"
            :class="{ active: mode === 'signin' }"
            type="button"
            role="tab"
            :aria-selected="mode === 'signin'"
            @click="mode = 'signin'; error = ''"
          >Log in</button>
          <button
            class="auth-tab"
            :class="{ active: mode === 'signup' }"
            type="button"
            role="tab"
            :aria-selected="mode === 'signup'"
            @click="mode = 'signup'; error = ''; googleBusy = false"
          >Create account</button>
        </div>

        <div v-if="error" class="auth-error" role="alert">
          <span aria-hidden="true">!</span>{{ error }}
        </div>

        <button class="google-button" type="button" :disabled="googleBusy" @click="signInWithGoogle(mode)">
          <svg class="google-icon" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z" transform="translate(0 4)" />
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.9a11.02 11.02 0 0 1-4.78 7.23l7.73 5.99c4.5-4.16 7.13-10.29 7.13-17.69Z" />
            <path fill="#FBBC05" d="M10.53 28.59a14.4 14.4 0 0 1 0-9.18l-7.98-6.19a23.94 23.94 0 0 0 0 21.56l7.98-6.19Z" transform="translate(0 4)" />
            <path fill="#34A853" d="M24 48c6.47 0 11.91-2.13 15.88-5.77l-7.73-5.99c-2.14 1.44-4.89 2.29-8.15 2.29-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.02 14.62 48 24 48Z" transform="translate(0 -4)" />
          </svg>
          <span>
            {{ googleBusy ? 'Connecting to Google…' : mode === 'signup' ? 'Create account with Google' : 'Log in with Google' }}
          </span>
          <span class="google-arrow" aria-hidden="true">→</span>
        </button>

        <p class="auth-domain-note">
          <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 1.7 3 4.5v4.2c0 4.4 3 8.5 7 9.6 4-1.1 7-5.2 7-9.6V4.5l-7-2.8Zm-1 11.5L5.8 10l1.4-1.4L9 10.4l3.8-3.8 1.4 1.4L9 13.2Z" /></svg>
          Only verified <strong>@gmail.com</strong> accounts can access this workspace.
        </p>

        <template v-if="mode === 'signin'">
          <div class="auth-divider"><span>or use your password</span></div>
          <form class="password-form" @submit.prevent="loginWithPassword">
            <label for="username">Username</label>
            <input id="username" v-model="username" autocomplete="username" required placeholder="Your username">
            <label for="password">Password</label>
            <input id="password" v-model="password" type="password" autocomplete="current-password" required placeholder="Your password">
            <button class="password-button" type="submit" :disabled="busy">
              {{ busy ? 'Signing in…' : 'Sign in with password' }}
            </button>
          </form>
        </template>

        <p class="auth-terms">
          {{ mode === 'signup' ? 'New here? Create your account with a verified Gmail address.' : 'Use the Gmail address linked to your existing account.' }}
        </p>
      </div>
    </section>
    <footer class="auth-footer">© {{ new Date().getFullYear() }} Bellatrix. For authorized team members only.</footer>
  </main>
</template>
