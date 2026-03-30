const PUBLIC_PATHS = ['/login', '/register', '/invite/accept']

export default defineNuxtRouteMiddleware((to) => {
  const authCookie = useCookie('auth_token')

  if (!authCookie.value && !PUBLIC_PATHS.includes(to.path)) {
    return navigateTo('/login')
  }

  if (authCookie.value && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/')
  }
})
