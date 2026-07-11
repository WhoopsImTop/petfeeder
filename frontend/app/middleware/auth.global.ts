const PUBLIC_PATHS = ['/login', '/register', '/invite/accept']

export default defineNuxtRouteMiddleware((to) => {
  const authCookie = useCookie('auth_token')
  const refreshCookie = useCookie('auth_refresh_token')

  const hasSession = !!(authCookie.value || refreshCookie.value)

  if (!hasSession && !PUBLIC_PATHS.includes(to.path)) {
    return navigateTo('/login')
  }

  if (hasSession && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/')
  }
})
