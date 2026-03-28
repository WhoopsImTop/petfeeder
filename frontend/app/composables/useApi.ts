import { useRuntimeConfig } from '#imports'

export const useApi = () => {
  const config = useRuntimeConfig()
  
  let baseURL = config.public.apiBase
  // Ensure protocol is attached if user forgot in .env
  if (baseURL && !baseURL.startsWith('http')) {
    baseURL = `http://${baseURL}`
  }

  return $fetch.create({
    baseURL,
    headers: {
      'Accept': 'application/json'
    }
  })
}
