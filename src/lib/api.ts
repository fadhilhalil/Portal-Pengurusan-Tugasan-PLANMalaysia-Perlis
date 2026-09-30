import { User, Project, Task, Role } from "../types";

export const api = {
  async getData() {
    const res = await fetch("/api/data");
    return res.json();
  },

  async createUser(user: Partial<User>) {
    const res = await fetch("/api/users", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(user),
    });
    return res.json();
  },

  async createProject(project: Partial<Project>) {
    const res = await fetch("/api/projects", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(project),
    });
    return res.json();
  },

  async createTask(task: Partial<Task>) {
    const res = await fetch("/api/tasks", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(task),
    });
    return res.json();
  },

  async updateTask(id: string, updates: Partial<Task>) {
    const res = await fetch(`/api/tasks/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updates),
    });
    return res.json();
  },

  async updateProject(id: string, updates: Partial<Project>) {
    const res = await fetch(`/api/projects/${id}`, {
      method: "PATCH",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(updates),
    });
    return res.json();
  },

  async deleteProject(id: string) {
    const res = await fetch(`/api/projects/${id}`, {
      method: "DELETE",
    });
    return res.json();
  },
  
  async deleteTask(id: string) {
    const res = await fetch(`/api/tasks/${id}`, {
      method: "DELETE",
    });
    return res.json();
  },

  async deleteUser(id: string) {
    const res = await fetch(`/api/users/${id}`, {
      method: "DELETE",
    });
    return res.json();
  }
};
