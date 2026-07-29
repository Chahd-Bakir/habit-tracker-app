<template>
  <AdminLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-lg font-semibold text-slate-900">Habit Suggestions</h1>
          <p class="text-sm text-slate-500">Manage suggested habits for onboarding</p>
        </div>
        <button @click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition">+ New suggestion</button>
      </div>
    </template>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-medium">Icon</th>
            <th class="px-4 py-3 font-medium">Name</th>
            <th class="px-4 py-3 font-medium">Category</th>
            <th class="px-4 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="s in suggestions" :key="s.id" class="text-slate-700">
            <td class="px-4 py-3 text-xl">{{ s.icon || '-' }}</td>
            <td class="px-4 py-3 font-medium">{{ s.name }}</td>
            <td class="px-4 py-3 text-slate-500">{{ s.category?.name || '-' }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="openEdit(s)" class="mr-2 text-sm text-indigo-600 hover:text-indigo-800">Edit</button>
              <button @click="confirmDelete(s)" class="text-sm text-red-500 hover:text-red-700">Delete</button>
            </td>
          </tr>
          <tr v-if="suggestions.length === 0">
            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-400">No suggestions yet.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900">{{ editing ? 'Edit suggestion' : 'New suggestion' }}</h3>
        <form @submit.prevent="save" class="mt-4 space-y-4">
          <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
            <input v-model="form.name" maxlength="100" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition" :class="errors.name ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400'" placeholder="e.g. Morning jog" />
            <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
          </label>
          <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Icon (emoji)</span>
            <div class="flex items-center gap-3">
              <span class="flex h-10 w-10 items-center justify-center rounded-lg border text-xl" :class="form.icon ? 'border-indigo-200 bg-indigo-50' : 'border-slate-200 bg-slate-50 text-slate-300'">{{ form.icon || '?' }}</span>
              <input v-model="form.icon" placeholder="🧘" maxlength="10" class="flex-1 rounded-lg border px-3 py-2 text-sm outline-none transition" :class="errors.icon ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400'" />
            </div>
            <p v-if="errors.icon" class="mt-1 text-xs text-red-500">{{ errors.icon }}</p>
          </label>
          <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Category</span>
            <select v-model="form.category_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm outline-none focus:border-indigo-400">
              <option :value="null">— None —</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </label>
          <div class="flex justify-end gap-3">
            <button type="button" @click="showModal = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-600 hover:bg-slate-50 transition">Cancel</button>
            <button type="submit" :disabled="saving" class="rounded-lg px-4 py-2 text-sm font-medium text-white transition" :class="saving ? 'bg-indigo-400 cursor-not-allowed' : 'bg-indigo-600 hover:bg-indigo-700'">
              <span v-if="saving" class="flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" /><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" /></svg>
                Saving...
              </span>
              <span v-else>{{ editing ? 'Update' : 'Save' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AdminLayout from '../components/AdminLayout.vue';
import { getStoredToken } from '../utils/auth';

const suggestions = ref([]);
const categories = ref([]);
const showModal = ref(false);
const editing = ref(null);
const saving = ref(false);
const form = ref({ name: '', icon: '', category_id: null });
const errors = ref({ name: '', icon: '' });

const resetErrors = () => { errors.value = { name: '', icon: '' }; };

const validate = () => {
  resetErrors();
  let valid = true;
  if (!form.value.name.trim()) {
    errors.value.name = 'Suggestion name is required.';
    valid = false;
  } else if (form.value.name.trim().length > 100) {
    errors.value.name = 'Name must be 100 characters or less.';
    valid = false;
  }
  if (form.value.icon && form.value.icon.length > 10) {
    errors.value.icon = 'Icon must be 10 characters or less.';
    valid = false;
  }
  return valid;
};

const fetchData = async () => {
  const token = getStoredToken();
  const headers = { Accept: 'application/json', Authorization: `Bearer ${token}` };
  try {
    const [sRes, cRes] = await Promise.all([
      fetch('/api/admin/suggestions', { headers }),
      fetch('/api/admin/categories', { headers }),
    ]);
    const sJson = await sRes.json();
    const cJson = await cRes.json();
    if (sJson.success) suggestions.value = sJson.suggestions;
    if (cJson.success) categories.value = cJson.categories;
  } catch (e) {
    console.error('Fetch error:', e);
  }
};

const openCreate = () => {
  editing.value = null;
  form.value = { name: '', icon: '', category_id: null };
  resetErrors();
  showModal.value = true;
};

const openEdit = (s) => {
  editing.value = s;
  form.value = { name: s.name, icon: s.icon || '', category_id: s.category_id };
  resetErrors();
  showModal.value = true;
};

const save = async () => {
  if (!validate()) return;
  saving.value = true;
  const token = getStoredToken();
  const method = editing.value ? 'PUT' : 'POST';
  const url = editing.value ? `/api/admin/suggestions/${editing.value.id}` : '/api/admin/suggestions';
  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify({
        name: form.value.name.trim(),
        icon: form.value.icon || null,
        category_id: form.value.category_id,
      }),
    });
    const json = await res.json();
    if (json.success) {
      showModal.value = false;
      await fetchData();
    } else if (json.errors?.name) {
      errors.value.name = json.errors.name[0];
    } else if (json.errors?.icon) {
      errors.value.icon = json.errors.icon[0];
    }
  } catch (e) {
    console.error('Save error:', e);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (s) => {
  if (!confirm(`Delete "${s.name}"?`)) return;
  const token = getStoredToken();
  try {
    const res = await fetch(`/api/admin/suggestions/${s.id}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (json.success) await fetchData();
  } catch (e) {
    console.error('Delete error:', e);
  }
};

onMounted(fetchData);
</script>
