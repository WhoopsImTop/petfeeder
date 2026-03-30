<template>
  <AuthPageShell
    outer-class="overflow-y-auto py-12"
    card-class="text-center my-auto"
    title="Haushalts-Einladung"
    :subtitle="subtitle"
  >
    <div v-if="phase === 'loading'" class="py-8 text-slate-500 font-bold text-sm">Einladung wird geprüft…</div>

    <template v-else-if="phase === 'bad_token'">
      <p class="text-slate-600 text-sm font-medium mb-6">Der Link ist ungültig (fehlender Token).</p>
      <NuxtLink to="/" class="text-primary-500 font-bold hover:underline">Zur Startseite</NuxtLink>
    </template>

    <template v-else-if="phase === 'invite_error'">
      <AuthFormError :message="error" />
      <p class="mt-4 text-sm text-slate-500">
        <NuxtLink to="/register" class="text-primary-500 font-bold hover:underline">Neu registrieren</NuxtLink>
        oder
        <NuxtLink :to="loginLink" class="text-primary-500 font-bold hover:underline">Einloggen</NuxtLink>
      </p>
    </template>

    <template v-else-if="phase === 'need_login'">
      <p class="text-slate-600 text-sm font-medium mb-6">
        Melde dich mit der E-Mail aus der Einladung an, um dem Haushalt beizutreten.
      </p>
      <NuxtLink
        :to="loginLink"
        class="inline-flex w-full justify-center items-center py-4 bg-app-sage text-white font-bold rounded-2xl shadow-md hover:opacity-95 transition-opacity"
      >
        Zum Login
      </NuxtLink>
      <p class="mt-6 text-sm text-slate-400">
        Noch kein Konto?
        <NuxtLink :to="registerLink" class="text-primary-500 font-bold hover:underline">Registrieren</NuxtLink>
      </p>
    </template>

    <template v-else-if="phase === 'accepting'">
      <div class="py-8 text-slate-500 font-bold text-sm flex flex-col items-center gap-3">
        <span class="animate-spin w-8 h-8 border-2 border-app-sage border-t-transparent rounded-full" />
        Tritt dem Haushalt bei…
      </div>
    </template>

    <template v-else-if="phase === 'success'">
      <p class="text-app-sage font-extrabold mb-4">{{ successMessage }}</p>
      <p class="text-sm text-slate-500">Du wirst weitergeleitet…</p>
    </template>

    <template v-else-if="phase === 'accept_error'">
      <AuthFormError :message="error" />
      <button
        type="button"
        class="mt-6 w-full py-4 bg-app-sage text-white font-bold rounded-2xl"
        @click="router.push('/')"
      >
        Zur App
      </button>
    </template>
  </AuthPageShell>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from '~/stores/auth'
import { useHouseholdStore } from '~/stores/household'

definePageMeta({ layout: false })

const route = useRoute()
const router = useRouter()
const config = useRuntimeConfig()
const authStore = useAuthStore()
const householdStore = useHouseholdStore()

const token = computed(() => {
  const q = route.query.token
  return typeof q === 'string' && q.length === 64 ? q : ''
})

const preview = ref<{ household_name?: string; email?: string } | null>(null)
const error = ref('')
const successMessage = ref('')
const phase = ref<
  'loading' | 'bad_token' | 'invite_error' | 'need_login' | 'accepting' | 'success' | 'accept_error'
>('loading')

const subtitle = computed(() => {
  if (preview.value?.household_name) {
    return `Einladung zu „${preview.value.household_name}“`
  }
  return 'Einladung annehmen'
})

const loginLink = computed(() => {
  const path = route.fullPath.startsWith('/') ? route.fullPath : `/invite/accept?token=${token.value}`
  return `/login?redirect=${encodeURIComponent(path)}`
})

const registerLink = computed(() =>
  token.value ? `/register?invite_token=${encodeURIComponent(token.value)}` : '/register'
)

async function tryAccept() {
  if (!token.value) return
  phase.value = 'accepting'
  error.value = ''
  try {
    const res = await householdStore.acceptInviteToken(token.value)
    successMessage.value = res?.message || 'Du bist dem Haushalt beigetreten.'
    await authStore.fetchUser()
    const hid = res?.household_id
    if (hid != null) {
      await householdStore.setActiveHousehold(Number(hid))
    }
    phase.value = 'success'
    setTimeout(() => router.push('/'), 1200)
  } catch (err: unknown) {
    const data =
      err && typeof err === 'object' && 'data' in err
        ? (err as { data?: { message?: string } }).data
        : undefined
    error.value = data?.message || 'Beitritt fehlgeschlagen.'
    phase.value = 'accept_error'
  }
}

onMounted(async () => {
  if (!token.value) {
    phase.value = 'bad_token'
    return
  }

  try {
    preview.value = await $fetch<{ household_name?: string; email?: string }>(`/invites/${token.value}`, {
      baseURL: config.public.apiBase as string,
      headers: { Accept: 'application/json' }
    })
  } catch {
    error.value =
      'Diese Einladung ist ungültig oder abgelaufen. Nutze den Link aus der E-Mail oder bitte um eine neue Einladung.'
    phase.value = 'invite_error'
    return
  }

  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch {
      /* weiter mit need_login */
    }
  }

  if (authStore.isAuthenticated) {
    await tryAccept()
  } else {
    phase.value = 'need_login'
  }
})
</script>
