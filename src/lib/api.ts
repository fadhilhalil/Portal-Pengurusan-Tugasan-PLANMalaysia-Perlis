import type {
  User,
  Project,
  Task,
  Notification,
} from "../types";

const STORAGE_KEY = "planmalaysia_portal_data";

interface PortalData {
  users: User[];
  projects: Project[];
  tasks: Task[];
  notifications: Notification[];
}

const defaultData: PortalData = {
  users: [
    {
      id: "admin-001",
      name: "Administrator",
      email: "admin@planmalaysia.gov.my",
      role: "Admin",
      password: "admin123",
    },
    {
      id: "staff-001",
      name: "Unit Bank Data",
      email: "bankdata@planmalaysia.gov.my",
      role: "Staff",
      password: "staff123",
    },
    {
      id: "pengarah-001",
      name: "Pengarah PLANMalaysia Perlis",
      email: "pengarah@planmalaysia.gov.my",
      role: "Pengarah",
      password: "pengarah123",
    },
  ],

  projects: [
    {
      id: "project-001",
      name: "Pengurusan Data Perancangan Negeri Perlis",
      description:
        "Pengurusan dan pengemaskinian data perancangan bandar dan desa Negeri Perlis.",
      staffIds: ["staff-001"],
      createdAt: "2026-09-01",
      endDate: "2026-12-31",
      status: "Dalam Proses",
    },
  ],

  tasks: [
    {
      id: "task-001",
      projectId: "project-001",
      title: "Kemaskini Data Perancangan Negeri Perlis",
      description:
        "Mengemaskini maklumat data perancangan bandar dan desa.",
      assignedTo: "staff-001",
      status: "In Progress",
      startDate: "2026-09-30",
      deadline: "2026-10-15",
      createdAt: "2026-09-30",
    },
    {
      id: "task-002",
      projectId: "project-001",
      title: "Penyediaan Laporan GIS",
      description:
        "Menyediakan laporan berkaitan data geospatial.",
      assignedTo: "staff-001",
      status: "Pending",
      startDate: "2026-09-30",
      deadline: "2026-10-20",
      createdAt: "2026-09-30",
    },
    {
      id: "task-003",
      projectId: "project-001",
      title: "Semakan Sistem Portal",
      description:
        "Semakan fungsi dan paparan portal pengurusan tugasan.",
      assignedTo: "staff-001",
      status: "Completed",
      startDate: "2026-09-25",
      deadline: "2026-09-30",
      createdAt: "2026-09-25",
    },
  ],

  notifications: [],
};

function getData(): PortalData {
  if (typeof window === "undefined") {
    return defaultData;
  }

  const stored = localStorage.getItem(STORAGE_KEY);

  if (!stored) {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify(defaultData)
    );

    return defaultData;
  }

  try {
    const parsed = JSON.parse(stored);

    return {
      users: Array.isArray(parsed.users)
        ? parsed.users
        : defaultData.users,

      projects: Array.isArray(parsed.projects)
        ? parsed.projects
        : defaultData.projects,

      tasks: Array.isArray(parsed.tasks)
        ? parsed.tasks
        : defaultData.tasks,

      notifications: Array.isArray(parsed.notifications)
        ? parsed.notifications
        : defaultData.notifications,
    };
  } catch {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify(defaultData)
    );

    return defaultData;
  }
}

function saveData(data: PortalData) {
  if (typeof window !== "undefined") {
    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify(data)
    );
  }
}

export const api = {
  async getData() {
    return getData();
  },

  async createTask(
    task: Omit<Task, "id" | "createdAt">
  ) {
    const data = getData();

    const newTask: Task = {
      ...task,
      id: Date.now().toString(),
      createdAt: new Date().toISOString(),
    };

    data.tasks = [...data.tasks, newTask];

    saveData(data);

    return newTask;
  },

  async updateTask(
    id: string,
    updates: Partial<Task>
  ) {
    const data = getData();

    data.tasks = data.tasks.map((task) =>
      task.id === id
        ? { ...task, ...updates }
        : task
    );

    saveData(data);

    return data.tasks.find(
      (task) => task.id === id
    );
  },

  async deleteTask(id: string) {
    const data = getData();

    data.tasks = data.tasks.filter(
      (task) => task.id !== id
    );

    saveData(data);

    return true;
  },
};
