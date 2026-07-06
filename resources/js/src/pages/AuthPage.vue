<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(76,175,80,0.16),transparent_28%),linear-gradient(180deg,#ffffff_0%,#f7fbf8_100%)] px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto grid min-h-[calc(100vh-4rem)] max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
      <section class="hidden overflow-hidden rounded-[36px] border border-emerald-100 bg-white p-8 shadow-[0_26px_80px_rgba(15,23,42,0.08)] lg:block">
        <div class="flex h-full min-h-[620px] flex-col justify-between rounded-[30px] bg-[linear-gradient(160deg,#ecfdf3_0%,#ffffff_55%,#dff5e5_100%)] p-8">
          <div class="inline-flex w-fit items-center gap-3 rounded-full bg-white/80 px-4 py-2 text-sm font-medium text-emerald-700 shadow-sm">
            <Leaf class="h-4 w-4" />
            Khotwa
          </div>
          <div>
            <h2 class="max-w-md text-5xl font-semibold tracking-tight text-slate-900">Build small rituals that make your day feel lighter.</h2>
            <p class="mt-5 max-w-lg text-lg leading-8 text-slate-600">Track habits, moods, and weekly momentum in one calm workspace designed for clarity.</p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div class="rounded-[28px] bg-white p-5 shadow-sm">
              <p class="text-sm text-slate-500">Weekly streak</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">12 days</p>
            </div>
            <div class="rounded-[28px] bg-white p-5 shadow-sm">
              <p class="text-sm text-slate-500">Mood score</p>
              <p class="mt-2 text-3xl font-semibold text-slate-900">8.6/10</p>
            </div>
          </div>
        </div>
      </section>

      <section class="mx-auto w-full max-w-md rounded-[36px] border border-emerald-100 bg-white p-6 shadow-[0_26px_80px_rgba(15,23,42,0.08)] sm:p-8">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white"><Leaf class="h-6 w-6" /></div>
          <div>
            <h1 class="text-2xl font-semibold text-slate-900">{{ isRegister ? 'Create account' : 'Welcome back' }}</h1>
            <p class="text-sm text-slate-500">{{ isRegister ? 'Fill your details to create your account' : 'Sign in to continue your habit flow' }}</p>
          </div>
        </div>

        <div class="mt-6 inline-flex rounded-2xl bg-slate-100 p-1 text-sm font-medium">
          <button type="button" class="rounded-xl px-4 py-2 transition" :class="!isRegister ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" @click="isRegister = false">Login</button>
          <button type="button" class="rounded-xl px-4 py-2 transition" :class="isRegister ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500'" @click="isRegister = true">Create an account</button>
        </div>

        <div v-if="message" class="mt-4 rounded-2xl border px-4 py-3 text-sm" :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
          {{ message }}
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="submitForm">
          <label v-if="isRegister" class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Name</span>
            <input v-model="form.name" type="text" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="Your name">
          </label>
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
            <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="chahd@example.com">
          </label>
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
            <input v-model="form.password" type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="••••••••">
          </label>
          <label v-if="isRegister" class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Confirm password</span>
            <input v-model="form.password_confirmation" type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="••••••••">
          </label>
          <label v-if="isRegister" class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Language</span>
            <select v-model="form.language" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white">
              <option value="en">English</option>
              <option value="fr">Français</option>
              <option value="ar">العربية</option>
            </select>
          </label>
          <div class="flex items-center justify-between gap-4 text-sm">
            <label v-if="!isRegister" class="flex items-center gap-2 text-slate-600"><input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500"> Remember me</label>
            <a href="#" class="font-medium text-emerald-700 hover:text-emerald-800">Forgot password?</a>
          </div>
          <UiButton class="w-full" :disabled="loading">{{ loading ? 'Please wait...' : (isRegister ? 'Create account' : 'Login') }}</UiButton>
          <UiButton variant="secondary" class="w-full" type="button" @click="goToSocial('google')">
            <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="M21.35 11.1h-9.18v2.9h5.26c-.23 1.36-1.52 3.99-5.26 3.99A5.82 5.82 0 0 1 6.33 12a5.82 5.82 0 0 1 5.84-5.99c1.66 0 2.77.71 3.4 1.31l2.32-2.24C16.4 3.7 14.49 2.8 12 2.8 6.88 2.8 2.7 6.98 2.7 12s4.18 9.2 9.3 9.2c5.33 0 8.86-3.74 8.86-9 0-.61-.07-1.09-.18-1.1z"/></svg>
            Continue with Google
          </UiButton>
          <UiButton variant="secondary" class="w-full" type="button" @click="goToSocial('apple')">
            Continue with Apple
          </UiButton>
          <p class="text-center text-sm text-slate-500">
            {{ isRegister ? 'Already have an account?' : 'New to Khotwa?' }}
            <button type="button" class="font-medium text-emerald-700 hover:text-emerald-800" @click="isRegister = !isRegister">
              {{ isRegister ? 'Login' : 'Create an account' }}
            </button>
          </p>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Leaf } from 'lucide-vue-next';
import UiButton from '../components/UiButton.vue';
import { saveAuthSession } from '../utils/auth';

const router = useRouter();
const route = useRoute();
const isRegister = ref(false);
const loading = ref(false);
const message = ref('');
const messageType = ref('success');

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  language: 'en',
});

const resetMessage = () => {
  message.value = '';
};

onMounted(async () => {
  const token = route.query.token;
  const error = route.query.error;

  if (error) {
    messageType.value = 'error';
    message.value = decodeURIComponent(error);
  } else if (token) {
    loading.value = true;
    try {
      const response = await fetch('/api/profile', {
        headers: {
          'Accept': 'application/json',
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await response.json();
      if (!response.ok || !data.success) {
        throw new Error(data.message || 'Authentication failed.');
      }
      saveAuthSession(data.user, token);
      await router.push('/dashboard');
    } catch (err) {
      messageType.value = 'error';
      message.value = err.message;
    } finally {
      loading.value = false;
    }
  }
});

const submitForm = async () => {
  loading.value = true;
  resetMessage();

  const endpoint = isRegister.value ? '/api/register' : '/api/login';
  const payload = isRegister.value
    ? {
        name: form.name,
        email: form.email,
        password: form.password,
        password_confirmation: form.password_confirmation,
        language: form.language,
      }
    : {
        email: form.email,
        password: form.password,
      };

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
      },
      body: JSON.stringify(payload),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message ?? 'An error occurred.');
    }

    saveAuthSession(data.user, data.token);

    messageType.value = 'success';
    message.value = isRegister.value ? 'Account created successfully.' : 'Login successful.';

    await router.push('/dashboard');
  } catch (error) {
    messageType.value = 'error';
    message.value = error.message;
  } finally {
    loading.value = false;
  }
};

const goToSocial = (provider) => {
  window.location.href = `/auth/${provider}/redirect`;
};
</script>
