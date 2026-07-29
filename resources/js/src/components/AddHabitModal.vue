<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="close">
    <div class="w-full max-w-md rounded-[36px] border border-emerald-100 bg-white p-6 shadow-[0_26px_80px_rgba(15,23,42,0.08)] sm:p-8">
      <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-slate-900">{{ isEditing ? 'Edit habit' : 'Add new habit' }}</h2>
        <button type="button" @click="close" class="rounded-full p-2 text-slate-500 hover:bg-slate-100">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div v-if="suggestions.length > 0 && !isEditing" class="mb-5">
        <p class="mb-2 text-sm font-medium text-slate-700">Quick add from suggestions</p>
        <div class="grid grid-cols-2 gap-2">
          <button v-for="s in suggestions" :key="s.id" type="button" @click="applySuggestion(s)" class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 text-left">
            <span class="text-lg">{{ s.icon || '📋' }}</span>
            <span class="min-w-0 flex-1 truncate font-medium">{{ s.name }}</span>
          </button>
        </div>
        <div class="my-4 flex items-center gap-3">
          <span class="h-px flex-1 bg-slate-200"></span>
          <span class="text-xs text-slate-400">or create your own</span>
          <span class="h-px flex-1 bg-slate-200"></span>
        </div>
      </div>

      <form @submit.prevent="submitForm" class="space-y-4">
        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Habit name *</span>
          <input v-model="form.title" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="e.g., Morning stretch">
          <span v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</span>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Icon *</span>
          <div class="grid grid-cols-6 gap-2">
            <button v-for="icon in icons" :key="icon" type="button" @click="form.icon = icon" class="flex h-10 w-10 items-center justify-center rounded-xl border transition" :class="form.icon === icon ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500 hover:border-emerald-300'">
              {{ icon }}
            </button>
          </div>
          <span v-if="errors.icon" class="mt-1 text-sm text-red-600">{{ errors.icon }}</span>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Color *</span>
          <div class="grid grid-cols-6 gap-2">
            <button v-for="color in colors" :key="color" type="button" @click="form.color = color" class="h-10 w-10 rounded-xl border transition" :class="form.color === color ? 'border-emerald-500 ring-2 ring-emerald-200' : 'border-slate-200 hover:border-emerald-300'" :style="{ backgroundColor: color }"></button>
          </div>
          <span v-if="errors.color" class="mt-1 text-sm text-red-600">{{ errors.color }}</span>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Frequency *</span>
          <select v-model="form.frequency" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="custom">Custom days</option>
          </select>
          <span v-if="errors.frequency" class="mt-1 text-sm text-red-600">{{ errors.frequency }}</span>
        </label>

        <label v-if="form.frequency === 'custom'" class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Select days</span>
          <div class="grid grid-cols-7 gap-2">
            <button v-for="day in days" :key="day.value" type="button" @click="toggleDay(day.value)" class="rounded-xl border px-2 py-2 text-xs font-medium transition" :class="form.custom_days?.includes(day.value) ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500 hover:border-emerald-300'">
              {{ day.label }}
            </button>
          </div>
          <span v-if="errors.custom_days" class="mt-1 text-sm text-red-600">{{ errors.custom_days }}</span>
        </label>

        <label class="block">
          <span class="mb-2 block text-sm font-medium text-slate-700">Reminder time (optional)</span>
          <input v-model="form.reminder_time" type="time" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white">
        </label>

        <div v-if="message" class="rounded-2xl border px-4 py-3 text-sm" :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
          {{ message }}
        </div>

        <UiButton class="w-full" :disabled="loading">{{ loading ? (isEditing ? 'Saving...' : 'Creating...') : (isEditing ? 'Save changes' : 'Create habit') }}</UiButton>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, watch, computed } from 'vue';
import UiButton from './UiButton.vue';
import { getStoredToken } from '../utils/auth';

const props = defineProps({
  isOpen: Boolean,
  habit: { type: Object, default: null },
});

const emit = defineEmits(['close', 'habitCreated', 'habitUpdated']);

