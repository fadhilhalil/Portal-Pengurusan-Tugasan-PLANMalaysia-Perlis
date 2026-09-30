export interface Task {
  id: string;
  title: string;
  description: string;
  status: 'Belum Mula' | 'Sedang Berjalan' | 'Selesai';
  priority: 'Rendah' | 'Sederhana' | 'Tinggi';
  assignee: string;
  dueDate: string;
  createdAt: string;
}

const STORAGE_KEY = 'planmalaysia_tasks';

const defaultTasks: Task[] = [
  {
    id: '1',
    title: 'Kemaskini Data Perancangan Negeri Perlis',
    description: 'Mengemaskini maklumat data perancangan bandar dan desa.',
    status: 'Sedang Berjalan',
    priority: 'Tinggi',
    assignee: 'Unit Bank Data',
    dueDate: '2026-10-15',
    createdAt: '2026-09-30',
  },
  {
    id: '2',
    title: 'Penyediaan Laporan GIS',
    description: 'Menyediakan laporan berkaitan data geospatial.',
    status: 'Belum Mula',
    priority: 'Sederhana',
    assignee: 'Unit GIS',
    dueDate: '2026-10-20',
    createdAt: '2026-09-30',
  },
  {
    id: '3',
    title: 'Semakan Sistem Portal',
    description: 'Semakan fungsi dan paparan portal pengurusan tugasan.',
    status: 'Selesai',
    priority: 'Rendah',
    assignee: 'Unit ICT',
    dueDate: '2026-09-30',
    createdAt: '2026-09-25',
  },
];

function getTasks(): Task[] {
  const stored = localStorage.getItem(STORAGE_KEY);

  if (stored) {
    try {
      return JSON.parse(stored);
    } catch {
      return defaultTasks;
    }
  }

  localStorage.setItem(STORAGE_KEY, JSON.stringify(defaultTasks));
  return defaultTasks;
}

function saveTasks(tasks: Task[]) {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(tasks));
}

export const api = {
  async getData() {
    return {
      tasks: getTasks(),
    };
  },

  async createTask(task: Omit<Task, 'id' | 'createdAt'>) {
    const tasks = getTasks();

    const newTask: Task = {
      ...task,
      id: Date.now().toString(),
      createdAt: new Date().toISOString(),
    };

    saveTasks([...tasks, newTask]);

    return newTask;
  },

  async updateTask(id: string, updates: Partial<Task>) {
    const tasks = getTasks();

    const updatedTasks = tasks.map((task) =>
      task.id === id ? { ...task, ...updates } : task
    );

    saveTasks(updatedTasks);

    return updatedTasks.find((task) => task.id === id);
  },

  async deleteTask(id: string) {
    const tasks = getTasks();

    const updatedTasks = tasks.filter((task) => task.id !== id);

    saveTasks(updatedTasks);

    return true;
  },
};
