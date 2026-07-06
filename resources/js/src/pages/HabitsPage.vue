<template>
  <AppShell>
    <template #header>
      <TopBar eyebrow="Habits" title="My Habits">
        <button @click="openModal" class="rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 hover:bg-emerald-100">Add Habit</button>
      </TopBar>
    </template>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
      <div v-for="col in columnsDef" :key="col.key" class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
          <div class="h-3 w-3 rounded-full" :class="col.dotClass"></div>
          <h2 class="font-semibold text-slate-900">{{ col.title }}</h2>
          <span class="ml-auto rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">{{ getList(col.key).length }}</span>
        </div>
        <div
          class="min-h-[160px] rounded-3xl border-2 border-dashed p-3 transition-colors"
          :class="col.borderClass"
        >
          <draggable
            :model-value="getList(col.key)"
            @update:model-value="(val) => setList(col.key, val)"
            group="habits"
            :handle="'.drag-handle'"
            item-key="id"
            :data-column="col.key"
            @change="(evt) => onChange(col.key, evt)"
          >
            <template #item="{ element }">
              <div class="mb-3 last:mb-0">
                <HabitCard
                  :habit="element"
                  :completed="!!element._completed"
                  @toggle="handleToggle"
                />
              </div>
            </template>
          </draggable>
        </div>
      </div>
    </div>

    <AddHabitModal :is-open="isModalOpen" @close="closeModal" @habit-created="onHabitCreated" />
  </AppShell>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import draggable from 'vuedraggable';
import AppShell from '../components/AppShell.vue';
import TopBar from '../components/TopBar.vue';
import HabitCard from '../components/HabitCard.vue';
import AddHabitModal from '../components/AddHabitModal.vue';
import { getStoredToken } from '../utils/auth';

const todoHabits = ref([]);
const doingHabits = ref([]);
const doneHabits = ref([]);
const isModalOpen = ref(false);

const columnsDef = [
  { key: 'todo', title: 'To Do', dotClass: 'bg-slate-400', borderClass: 'border-slate-100 hover:border-slate-200' },
  { key: 'doing', title: 'Doing', dotClass: 'bg-amber-400', borderClass: 'border-amber-100 hover:border-amber-200' },
  { key: 'done', title: 'Done', dotClass: 'bg-emerald-400', borderClass: 'border-emerald-100 hover:border-emerald-200' },
];

const getList = (key) => {
  if (key === 'todo') return todoHabits.value;
  if (key === 'doing') return doingHabits.value;
  return doneHabits.value;
};

const setList = (key, val) => {
  if (key === 'todo') todoHabits.value = val;
  else if (key === 'doing') doingHabits.value = val;
  else doneHabits.value = val;
};

const removeFromCurrentList = (habit) => {
  [todoHabits, doingHabits, doneHabits].forEach(ref => {
    const idx = ref.value.indexOf(habit);
    if (idx !== -1) ref.value.splice(idx, 1);
  });
};

let snapshot = null;

const saveSnapshot = () => {
  const snap = (list) => list.map(h => ({ id: h.id, status: h.status, _completed: h._completed }));
  snapshot = {
    todo: snap(todoHabits.value),
    doing: snap(doingHabits.value),
    done: snap(doneHabits.value),
  };
};

const restoreSnapshot = () => {
  if (!snapshot) return;
  const all = [...todoHabits.value, ...doingHabits.value, ...doneHabits.value];
  const map = new Map(all.map(h => [h.id, h]));
  const restore = (list, snapList) => {
    list.length = 0;
    snapList.forEach(s => {
      const h = map.get(s.id);
      if (h) { h.status = s.status; h._completed = s._completed; list.push(h); }
    });
  };
  restore(todoHabits.value, snapshot.todo);
  restore(doingHabits.value, snapshot.doing);
  restore(doneHabits.value, snapshot.done);
  snapshot = null;
};

const groupHabits = (habits) => {
  const todo = [], doing = [], done = [];
  habits.forEach(h => {
    if (h.completed_today) {
      h._completed = true;
      done.push(h);
    } else if (h.status === 'doing') {
      h._completed = false;
      doing.push(h);
    } else {
      h._completed = false;
      todo.push(h);
    }
  });
  todoHabits.value = todo;
  doingHabits.value = doing;
  doneHabits.value = done;
};

const fetchHabits = async () => {
  const token = getStoredToken();
  try {
    const response = await fetch('/api/habits', {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (response.ok && data.habits) {
      groupHabits(data.habits);
    }
  } catch (error) {
    console.error('Failed to fetch habits:', error);
  }
};

const onChange = async (targetStatus, evt) => {
  if (!evt.added) return;
  const habit = evt.added.element;
  const fromStatus = habit.status;
  if (fromStatus === targetStatus) return;

  saveSnapshot();
  habit.status = targetStatus;
  if (targetStatus === 'done') habit._completed = true;
  else if (fromStatus === 'done') habit._completed = false;

  try {
    const token = getStoredToken();
    if (targetStatus === 'done' || fromStatus === 'done') {
      const response = await fetch(`/api/habits/${habit.id}/toggle`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Request failed');
      habit._completed = data.completed;
      habit.status = data.habit.status;
    } else {
      const response = await fetch(`/api/habits/${habit.id}`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: JSON.stringify({ status: targetStatus }),
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'Request failed');
    }
  } catch (error) {
    console.error('Failed to update habit:', error);
    restoreSnapshot();
  }
};

const handleToggle = async (habit) => {
  const wasCompleted = habit._completed;
  saveSnapshot();

  removeFromCurrentList(habit);
  habit._completed = !wasCompleted;
  habit.status = habit._completed ? 'done' : 'todo';
  if (habit._completed) {
    doneHabits.value.push(habit);
  } else {
    todoHabits.value.unshift(habit);
  }

  try {
    const token = getStoredToken();
    const response = await fetch(`/api/habits/${habit.id}/toggle`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json();
    if (!response.ok || !data.success) throw new Error(data.message || 'Request failed');
    habit._completed = data.completed;
    habit.status = data.habit.status;
  } catch (error) {
    console.error('Failed to toggle habit:', error);
    restoreSnapshot();
  }
};

const openModal = () => { isModalOpen.value = true; };
const closeModal = () => { isModalOpen.value = false; };
const onHabitCreated = (habit) => {
  habit._completed = false;
  habit.status = habit.status || 'todo';
  todoHabits.value.unshift(habit);
};

onMounted(() => { fetchHabits(); });
</script>
