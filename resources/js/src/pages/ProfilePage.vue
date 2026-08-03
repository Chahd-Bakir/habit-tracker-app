<template>
  <AppShell>
    <template #header><TopBar eyebrow="Profile" title="Profile settings"><UiButton variant="secondary" @click="openEdit">Edit Profile</UiButton></TopBar></template>
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
      <SectionCard>
        <div class="flex flex-col items-center text-center">
          <div class="flex h-24 w-24 items-center justify-center rounded-full bg-emerald-100 text-3xl font-semibold text-emerald-700">{{ initials }}</div>
          <h2 class="mt-4 text-2xl font-semibold text-slate-900">{{ user?.name ?? 'Guest user' }}</h2>
          <p class="text-sm text-slate-500">{{ user?.email ?? 'No email available' }}</p>
        </div>
      </SectionCard>
      <SectionCard>
        <div class="grid gap-4 sm:grid-cols-2">
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Name</p><p class="mt-1 font-medium text-slate-900">{{ user?.name ?? 'Guest user' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Email</p><p class="mt-1 font-medium text-slate-900">{{ user?.email ?? 'No email available' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Language</p><p class="mt-1 font-medium text-slate-900">{{ user?.language ?? 'en' }}</p></div>
          <div class="rounded-[24px] bg-slate-50 p-4"><p class="text-sm text-slate-500">Goals</p><p class="mt-1 font-medium text-slate-900">{{ goalSummary }}</p></div>
        </div>
      </SectionCard>
    </div>

    <div v-if="showEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeEdit">
      <div class="flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-[36px] border border-emerald-100 bg-white p-6 shadow-[0_26px_80px_rgba(15,23,42,0.08)] sm:p-8">
        <div class="mb-4 flex shrink-0 items-center justify-between">
          <h2 class="text-xl font-semibold text-slate-900">Edit profile</h2>
          <button type="button" @click="closeEdit" class="rounded-full p-2 text-slate-500 hover:bg-slate-100">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <form @submit.prevent="saveProfile" class="flex min-h-0 flex-1 flex-col">
          <div class="-mr-2 min-h-0 flex-1 space-y-4 overflow-y-auto pr-2">
            <label class="block">
              <span class="mb-1.5 block text-sm font-medium text-slate-700">Name *</span>
              <input v-model="editForm.name" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="Your name">
              <span v-if="editErrors.name" class="mt-1 text-sm text-red-600">{{ editErrors.name }}</span>
            </label>

            <label class="block">
              <span class="mb-1.5 block text-sm font-medium text-slate-700">Email *</span>
              <input v-model="editForm.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="you@example.com">
              <span v-if="editErrors.email" class="mt-1 text-sm text-red-600">{{ editErrors.email }}</span>
            </label>

            <label class="block">
              <span class="mb-1.5 block text-sm font-medium text-slate-700">Language</span>
              <select v-model="editForm.language" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2.5 outline-none transition focus:border-emerald-400 focus:bg-white">
                <option value="en">English</option>
                <option value="fr">Français</option>
                <option value="ar">العربية</option>
              </select>
            </label>

            <div v-if="allGoals.length > 0" class="block">
              <span class="mb-1.5 block text-sm font-medium text-slate-700">Goals</span>
              <div class="grid grid-cols-2 gap-2">
                <button v-for="goal in allGoals" :key="goal.id" type="button" @click="toggleGoal(goal.id)" class="rounded-xl border px-3 py-2 text-sm font-medium transition" :class="editForm.goals.includes(goal.id) ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-600 hover:border-emerald-300'">
                  {{ goal.title }}
                </button>
              </div>
              <span v-if="editErrors.goals" class="mt-1 text-sm text-red-600">{{ editErrors.goals }}</span>
            </div>

            <div v-if="editMessage" class="rounded-2xl border px-4 py-3 text-sm" :class="editError ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
              {{ editMessage }}
            </div>
          </div>

          <div class="mt-4 shrink-0 border-t border-slate-100 pt-4">
            <UiButton class="w-full" :disabled="saving">{{ saving ? 'Saving...' : 'Save changes' }}</UiButton>
          </div>
        </form>
      </div>
    </div>
  </AppShell>
</template>

<script setup>
import { computed, ref, reactive } from 'vue';
import AppShell from '../components/AppShell.vue';
import SectionCard from '../components/SectionCard.vue';
import TopBar from '../components/TopBar.vue';
import UiButton from '../components/UiButton.vue';
import { getStoredUser, getUserInitials, getStoredToken, updateStoredUser } from '../utils/auth';

const user = computed(() => getStoredUser());
const initials = computed(() => getUserInitials(user.value));
const goalSummary = computed(() => {
  const goals = user.value?.goals ?? [];

  if (!goals.length) {
    return 'No goals selected yet';
  }

  return goals.map((goal) => goal.title).join(', ');
});

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

const showEdit = ref(false);
const saving = ref(false);
const editMessage = ref('');
const editError = ref(false);
const allGoals = ref([]);
const editForm = reactive({ name: '', email: '', language: 'en', goals: [] });
const editErrors = reactive({ name: '', email: '', goals: '' });

const fetchGoals = async () => {
  try {
    const res = await fetch('/api/goals', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${getStoredToken()}` },
    });
    const json = await res.json();
    if (json.success) allGoals.value = json.goals;
  } catch (e) {
    /* silent */
  }
};

const openEdit = async () => {
  const current = user.value ?? {};
  editForm.name = current.name ?? '';
  editForm.email = current.email ?? '';
  editForm.language = current.language ?? 'en';
  editForm.goals = [...(current.goals ?? []).map((g) => g.id)];
  Object.keys(editErrors).forEach((key) => { editErrors[key] = ''; });
  editMessage.value = '';
  editError.value = false;
  await fetchGoals();
  showEdit.value = true;
};

const closeEdit = () => {
  showEdit.value = false;
};

const toggleGoal = (id) => {
  const index = editForm.goals.indexOf(id);
  if (index > -1) {
    editForm.goals.splice(index, 1);
  } else {
    editForm.goals.push(id);
  }
};

const saveProfile = async () => {
  let valid = true;

  editErrors.name = editForm.name.trim() ? '' : 'Name is required';
  editErrors.email = '';
  if (!editForm.email.trim()) {
    editErrors.email = 'Email is required';
  } else if (!EMAIL_REGEX.test(editForm.email.trim())) {
    editErrors.email = 'Enter a valid email address';
  }
  editErrors.goals = '';
  if (allGoals.value.length > 0 && (editForm.goals.length < 2 || editForm.goals.length > 3)) {
    editErrors.goals = 'Select between 2 and 3 goals';
  }

  if (editErrors.name || editErrors.email || editErrors.goals) return;

  saving.value = true;
  editMessage.value = '';
  editError.value = false;

  try {
    const payload = {
      name: editForm.name.trim(),
      email: editForm.email.trim(),
      language: editForm.language,
      goals: editForm.goals,
    };

    const res = await fetch('/api/profile', {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${getStoredToken()}` },
      body: JSON.stringify(payload),
    });

    const json = await res.json();

    if (!res.ok) {
      editError.value = true;
      editMessage.value = json.message || 'Failed to save profile.';
      return;
    }

    updateStoredUser(json.user);
    showEdit.value = false;
  } catch (e) {
    editError.value = true;
    editMessage.value = 'Connection error. Please try again.';
  } finally {
    saving.value = false;
  }
};
</script>
