<template>
  <AdminLayout>
    <template #header>
      <TopBar eyebrow="Admin" title="Users" />
    </template>

    <div v-if="loading" class="py-12 text-center text-sm text-slate-400">Loading users...</div>
    <div v-else-if="users.length === 0" class="py-12 text-center text-sm text-slate-400">No users found.</div>
    <div v-else class="overflow-hidden rounded-xl border border-slate-200">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-medium">Name</th>
            <th class="px-4 py-3 font-medium">Email</th>
            <th class="px-4 py-3 font-medium">Roles</th>
            <th class="px-4 py-3 font-medium">Joined</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="u in users" :key="u.id" class="text-slate-700">
            <td class="px-4 py-3">{{ u.name }}</td>
            <td class="px-4 py-3">{{ u.email }}</td>
            <td class="px-4 py-3">
              <span v-for="r in roleList(u)" :key="r" class="mr-1 inline-block rounded-full px-2.5 py-0.5 text-xs font-medium" :class="r === 'admin' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600'">{{ r }}</span>
            </td>
            <td class="px-4 py-3 text-slate-500">{{ formatDate(u.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../components/AdminLayout.vue';
import TopBar from '../components/TopBar.vue';
import { getStoredToken } from '../utils/auth';

const loading = ref(false);
const users = ref([]);

const formatDate = (iso) => {
  if (!iso) return '-';
  return new Date(iso).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

const roleList = (u) => {
  if (!u.role) return u.roles || [];
  return [u.role, ...(u.roles || []).filter((r) => r !== u.role)];
};

onMounted(async () => {
  loading.value = true;
  try {
    const res = await fetch('/api/admin/users', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${getStoredToken()}` },
    });
    const json = await res.json();
    if (json.success) users.value = json.users;
  } catch (e) {
    console.error('Failed to load users:', e);
  } finally {
    loading.value = false;
  }
});
</script>
