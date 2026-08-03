<template>
  <AdminLayout>
    <template #header>
      <TopBar eyebrow="Admin" title="Habit Categories">
        <button @click="openCreate" class="rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 transition">+ New category</button>
      </TopBar>
    </template>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="px-4 py-3 font-medium">Name</th>
            <th class="px-4 py-3 font-medium">Suggestions count</th>
            <th class="px-4 py-3 font-medium text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="c in categories" :key="c.id" class="text-slate-700">
            <td class="px-4 py-3 font-medium">{{ c.name }}</td>
            <td class="px-4 py-3 text-slate-500">{{ c.suggestions_count ?? 0 }}</td>
            <td class="px-4 py-3 text-right">
              <button @click="openEdit(c)" class="mr-2 text-sm text-indigo-600 hover:text-indigo-800">Edit</button>
              <button @click="confirmDelete(c)" class="text-sm text-red-500 hover:text-red-700">Delete</button>
            </td>
          </tr>
          <tr v-if="categories.length === 0">
            <td colspan="3" class="px-4 py-8 text-center text-sm text-slate-400">No categories yet.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showModal = false">
      <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
        <h3 class="text-lg font-semibold text-slate-900">{{ editing ? 'Edit category' : 'New category' }}</h3>
        <form @submit.prevent="save" class="mt-4 space-y-4">
          <label class="block">
            <span class="mb-1 block text-sm font-medium text-slate-700">Name</span>
            <input v-model="form.name" maxlength="50" class="w-full rounded-lg border px-3 py-2 text-sm outline-none transition" :class="errors.name ? 'border-red-400 bg-red-50' : 'border-slate-300 focus:border-indigo-400'" placeholder="e.g. Fitness" />
            <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
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
import TopBar from '../components/TopBar.vue';
import { getStoredToken } from '../utils/auth';

const categories = ref([]);
const showModal = ref(false);
const editing = ref(null);
const saving = ref(false);
const form = ref({ name: '' });
const errors = ref({ name: '' });

const resetErrors = () => { errors.value = { name: '' }; };

const validate = () => {
  resetErrors();
  let valid = true;
  if (!form.value.name.trim()) {
    errors.value.name = 'Category name is required.';
    valid = false;
  } else if (form.value.name.trim().length > 50) {
    errors.value.name = 'Name must be 50 characters or less.';
    valid = false;
  }
  return valid;
};

const fetchData = async () => {
  const token = getStoredToken();
  try {
    const res = await fetch('/api/admin/categories', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const json = await res.json();
    if (json.success) categories.value = json.categories;
  } catch (e) {
    console.error('Fetch error:', e);
  }
};

const openCreate = () => {
  editing.value = null;
  form.value = { name: '' };
  resetErrors();
  showModal.value = true;
};

const openEdit = (c) => {
  editing.value = c;
  form.value = { name: c.name };
  resetErrors();
  showModal.value = true;
};

const save = async () => {
  if (!validate()) return;
  saving.value = true;
  const token = getStoredToken();
  const method = editing.value ? 'PUT' : 'POST';
  const url = editing.value ? `/api/admin/categories/${editing.value.id}` : '/api/admin/categories';
  try {
    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: JSON.stringify({ name: form.value.name.trim() }),
    });
    const json = await res.json();
    if (json.success) {
      showModal.value = false;
      await fetchData();
    } else if (json.errors?.name) {
      errors.value.name = json.errors.name[0];
    }
  } catch (e) {
    console.error('Save error:', e);
  } finally {
    saving.value = false;
  }
};

const confirmDelete = async (c) => {
  if (!confirm(`Delete category "${c.name}"?`)) return;
  const token = getStoredToken();
  try {
    const res = await fetch(`/api/admin/categories/${c.id}`, {
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
