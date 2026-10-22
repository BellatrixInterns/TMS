export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('token')
  if (to.path === '/auth/callback') return
  if (to.path !== '/login' && !token.value) return navigateTo('/login')
  if (to.path === '/login' && token.value) return navigateTo('/')
})