const isEditing = computed(() => !!props.habit);

const loading = ref(false);
const message = ref('');
const messageType = ref('success');
const errors = reactive({});
const suggestions = ref([]);

const form = reactive({
  title: '',
  icon: '🏃',
  color: '#10b981',
  frequency: 'daily',
  custom_days: [],
  reminder_time: '',
});

const icons = ['🏃', '💧', '📚', '🧘', '🍎', '😴', '💪', '🎯', '🌱', '☀️', '🧠', '❤️'];
const colors = [
  '#10b981',
  '#3b82f6',
  '#ef4444',
];
const days = [
  { label: 'Mon', value: 'monday' },
  { label: 'Tue', value: 'tuesday' },
  { label: 'Wed', value: 'wednesday' },
  { label: 'Thu', value: 'thursday' },
  { label: 'Fri', value: 'friday' },
  { label: 'Sat', value: 'saturday' },
  { label: 'Sun', value: 'sunday' },
];

watch(() => props.isOpen, (open) => {
  if (open) {
    if (props.habit) {
      form.title = props.habit.title || '';
      form.icon = props.habit.icon || '🏃';
      form.color = props.habit.color || '#10b981';
      form.frequency = props.habit.frequency || 'daily';
      form.custom_days = [...(props.habit.custom_days || [])];
      form.reminder_time = props.habit.reminder_time || '';
    } else {
      resetForm();
    }
    Object.keys(errors).forEach(key => delete errors[key]);
    message.value = '';
    fetchSuggestions();
  }
});

const resetForm = () => {
  form.title = '';
  form.icon = '🏃';
  form.color = '#10b981';
  form.frequency = 'daily';
  form.custom_days = [];
  form.reminder_time = '';
};

const fetchSuggestions = async () => {
  const token = getStoredToken();
  try {
    const res = await fetch('/api/suggestions', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (json.success) suggestions.value = json.suggestions;
  } catch (e) {
    /* silent */
  }
};

const applySuggestion = (s) => {
  form.title = s.name;
  form.icon = s.icon || '🏃';
};

const close = () => {
  emit('close');
};

const toggleDay = (day) => {
  const index = form.custom_days.indexOf(day);
  if (index > -1) {
    form.custom_days.splice(index, 1);
  } else {
    form.custom_days.push(day);
  }
};

const validateForm = () => {
  Object.keys(errors).forEach(key => delete errors[key]);

  if (!form.title.trim()) {
    errors.title = 'Habit name is required';
  }

  if (!form.icon) {
    errors.icon = 'Icon is required';
  }

  if (!form.color) {
    errors.color = 'Color is required';
  }

  if (!form.frequency) {
    errors.frequency = 'Frequency is required';
  }

  if (form.frequency === 'custom' && (!form.custom_days || form.custom_days.length === 0)) {
    errors.custom_days = 'Please select at least one day';
  }

  return Object.keys(errors).length === 0;
};

const submitForm = async () => {
  if (!validateForm()) return;

  loading.value = true;
  message.value = '';

  const token = getStoredToken();
  const payload = {
    title: form.title,
    icon: form.icon,
    color: form.color,
    frequency: form.frequency,
    reminder_time: form.reminder_time || null,
  };

  if (form.frequency === 'custom') {
    payload.custom_days = form.custom_days;
  }

  try {
    const url = isEditing.value ? `/api/habits/${props.habit.id}` : '/api/habits';
    const method = isEditing.value ? 'PATCH' : 'POST';

    const response = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      if (data.errors) {
        Object.assign(errors, data.errors);
      } else {
        throw new Error(data.message || `Failed to ${isEditing.value ? 'update' : 'create'} habit`);
      }
      return;
    }

    if (isEditing.value) {
      emit('habitUpdated', data.habit);
    } else {
      emit('habitCreated', data.habit);
    }
    close();
  } catch (error) {
    messageType.value = 'error';
    message.value = error.message;
  } finally {
    loading.value = false;
  }
};
</script>
