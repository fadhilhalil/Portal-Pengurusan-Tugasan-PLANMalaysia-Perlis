import type {
  User,
  Project,
  Task,
  Notification,
} from "../types";

const STORAGE_KEY = "planmalaysia_portal_data";

export interface PortalData {
  users: User[];
  projects: Project[];
  tasks: Task[];
  notifications: Notification[];
}

const defaultData: PortalData = {
  users: [
   async createUser(user: Omit<User, "id">) {
  const data = getData();

  const loginId = user.loginId?.trim();

  if (!loginId) {
    throw new Error("ID pengguna diperlukan.");
  }

  const existingUser = data.users.find(
    (existing) =>
      existing.loginId?.toLowerCase() === loginId.toLowerCase()
  );

  if (existingUser) {
    throw new Error("ID pengguna telah digunakan.");
  }

  const newUser: User = {
    ...user,
    loginId,
    id: `user-${Date.now()}`,
  };

  data.users = [
    ...data.users,
    newUser,
  ];

  saveData(data);

  return newUser;
},
  ],

  projects: [
    {
      id: "project-001",
      name: "Pengurusan Data Perancangan Negeri Perlis",
      description:
        "Pengurusan dan pengemaskinian data perancangan bandar dan desa Negeri Perlis.",
      staffIds: ["staff-001"],
      createdAt: "2026-09-01T00:00:00.000Z",
      endDate: "2026-12-31T00:00:00.000Z",
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
      startDate: "2026-09-30T00:00:00.000Z",
      deadline: "2026-10-15T00:00:00.000Z",
      createdAt: "2026-09-30T00:00:00.000Z",
    },
    {
      id: "task-002",
      projectId: "project-001",
      title: "Penyediaan Laporan GIS",
      description:
        "Menyediakan laporan berkaitan data geospatial.",
      assignedTo: "staff-001",
      status: "Pending",
      startDate: "2026-09-30T00:00:00.000Z",
      deadline: "2026-10-20T00:00:00.000Z",
      createdAt: "2026-09-30T00:00:00.000Z",
    },
    {
      id: "task-003",
      projectId: "project-001",
      title: "Semakan Sistem Portal",
      description:
        "Semakan fungsi dan paparan portal pengurusan tugasan.",
      assignedTo: "staff-001",
      status: "Completed",
      startDate: "2026-09-25T00:00:00.000Z",
      deadline: "2026-09-30T00:00:00.000Z",
      createdAt: "2026-09-25T00:00:00.000Z",
    },
  ],

  notifications: [],
};

function cloneDefaultData(): PortalData {
  return JSON.parse(JSON.stringify(defaultData));
}

