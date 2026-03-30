<template>
  <AuthPageShell
    outer-class="overflow-y-auto py-12"
    card-class="my-auto"
    title="Account erstellen"
    :subtitle="inviteSubtitle"
  >
    <form class="space-y-4" @submit.prevent="handleRegister">
      <p v-if="inviteFailed" class="p-3 bg-amber-50 text-amber-900 rounded-2xl text-sm font-bold text-center">
        Dieser Einladungslink ist ungültig oder abgelaufen. Du kannst dich normal registrieren (neuer Haushalt).
      </p>
      <AuthFormError :message="error" />
      <AuthTextField v-model="form.name" label="Name" placeholder="Max Mustermann" required autocomplete="name" />
      <AuthTextField
        v-model="form.email"
        label="E-Mail"
        type="email"
        placeholder="max@example.com"
        required
        autocomplete="email"
      />
      <AuthTextField
        v-model="form.password"
        label="Passwort"
        type="password"
        placeholder="••••••••"
        required
        autocomplete="new-password"
      />
      <AuthTextField
        v-model="form.password_confirmation"
        label="Passwort wiederholen"
        type="password"
        placeholder="••••••••"
        required
        autocomplete="new-password"
      />
      <AuthSubmitButton :loading="loading" button-class="mt-4">Registrieren</AuthSubmitButton>
    </form>

    <template #footer>
      <p class="mt-8 text-sm text-slate-400 font-medium text-center">
        Bereits einen Account?
        <NuxtLink to="/login" class="text-primary-500 font-bold hover:underline">Einloggen</NuxtLink>
      </p>
    </template>
  </AuthPageShell>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useRuntimeConfig } from '#imports'
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: false })

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()
const config = useRuntimeConfig()

const inviteToken = computed(() => {
  const q = route.query.invite_token
  return typeof q === 'string' && q.length === 64 ? q : ''
})

const inviteHouseholdName = ref('')
const inviteReady = ref(false)
const inviteFailed = ref(false)
const inviteSubtitle = computed(() =>
  inviteHouseholdName.value
    ? `Einladung zu „${inviteHouseholdName.value}“ — nutze die E-Mail aus der Einladung.`
    : 'Tritt der Petcare-Familie bei'
)

const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })
const loading = ref(false)
const error = ref('')

onMounted(async () => {
  if (!inviteToken.value) return
  try {
    const data = await $fetch<{ household_name?: string; email?: string }>(`/invites/${inviteToken.value}`, {
      baseURL: config.public.apiBase as string,
      headers: { Accept: 'application/json' }
    })
    if (data.household_name) inviteHouseholdName.value = data.household_name
    if (data.email) form.email = data.email
    inviteReady.value = true
  } catch {
    inviteFailed.value = true
  }
})

const handleRegister = async () => {
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwörter stimmen nicht überein.'
    return
  }
  loading.value = true
  error.value = ''
  try {
    const body: Record<string, string | undefined> = {
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    }
    if (inviteToken.value && inviteReady.value) body.invite_token = inviteToken.value

    await authStore.register(body)
    if (authStore.isAuthenticated) {
      router.push('/')
    } else {
      error.value = 'Registrierung unvollständig. Bitte erneut versuchen.'
    }
  } catch (err: unknown) {
    const data = err && typeof err === 'object' && 'data' in err ? (err as { data?: { message?: string } }).data : undefined
    error.value = data?.message || 'Ein Fehler ist bei der Registrierung aufgetreten.'
  } finally {
    loading.value = false
  }
}
</script>
