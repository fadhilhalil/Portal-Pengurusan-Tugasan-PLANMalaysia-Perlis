export type Role = "Admin" | "Staff" | "Pengarah";

export interface User {
  id: string;
  name: string;
  email: string;
  role: Role;
  password?: string;
}

export type ProjectStatus = "Belum Selesai" | "Dalam Proses" | "Dalam Perancangan" | "Selesai";

export interface Project {
  id: string;
  name: string;
  description: string;
  staffIds: string[];
  createdAt: string;
  endDate: string;
  status: ProjectStatus;
}

export interface Task {
  id: string;
  projectId: string;
  title: string;
  description: string;
  assignedTo: string; // User ID
  status: "Pending" | "In Progress" | "Completed";
  startDate: string;
  deadline: string;
  createdAt: string;
}

export interface Notification {
  id: string;
  userId: string;
  message: string;
  type: "Deadline" | "Assignment";
  read: boolean;
  createdAt: string;
}
