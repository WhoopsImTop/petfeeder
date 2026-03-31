<template>
  <div class="h-[100dvh] flex flex-col bg-app-cream text-app-brown pb-[calc(env(safe-area-inset-bottom)+80px)] overflow-hidden font-nunito">
    <!-- Top Bar -->
    <header class="flex items-center justify-between px-4 sm:px-6 pt-10 pb-4 z-10 sticky top-0 bg-app-cream/95 backdrop-blur-sm">
      <div class="flex flex-col">
        <h1 class="text-3xl font-extrabold tracking-tight text-app-brown">Hi {{ userFirstName }}</h1>
        <p class="text-sm font-bold text-app-muted">{{ householdStore.activeHousehold?.name || 'Dein Haushalt' }}</p>
      </div>
      <button type="button" class="relative hover:opacity-80 transition-opacity" @click="openSettings">
        <div class="w-14 h-14 rounded-full overflow-hidden bg-app-tan/60 flex items-center justify-center border-2 border-app-tan shadow-sm">
          <UserIcon v-if="!userAvatar" class="w-7 h-7 text-app-accent/70" />
          <img v-else :src="userAvatar" class="w-full h-full object-cover" alt="">
        </div>
      </button>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-y-auto py-2 scroll-smooth">
      <slot />
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="fixed bottom-0 left-0 w-full bg-white z-20 border-t border-app-tan/50">
      <div class="flex justify-around items-center h-[80px] px-2 pb-[env(safe-area-inset-bottom)]">
        <NuxtLink
          v-for="tab in tabs"
          :key="tab.name"
          :to="tab.to"
          class="flex flex-col items-center justify-center w-full h-full space-y-1 text-app-muted nav-link transition-colors"
          active-class="text-app-accent font-bold"
        >
          <component :is="tab.icon" class="w-8 h-8" :class="{ 'text-app-accent': isRouteActive(tab.to) }" />
          <span class="text-[11px]" :class="isRouteActive(tab.to) ? 'text-app-accent font-extrabold' : 'text-app-muted font-bold'">{{ tab.name }}</span>
        </NuxtLink>
      </div>
    </nav>

    <!-- Settings Overlay -->
    <div v-if="isSettingsOpen" class="fixed inset-0 bg-slate-50 z-50 overflow-y-auto pb-[env(safe-area-inset-bottom)]">
      <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between mt-4">
          <h2 class="text-3xl font-extrabold text-slate-800">Haushalt</h2>
          <button @click="isSettingsOpen = false" class="p-3 bg-white rounded-full shadow-soft hover:bg-slate-50 transition-colors">
            <XMarkIcon class="w-6 h-6 text-slate-600" />
          </button>
        </div>
        
        <div class="mt-8 space-y-6">
          <!-- House Switcher -->
          <section>
            <h3 class="text-lg font-bold mb-3 px-1">Aktueller Haushalt</h3>
            <div class="bg-white p-5 rounded-3xl flex justify-between items-center shadow-soft">
              <span class="font-bold text-lg">{{ householdStore.activeHousehold?.name || 'Lade...' }}</span>
              <div class="relative">
                <select 
                  v-if="householdStore.households.length > 1"
                  @change="(e) => householdStore.setActiveHousehold(e.target.value)"
                  class="opacity-0 absolute inset-0 w-full cursor-pointer z-10"
                >
                  <option v-for="h in householdStore.households" :key="h.id" :value="h.id">{{ h.name }}</option>
                </select>
                <button v-if="householdStore.households.length > 1" class="px-4 py-2 bg-primary-50 text-primary-600 rounded-xl text-sm font-bold relative z-0">
                  Wechseln
                </button>
              </div>
            </div>
            <div v-if="isAdmin" class="mt-4 bg-white p-5 rounded-3xl shadow-soft">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <h4 class="font-bold">Haushaltsname</h4>
                  <p class="text-sm text-slate-500">{{ householdStore.activeHousehold?.name || '-' }}</p>
                </div>
                <button
                  type="button"
                  class="p-2.5 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"
                  aria-label="Haushaltsname bearbeiten"
                  @click="showRenameForm = !showRenameForm"
                >
                  <PencilSquareIcon class="w-5 h-5" />
                </button>
              </div>
              <form v-if="showRenameForm" @submit.prevent="handleRenameHousehold" class="space-y-3 mt-4">
                <input
                  v-model="renameHouseholdForm.name"
                  type="text"
                  required
                  maxlength="255"
                  placeholder="Neuer Haushaltsname"
                  class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:outline-none focus:border-primary-300 transition-colors text-sm"
                />
                <div class="flex gap-2 pt-1">
                  <button
                    type="button"
                    class="flex-1 py-3 text-slate-500 font-bold bg-slate-100 rounded-xl hover:bg-slate-200"
                    @click="showRenameForm = false"
                  >
                    Abbrechen
                  </button>
                  <button
                    type="submit"
                    :disabled="isRenamingHousehold || !renameHouseholdForm.name.trim()"
                    class="flex-1 py-3 bg-app-sage text-white font-bold rounded-xl hover:bg-primary-600 disabled:opacity-60 flex justify-center items-center"
                  >
                    <span v-if="isRenamingHousehold" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                    Speichern
                  </button>
                </div>
              </form>
              <p v-if="renameHouseholdMessage" class="text-green-500 font-bold text-sm text-center mt-3">{{ renameHouseholdMessage }}</p>
              <p v-if="renameHouseholdError" class="text-red-500 font-bold text-sm text-center mt-3">{{ renameHouseholdError }}</p>
            </div>
          </section>



          <!-- Push Notifications -->
          <section>
            <h3 class="text-lg font-bold mb-3 px-1">Benachrichtigungen</h3>
            <div class="bg-white p-5 rounded-3xl shadow-soft flex items-center justify-between">
              <div>
                <span class="font-bold block text-slate-700">Push-Benachrichtigungen</span>
                <span class="text-xs text-slate-400 font-medium">{{ pushStatusText }}</span>
              </div>
              <button 
                @click="togglePushNotifications"
                :disabled="isPushLoading"
                class="px-4 py-2 font-bold rounded-xl text-sm transition-colors"
                :class="isPushEnabled ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-primary-50 text-primary-600 hover:bg-primary-100'"
              >
                {{ isPushLoading ? 'Lädt...' : (isPushEnabled ? 'Deaktivieren' : 'Aktivieren') }}
              </button>
            </div>
          </section>

          <!-- Members List -->
          <section>
            <h3 class="text-lg font-bold mb-3 px-1">Mitglieder</h3>
            
            <div class="bg-white p-5 rounded-3xl space-y-4 shadow-soft">
              <!-- Loading state -->
              <div v-if="!householdStore.activeHouseholdDetails" class="animate-pulse flex gap-4">
                <div class="w-10 h-10 bg-slate-200 rounded-full"></div>
                <div class="h-10 bg-slate-200 rounded flex-1"></div>
              </div>

              <!-- Actual members -->
              <template v-else>
                <div v-for="user in householdStore.activeHouseholdDetails.users" :key="user.id" class="flex items-center justify-between">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 font-bold">
                      <img v-if="user.avatar" :src="user.avatar" class="w-full h-full rounded-full object-cover">
                      <span v-else>{{ user.name.charAt(0) }}</span>
                    </div>
                    <span class="font-semibold text-slate-700">
                      {{ user.id === authStore.user?.id ? 'Du' : user.name }}
                      <span class="text-slate-400 font-normal text-sm">({{ user.pivot.role }})</span>
                      <span
                        v-if="user.pivot.role === 'sitter' && user.pivot.expires_at"
                        class="text-slate-400 font-normal text-sm"
                      >
                        · bis {{ formatDate(user.pivot.expires_at) }}
                      </span>
                    </span>
                  </div>
                  <button
                    v-if="isAdmin && user.id !== authStore.user?.id"
                    type="button"
                    class="p-2 rounded-lg text-red-500 hover:bg-red-50 transition-colors disabled:opacity-50"
                    :disabled="isRemovingMemberId === user.id"
                    :aria-label="`${user.name} entfernen`"
                    @click="handleRemoveMember(user)"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
              </template>
            </div>
            
            <!-- Invite Button / Form -->
            <div v-if="!showInviteForm" class="mt-4">
              <button 
                v-if="isAdmin"
                @click="showInviteForm = true"
                class="w-full py-4 bg-app-sage text-white font-bold shadow-soft rounded-3xl flex items-center justify-center gap-2 hover:bg-app-sage/90 transition-colors"
              >
                <PlusIcon class="w-5 h-5 text-white" />
                Mitglied einladen
              </button>
            </div>
            
            <div v-else class="mt-4 bg-white p-5 rounded-3xl shadow-soft">
              <h4 class="font-bold mb-4">Einladen</h4>
              <p class="text-xs text-slate-500 font-medium mb-4 leading-relaxed">
                Bereits registrierte Nutzer werden sofort hinzugefügt und erhalten eine E-Mail.
                Bei neuer Adresse verschicken wir eine Einladung mit Registrierungslink.
              </p>
              <form @submit.prevent="handleInvite" class="space-y-3">
                <input 
                  v-model="inviteForm.email"
                  type="email" 
                  inputmode="email"
                  autocomplete="email"
                  required
                  placeholder="E-Mail Adresse"
                  class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:outline-none focus:border-primary-300 transition-colors text-sm"
                />
                <select 
                  v-model="inviteForm.role"
                  class="w-full px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:outline-none focus:border-primary-300 transition-colors text-sm"
                >
                  <option value="member">Mitglied</option>
                  <option value="sitter">Pet Sitter (temporär)</option>
                  <option value="admin">Admin</option>
                </select>
                <input
                  v-if="inviteForm.role === 'sitter'"
                  v-model="inviteForm.expires_at"
                  type="date"
                  required
                  class="w-full ios-date-fix px-4 py-3 bg-slate-50 border-2 border-slate-100 rounded-xl focus:outline-none focus:border-primary-300 transition-colors text-sm"
                />
                <div class="flex gap-2 pt-2">
                  <button type="button" @click="showInviteForm = false" class="flex-1 py-3 text-slate-500 font-bold bg-slate-100 rounded-xl hover:bg-slate-200">Abbrechen</button>
                  <button type="submit" :disabled="isInviting" class="flex-1 py-3 bg-app-sage text-white font-bold rounded-xl hover:bg-primary-600 flex justify-center items-center">
                    <span v-if="isInviting" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                    Senden
                  </button>
                </div>
                <p v-if="inviteMessage" class="text-green-500 font-bold text-sm text-center mt-2">{{ inviteMessage }}</p>
                <p v-if="inviteError" class="text-red-500 font-bold text-sm text-center mt-2">{{ inviteError }}</p>
              </form>
            </div>
          </section>

          <section class="pt-8">
            <button @click="handleLogout" class="w-full py-4 text-red-500 font-bold bg-red-50 hover:bg-red-100 transition-colors rounded-3xl">
              Abmelden
            </button>
          </section>
        </div>
      </div>
    </div>

    <PetEditDrawerHost />
  </div>
