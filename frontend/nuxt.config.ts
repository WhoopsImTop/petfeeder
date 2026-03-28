// https://nuxt.com/docs/api/configuration/nuxt-config
import tailwindcss from "@tailwindcss/vite";

export default defineNuxtConfig({
  ssr: false,
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  css: ['./app/assets/css/main.css'],
  modules: [
    '@pinia/nuxt',
    '@nuxtjs/google-fonts',
    '@vite-pwa/nuxt',
  ],
  pwa: {
    strategies: 'injectManifest',
    srcDir: 'public',
    filename: 'sw.js',
    registerType: 'autoUpdate',
    manifest: {
      name: 'Petfeeder',
      short_name: 'Petfeeder',
      theme_color: '#F4BA8C',
      background_color: '#ffffff',
      display: 'standalone',
      start_url: '/',
      scope: '/',
      icons: [
        {
          src: '/pwa-192x192.svg',
          sizes: '192x192',
          type: 'image/svg+xml',
          purpose: 'any maskable'
        },
        {
          src: '/pwa-512x512.svg',
          sizes: '512x512',
          type: 'image/svg+xml',
          purpose: 'any maskable'
        }
      ]
    },
    workbox: {
      globPatterns: ['**/*.{js,css,html,png,svg,ico}']
    },
    devOptions: {
      enabled: true,
      suppressWarnings: true,
      navigateFallbackAllowlist: [/^\/$/],
      type: 'module'
    }
  },
  // @ts-expect-error The module types are only available after first build
  googleFonts: {
    families: {
      Nunito: [400, 500, 600, 700, 800, 900]
    }
  },
  runtimeConfig: {
    public: {
      apiBase:
        process.env.NUXT_PUBLIC_API_BASE ||
        process.env.API_URL ||
        process.env.API_PROD_URL ||
        '',
    }
  },
  vite: {
    plugins: [
      tailwindcss(),
    ],
  },
})
