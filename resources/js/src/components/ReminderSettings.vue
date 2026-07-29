<template>
  <SectionCard>
    <template #header>
      <div><h2 class="text-lg font-semibold text-slate-900">Mood reminder</h2><p class="text-sm text-slate-500">Get a daily nudge to log your mood</p></div>
    </template>
    <div class="space-y-5">
      <div class="flex items-center justify-between">
        <div>
          <p id="reminder-enabled-label" class="text-sm font-medium text-slate-900">Enable reminder</p>
          <p class="text-xs text-slate-500">Receive a notification at your chosen time</p>
        </div>
        <button id="reminder-enabled" @click="toggleEnabled" type="button" role="switch" :aria-checked="form.reminder_enabled" aria-labelledby="reminder-enabled-label" class="relative h-6 w-11 rounded-full transition-colors" :class="form.reminder_enabled ? 'bg-emerald-500' : 'bg-slate-300'">
          <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform" :class="form.reminder_enabled ? 'translate-x-5' : 'translate-x-0'" />
        </button>
      </div>

      <div>
        <label for="reminder-time" class="mb-1 block text-sm font-medium text-slate-900">Reminder time</label>
        <input id="reminder-time" name="reminder_time" type="time" v-model="form.reminder_time" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" :disabled="!form.reminder_enabled" />
      </div>

      <div>
        <label for="reminder-timezone" class="mb-1 block text-sm font-medium text-slate-900">Timezone</label>
        <select id="reminder-timezone" name="timezone" v-model="form.timezone" class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100" :disabled="!form.reminder_enabled">
          <option v-for="tz in timezones" :key="tz" :value="tz">{{ tz }}</option>
        </select>
      </div>

      <div class="flex items-center gap-3">
        <UiButton @click="save" :disabled="saving">Save</UiButton>
        <span v-if="saved" class="text-sm text-emerald-600 font-medium">Settings saved!</span>
      </div>
    </div>
  </SectionCard>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import SectionCard from './SectionCard.vue';
import UiButton from './UiButton.vue';
import { getStoredToken } from '../utils/auth';

const saving = ref(false);
const saved = ref(false);

const form = reactive({
  reminder_enabled: true,
  reminder_time: '20:00',
  timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || 'Africa/Tunis',
});

const timezones = [
  'Africa/Tunis', 'Africa/Cairo', 'Africa/Casablanca',
  'Europe/Paris', 'Europe/London', 'Europe/Berlin', 'Europe/Madrid', 'Europe/Rome',
  'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
  'Asia/Dubai', 'Asia/Riyadh', 'Asia/Shanghai', 'Asia/Tokyo',
  'Australia/Sydney', 'Pacific/Auckland',
];

const toggleEnabled = () => {
  form.reminder_enabled = !form.reminder_enabled;
};

const save = async () => {
  saving.value = true;
  saved.value = false;
  const token = getStoredToken();
  try {
    const body = {
      reminder_enabled: form.reminder_enabled,
      reminder_time: form.reminder_time + ':00',
      timezone: form.timezone,
    };
    const res = await fetch('/api/reminder-settings', {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify(body),
    });
    const json = await res.json();
    if (res.ok && json.success) {
      saved.value = true;
      setTimeout(() => { saved.value = false; }, 3000);
    }
  } catch (e) {
    console.error('Failed to save reminder settings:', e);
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  const token = getStoredToken();
  try {
    const res = await fetch('/api/profile', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (res.ok && json.success && json.user) {
      form.reminder_enabled = json.user.reminder_enabled ?? true;
      form.reminder_time = (json.user.reminder_time ?? '20:00:00').slice(0, 5);
      form.timezone = json.user.timezone || form.timezone;
    }
  } catch (e) {
    /* silent */
  }
});
</script>