</template>

<script setup>
import { ref, computed, reactive, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '~/stores/auth'
import { useHouseholdStore } from '~/stores/household'
import { useRuntimeConfig } from '#imports'
import { formatDate } from '~/utils/formatters'
import { 
  HomeIcon, 
  ListBulletIcon,
  ChartBarIcon,
  UserIcon,
  XMarkIcon,
  PlusIcon,
  PencilSquareIcon,
  TrashIcon
} from '@heroicons/vue/24/solid'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const householdStore = useHouseholdStore()

const isSettingsOpen = ref(false)

const userFirstName = computed(() => {
  const name = authStore.user?.name || 'Gast'
  return name.split(' ')[0]
})
const userAvatar = computed(() => authStore.user?.avatar || null)

const tabs = [
  { name: 'Dashboard', to: '/', icon: HomeIcon },
  { name: 'Tiere', to: '/pets', icon: ListBulletIcon },
  { name: 'Aktivitäten', to: '/activities', icon: ChartBarIcon }
]

const isRouteActive = (path) => {
  if (path === '/' && route.path === '/') return true
  if (path !== '/' && route.path.startsWith(path)) return true
  return false
}

const openSettings = () => {
  isSettingsOpen.value = true
  if (!householdStore.activeHouseholdDetails && householdStore.activeHousehold) {
    householdStore.fetchActiveHouseholdDetails()
  }
}

// Watch household changes if settings are open
watch(() => householdStore.activeHousehold, () => {
  if (isSettingsOpen.value) {
    householdStore.fetchActiveHouseholdDetails()
  }
})

const handleLogout = () => {
  authStore.logout()
  isSettingsOpen.value = false
  router.push('/login')
}

// Invite Logic
const showInviteForm = ref(false)
const isInviting = ref(false)
const inviteForm = reactive({ email: '', role: 'member', expires_at: '' })
const inviteMessage = ref('')
const inviteError = ref('')
const isRenamingHousehold = ref(false)
const showRenameForm = ref(false)
const renameHouseholdForm = reactive({ name: '' })
const renameHouseholdMessage = ref('')
const renameHouseholdError = ref('')
const isRemovingMemberId = ref(null)

watch(() => inviteForm.role, (role) => {
  // Wenn keine „temporäre“ Rolle gewählt ist, Datum leeren.
  if (role !== 'sitter') inviteForm.expires_at = ''
})

const isAdmin = computed(() => {
  const id = authStore.user?.id
  const users = householdStore.activeHouseholdDetails?.users || []
  const me = users.find(u => u.id === id)
  return me?.pivot?.role === 'admin'
})

watch(
  () => householdStore.activeHousehold?.name,
  (name) => {
    renameHouseholdForm.name = name || ''
  },
  { immediate: true }
)

const handleInvite = async () => {
  isInviting.value = true
  inviteMessage.value = ''
  inviteError.value = ''
  try {
    const expiresAt = inviteForm.role === 'sitter' && inviteForm.expires_at ? inviteForm.expires_at : null
    const res = await householdStore.inviteMember(inviteForm.email, inviteForm.role, expiresAt)
    inviteMessage.value = (res && typeof res.message === 'string') ? res.message : 'Erfolgreich eingeladen!'
    setTimeout(() => {
      showInviteForm.value = false
      inviteForm.email = ''
      inviteForm.role = 'member'
      inviteForm.expires_at = ''
      inviteMessage.value = ''
    }, 2000)
  } catch (err) {
    inviteError.value = err.data?.message || 'Fehler beim Einladen.'
  } finally {
    isInviting.value = false
  }
}

const handleRenameHousehold = async () => {
  const name = renameHouseholdForm.name.trim()
  if (!name) return
  isRenamingHousehold.value = true
  renameHouseholdMessage.value = ''
  renameHouseholdError.value = ''
  try {
    await householdStore.updateActiveHouseholdName(name)
    await Promise.all([
      authStore.fetchUser(),
      householdStore.fetchActiveHouseholdDetails()
    ])
    renameHouseholdMessage.value = 'Haushaltsname aktualisiert.'
    showRenameForm.value = false
  } catch (err) {
    renameHouseholdError.value = err.data?.message || 'Fehler beim Aktualisieren des Haushaltsnamens.'
  } finally {
    isRenamingHousehold.value = false
  }
}

const handleRemoveMember = async (user) => {
  if (!user?.id) return
  const confirmed = window.confirm(`${user.name} wirklich aus dem Haushalt entfernen?`)
  if (!confirmed) return

  isRemovingMemberId.value = Number(user.id)
  try {
    await householdStore.removeMember(Number(user.id))
    await Promise.all([
      authStore.fetchUser(),
      householdStore.fetchActiveHouseholdDetails()
    ])
  } catch (err) {
    alert(err.data?.message || 'Mitglied konnte nicht entfernt werden.')
  } finally {
    isRemovingMemberId.value = null
  }
}

// Push Notifications Logic
const isPushEnabled = ref(false)
const isPushLoading = ref(false)
const pushStatusText = ref('Nicht abonniert')

onMounted(() => {
  if (typeof window !== 'undefined' && 'Notification' in window) {
    if (Notification.permission === 'granted') {
      checkPushSubscription()
    } else if (Notification.permission === 'denied') {
      pushStatusText.value = 'Im Browser blockiert'
    }
  }
})

async function checkPushSubscription() {
  if (!('serviceWorker' in navigator)) return
  try {
    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.getSubscription()
    if (subscription) {
      isPushEnabled.value = true
      pushStatusText.value = 'Aktiviert & Empfangsbereit'
    }
  } catch(e) {}
}

async function enablePushNotifications() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    alert("Push-Benachrichtigungen werden von diesem Browser nicht unterstützt.")
    return
  }

  isPushLoading.value = true
  try {
    const permission = await Notification.requestPermission()
    if (permission !== 'granted') {
       pushStatusText.value = 'Berechtigung verweigert'
       return
    }

    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array('BDD96XuDI9qzRygL02-HHXC6DCNMFUNGNQ3i0o1K71cK4KMulPNj1zii3Wv44cZQKbeKV2_DpK-ZFsJjyDtI9Zg')
    })
    
    await sendSubscriptionToBackend(subscription)

    isPushEnabled.value = true
    pushStatusText.value = 'Aktiviert & Empfangsbereit'
  } catch (error) {
    console.error("Fehler bei Push-Aktivierung:", error)
    alert("Fehler bei Push-Aktivierung.")
  } finally {
    isPushLoading.value = false
  }
}

