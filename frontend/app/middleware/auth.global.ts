export default defineNuxtRouteMiddleware((to, from) => {
  const authCookie = useCookie('auth_token')
  
  if (!authCookie.value && to.path !== '/login' && to.path !== '/register') {
    return navigateTo('/login')
  }

  if (authCookie.value && (to.path === '/login' || to.path === '/register')) {
    return navigateTo('/')
  }
})
