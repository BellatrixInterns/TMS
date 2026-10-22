export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },

  // This is a login-protected admin app, so we run it as a single-page app.
  // It keeps login handling simple (no server-side rendering surprises).
  ssr: false,

  css: ['~/assets/css/main.css'],

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },
})