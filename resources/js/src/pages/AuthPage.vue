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
            <h1 class="text-2xl font-semibold text-slate-900">Welcome back</h1>
            <p class="text-sm text-slate-500">Sign in to continue your habit flow</p>
          </div>
        </div>
// Temporary navigation
        <div class="mt-6 rounded-[28px] border border-dashed border-emerald-200 bg-emerald-50/60 p-4">
          <p class="text-sm font-medium text-emerald-800">Temporary navigation</p>
          <p class="mt-1 text-sm text-emerald-700">Use this launcher to open any page without logging in.</p>
          <div class="mt-4 grid grid-cols-2 gap-2">
            <UiButton href="/dashboard" variant="secondary" class="w-full">Dashboard</UiButton>
            <UiButton href="/habits" variant="secondary" class="w-full">Habits</UiButton>
            <UiButton href="/mood" variant="secondary" class="w-full">Mood</UiButton>
            <UiButton href="/calendar" variant="secondary" class="w-full">Calendar</UiButton>
            <UiButton href="/statistics" variant="secondary" class="w-full">Statistics</UiButton>
            <UiButton href="/profile" variant="secondary" class="w-full">Profile</UiButton>
          </div>
        </div>

        <form class="mt-8 space-y-4">
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
            <input type="email" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="chahd@example.com">
          </label>
          <label class="block">
            <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
            <input type="password" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none transition focus:border-emerald-400 focus:bg-white" placeholder="••••••••">
          </label>
          <div class="flex items-center justify-between gap-4 text-sm">
            <label class="flex items-center gap-2 text-slate-600"><input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-500"> Remember me</label>
            <a href="#" class="font-medium text-emerald-700 hover:text-emerald-800">Forgot password?</a>
          </div>
          <UiButton class="w-full">Login</UiButton>
          <UiButton variant="secondary" class="w-full">
            <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="M21.35 11.1h-9.18v2.9h5.26c-.23 1.36-1.52 3.99-5.26 3.99A5.82 5.82 0 0 1 6.33 12a5.82 5.82 0 0 1 5.84-5.99c1.66 0 2.77.71 3.4 1.31l2.32-2.24C16.4 3.7 14.49 2.8 12 2.8 6.88 2.8 2.7 6.98 2.7 12s4.18 9.2 9.3 9.2c5.33 0 8.86-3.74 8.86-9 0-.61-.07-1.09-.18-1.1z"/></svg>
            Continue with Google
          </UiButton>
          <p class="text-center text-sm text-slate-500">New to Khotwa? <a href="#" class="font-medium text-emerald-700 hover:text-emerald-800">Create an account</a></p>
        </form>
      </section>
    </div>
  </div>
</template>

<script setup>
import { Leaf } from 'lucide-vue-next';
import UiButton from '../components/UiButton.vue';
</script>