async function disablePushNotifications() {
  if (!('serviceWorker' in navigator)) return

  isPushLoading.value = true
  try {
    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.getSubscription()

    if (subscription) {
      const endpoint = subscription.endpoint
      await subscription.unsubscribe()
      await removeSubscriptionFromBackend(endpoint)
    } else {
      await removeSubscriptionFromBackend()
    }

    isPushEnabled.value = false
    pushStatusText.value = 'Nicht abonniert'
  } catch (error) {
    console.error("Fehler bei Push-Deaktivierung:", error)
    alert("Fehler bei Push-Deaktivierung.")
  } finally {
    isPushLoading.value = false
  }
}

async function togglePushNotifications() {
  if (isPushEnabled.value) {
    await disablePushNotifications()
    return
  }
  await enablePushNotifications()
}

async function sendSubscriptionToBackend(subscription) {
  const subJson = subscription.toJSON()
  const payload = {
    endpoint: subJson.endpoint,
    keys: {
      p256dh: subJson.keys?.p256dh || '',
      auth: subJson.keys?.auth || ''
    },
    contentEncoding: ('PushManager' in window && PushManager.supportedContentEncodings) ? PushManager.supportedContentEncodings[0] : 'aesgcm'
  }
  
  const config = useRuntimeConfig()
  await $fetch('/user/push-subscriptions', {
    baseURL: config.public.apiBase,
    method: 'POST',
    body: payload,
    headers: authStore.baseHeaders
  })
}

async function removeSubscriptionFromBackend(endpoint) {
  const config = useRuntimeConfig()
  await $fetch('/user/push-subscriptions', {
    baseURL: config.public.apiBase,
    method: 'DELETE',
    body: endpoint ? { endpoint } : {},
    headers: authStore.baseHeaders
  })
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}
</script>