function getData(): PortalData {
  if (typeof window === "undefined") {
    return cloneDefaultData();
  }

  const stored = localStorage.getItem(STORAGE_KEY);

  if (!stored) {
    const initialData = cloneDefaultData();

    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify(initialData)
    );

    return initialData;
  }

  try {
    const parsed = JSON.parse(stored);

    return {
      users: Array.isArray(parsed.users)
        ? parsed.users
        : cloneDefaultData().users,

      projects: Array.isArray(parsed.projects)
        ? parsed.projects
        : cloneDefaultData().projects,

      tasks: Array.isArray(parsed.tasks)
        ? parsed.tasks
        : cloneDefaultData().tasks,

      notifications: Array.isArray(parsed.notifications)
        ? parsed.notifications
        : cloneDefaultData().notifications,
    };
  } catch {
    const resetData = cloneDefaultData();

    localStorage.setItem(
      STORAGE_KEY,
      JSON.stringify(resetData)
    );

    return resetData;
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

  // =========================
  // GENERAL
  // =========================

  async getData() {
    return getData();
  },

  async resetData() {
    const data = cloneDefaultData();
    saveData(data);
    return data;
  },

  // =========================
  // USERS
  // =========================

  async createUser(user: Omit<User, "id">) {
    const data = getData();

    const newUser: User = {
      ...user,
      id: `user-${Date.now()}`,
    };

    data.users = [
      ...data.users,
      newUser,
    ];

    saveData(data);

    return newUser;
  },

  async updateUser(
    id: string,
    updates: Partial<User>
  ) {
    const data = getData();

    data.users = data.users.map((user) =>
      user.id === id
        ? { ...user, ...updates }
        : user
    );

    saveData(data);

    return data.users.find(
      (user) => user.id === id
    );
  },

  async deleteUser(id: string) {
    const data = getData();

    data.users = data.users.filter(
      (user) => user.id !== id
    );

    data.projects = data.projects.map(
      (project) => ({
        ...project,
        staffIds: project.staffIds.filter(
          (staffId) => staffId !== id
        ),
      })
    );

    data.tasks = data.tasks.filter(
      (task) => task.assignedTo !== id
    );

    data.notifications =
      data.notifications.filter(
        (notification) =>
          notification.userId !== id
      );

    saveData(data);

    return true;
  },

  // =========================
  // PROJECTS / TUGASAN UTAMA
  // =========================

  async createProject(
    project: Omit<Project, "id">
  ) {
    const data = getData();

    const newProject: Project = {
      ...project,
      id: `project-${Date.now()}`,
    };

    data.projects = [
      ...data.projects,
      newProject,
    ];

    saveData(data);

    return newProject;
  },

  async updateProject(
    id: string,
    updates: Partial<Project>
  ) {
    const data = getData();

    data.projects = data.projects.map(
      (project) =>
        project.id === id
          ? {
              ...project,
              ...updates,
            }
          : project
    );

    saveData(data);

    return data.projects.find(
      (project) => project.id === id
    );
  },

  async deleteProject(id: string) {
    const data = getData();

    data.projects =
      data.projects.filter(
        (project) => project.id !== id
      );

    // Padam sub-tugasan yang berkaitan
    data.tasks =
      data.tasks.filter(
        (task) => task.projectId !== id
      );

    saveData(data);

    return true;
  },

  // =========================
  // SUB-TUGASAN
  // =========================

  async createTask(
    task: Omit<
      Task,
      "id" | "createdAt" | "status"
    >
  ) {
    const data = getData();

    const newTask: Task = {
      ...task,
      id: `task-${Date.now()}`,
      status: "Pending",
      createdAt:
        new Date().toISOString(),
    };

    data.tasks = [
      ...data.tasks,
      newTask,
    ];

    saveData(data);

    return newTask;
  },

  async updateTask(
    id: string,
    updates: Partial<Task>
  ) {
    const data = getData();

    data.tasks = data.tasks.map(
      (task) =>
        task.id === id
          ? {
              ...task,
              ...updates,
            }
          : task
    );

    saveData(data);

    return data.tasks.find(
      (task) => task.id === id
    );
  },

  async deleteTask(id: string) {
    const data = getData();

    data.tasks =
      data.tasks.filter(
        (task) => task.id !== id
      );

    saveData(data);

    return true;
  },

  // =========================
  // NOTIFICATIONS
  // =========================

  async createNotification(
    notification: Omit<
      Notification,
      "id" | "createdAt"
    >
  ) {
    const data = getData();

    const newNotification: Notification = {
      ...notification,
      id: `notification-${Date.now()}`,
      createdAt:
        new Date().toISOString(),
    };

    data.notifications = [
      ...data.notifications,
      newNotification,
    ];

    saveData(data);

    return newNotification;
  },

  async markNotificationAsRead(
    id: string
  ) {
    const data = getData();

    data.notifications =
      data.notifications.map(
        (notification) =>
          notification.id === id
            ? {
                ...notification,
                read: true,
              }
            : notification
      );

    saveData(data);

    return true;
  },

  async deleteNotification(
    id: string
  ) {
    const data = getData();

    data.notifications =
      data.notifications.filter(
        (notification) =>
          notification.id !== id
      );

    saveData(data);

    return true;
  },
};
