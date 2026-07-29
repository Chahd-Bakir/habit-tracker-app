<template>
  <div class="min-h-screen bg-[radial-gradient(circle_at_top_right,rgba(76,175,80,0.16),transparent_28%),linear-gradient(180deg,#ffffff_0%,#f7fbf8_100%)] px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto flex min-h-[calc(100vh-4rem)] max-w-md items-center justify-center">
      <div class="w-full rounded-[36px] border border-emerald-100 bg-white p-6 shadow-[0_26px_80px_rgba(15,23,42,0.08)] sm:p-8">
        <div class="flex items-center gap-3">
          <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500 text-white">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
          </div>
          <div>
            <h1 class="text-2xl font-semibold text-slate-900">Admin access</h1>
            <p class="text-sm text-slate-500">Sign in with your admin account</p>
          </div>
        </div>

        <div v-if="message" class="mt-4 rounded-2xl border px-4 py-3 text-sm" :class="messageType === 'error' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700'">
          {{ message }}
        </div>

        <form class="mt-8 space-y-4" @submit.prevent="submitForm">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
            <input v-model="form.email" type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="admin@khotwa.app">
          </label>
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
            <input v-model="form.password" type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="••••••••">
          </label>
          <UiButton class="w-full" :disabled="loading || !form.email || !form.password">{{ loading ? 'Signing in...' : 'Sign in' }}</UiButton>
        </form>

        <p class="mt-6 text-center text-sm text-slate-500">
          <router-link to="/login" class="font-medium text-emerald-700 hover:text-emerald-800">Back to user login</router-link>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import UiButton from '../components/UiButton.vue';
import { saveAuthSession } from '../utils/auth';

const router = useRouter();
const loading = ref(false);
const message = ref('');
const messageType = ref('success');

const form = reactive({
  email: '',
  password: '',
});

const submitForm = async () => {
  loading.value = true;
  message.value = '';

  try {
    const response = await fetch('/api/admin/login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email: form.email, password: form.password }),
    });

    const data = await response.json();

    if (!response.ok) {
      messageType.value = 'error';
      message.value = data.message || 'An error occurred.';
      return;
    }

    saveAuthSession(data.user, data.token);
    await router.push('/admin/users');
  } catch (e) {
    messageType.value = 'error';
    message.value = 'Connection error. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>
