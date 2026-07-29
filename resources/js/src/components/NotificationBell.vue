<template>
  <div class="relative" ref="container">
    <button @click="toggle" type="button" aria-label="Notifications" class="relative rounded-full p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">
      <Bell class="h-5 w-5" />
      <span v-if="count > 0" class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ count > 9 ? '9+' : count }}</span>
    </button>

    <div v-if="open" class="absolute right-0 top-full z-50 mt-2 w-80 rounded-[24px] border border-slate-200 bg-white p-3 shadow-lg">
      <div class="mb-2 flex items-center justify-between px-1">
        <p class="text-sm font-semibold text-slate-900">Notifications</p>
        <button @click="open = false" type="button" aria-label="Close notifications" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition">
          <X class="h-4 w-4" />
        </button>
      </div>
      <div v-if="loading" class="py-6 text-center text-sm text-slate-400">Loading...</div>
      <div v-else-if="list.length === 0" class="py-6 text-center text-sm text-slate-400">No new notifications.</div>
      <div v-else class="max-h-72 space-y-1 overflow-y-auto">
        <div v-for="n in list" :key="n.id" class="group flex items-start gap-3 rounded-2xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50">
          <span class="mt-0.5 flex-shrink-0 text-lg">{{ n.data?.message?.includes('\u{1F319}') ? '\u{1F319}' : '\u{1F514}' }}</span>
          <div class="min-w-0 flex-1">
            <p class="break-words">{{ n.data?.message ?? 'You have a new notification.' }}</p>
            <p class="mt-0.5 text-xs text-slate-400">{{ timeAgo(n.created_at) }}</p>
          </div>
          <button @click="markRead(n.id)" type="button" class="flex-shrink-0 rounded-full p-1 text-slate-300 opacity-0 group-hover:opacity-100 hover:text-emerald-600 transition" title="Mark as read">
            <Check class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Bell, X, Check } from 'lucide-vue-next';
import { getStoredToken } from '../utils/auth';

const container = ref(null);
const open = ref(false);
const loading = ref(false);
const list = ref([]);
const count = computed(() => list.value.length);

let pollTimer = null;

const fetchNotifications = async () => {
  const token = getStoredToken();
  if (!token) return;
  try {
    const res = await fetch('/api/notifications', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (res.ok && json.success) list.value = json.notifications;
  } catch (e) {
    /* silent */
  }
};

const markRead = async (id) => {
  const token = getStoredToken();
  try {
    const res = await fetch(`/api/notifications/${id}/read`, {
      method: 'PATCH',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    if (res.ok) list.value = list.value.filter(n => n.id !== id);
  } catch (e) {
    /* silent */
  }
};

const toggle = () => {
  open.value = !open.value;
  if (open.value) fetchNotifications();
};

const timeAgo = (iso) => {
  const diff = Date.now() - new Date(iso).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 1) return 'just now';
  if (mins < 60) return `${mins}m ago`;
  const hrs = Math.floor(mins / 60);
  if (hrs < 24) return `${hrs}h ago`;
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
};

const onClickOutside = (e) => {
  if (open.value && container.value && !container.value.contains(e.target)) {
    open.value = false;
  }
};

onMounted(() => {
  fetchNotifications();
  pollTimer = setInterval(fetchNotifications, 60000);
  document.addEventListener('click', onClickOutside);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
  document.removeEventListener('click', onClickOutside);
});
</script>
