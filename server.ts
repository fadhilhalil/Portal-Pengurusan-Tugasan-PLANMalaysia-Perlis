import express from "express";
import { createServer as createViteServer } from "vite";
import path from "path";
import fs from "fs";

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(express.json());

  // Mock Database Path
  const DB_PATH = path.join(process.cwd(), "db.json");

  // Initialize DB if not exists
  if (!fs.existsSync(DB_PATH)) {
    const initialData = {
      users: [
        { id: "1", name: "Admin User", email: "admin@example.com", role: "Admin", password: "password" },
        { id: "2", name: "Staff 1", email: "staff1@example.com", role: "Staff", password: "password" },
        { id: "3", name: "Staff 2", email: "staff2@example.com", role: "Staff", password: "password" },
        { id: "4", name: "Staff 3", email: "staff3@example.com", role: "Staff", password: "password" },
        { id: "5", name: "Pengarah User", email: "pengarah@example.com", role: "Pengarah", password: "password" },
      ],
      projects: [],
      tasks: [],
      notifications: []
    };
    fs.writeFileSync(DB_PATH, JSON.stringify(initialData, null, 2));
  }

  const getDB = () => JSON.parse(fs.readFileSync(DB_PATH, "utf-8"));
  const saveDB = (data: any) => fs.writeFileSync(DB_PATH, JSON.stringify(data, null, 2));

  // API Routes
  app.get("/api/data", (req, res) => {
    res.json(getDB());
  });

  app.post("/api/users", (req, res) => {
    const db = getDB();
    const newUser = { ...req.body, id: Date.now().toString() };
    db.users.push(newUser);
    saveDB(db);
    res.json(newUser);
  });

  app.post("/api/projects", (req, res) => {
    const db = getDB();
    const newProject = { 
      ...req.body, 
      id: Date.now().toString(), 
      createdAt: new Date().toISOString(),
      status: req.body.status || "Dalam Perancangan"
    };
    db.projects.push(newProject);
    saveDB(db);
    res.json(newProject);
  });

  app.post("/api/tasks", (req, res) => {
    const db = getDB();
    const newTask = { ...req.body, id: Date.now().toString(), status: "Pending", createdAt: new Date().toISOString() };
    db.tasks.push(newTask);
    saveDB(db);
    res.json(newTask);
  });

  app.patch("/api/tasks/:id", (req, res) => {
    const db = getDB();
    const taskIndex = db.tasks.findIndex((t: any) => t.id === req.params.id);
    if (taskIndex > -1) {
      db.tasks[taskIndex] = { ...db.tasks[taskIndex], ...req.body };
      saveDB(db);
      res.json(db.tasks[taskIndex]);
    } else {
      res.status(404).json({ error: "Task not found" });
    }
  });

  app.patch("/api/projects/:id", (req, res) => {
    const db = getDB();
    const projectIndex = db.projects.findIndex((p: any) => p.id === req.params.id);
    if (projectIndex > -1) {
      db.projects[projectIndex] = { ...db.projects[projectIndex], ...req.body };
      saveDB(db);
      res.json(db.projects[projectIndex]);
    } else {
      res.status(404).json({ error: "Project not found" });
    }
  });

  app.delete("/api/projects/:id", (req, res) => {
    const db = getDB();
    db.projects = db.projects.filter((p: any) => p.id !== req.params.id);
    // Also delete tasks associated with this project
    db.tasks = db.tasks.filter((t: any) => t.projectId !== req.params.id);
    saveDB(db);
    res.json({ success: true });
  });

  app.delete("/api/tasks/:id", (req, res) => {
    const db = getDB();
    db.tasks = db.tasks.filter((t: any) => t.id !== req.params.id);
    saveDB(db);
    res.json({ success: true });
  });

  app.delete("/api/users/:id", (req, res) => {
    const db = getDB();
    db.users = db.users.filter((u: any) => u.id !== req.params.id);
    // Also delete tasks assigned to this user? Maybe just unassign them.
    // For now, let's just delete the user.
    saveDB(db);
    res.json({ success: true });
  });

  // Vite middleware for development
  if (process.env.NODE_ENV !== "production") {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: "spa",
    });
    app.use(vite.middlewares);
  } else {
    const distPath = path.join(process.cwd(), "dist");
    app.use(express.static(distPath));
    app.get("*", (req, res) => {
      res.sendFile(path.join(distPath, "index.html"));
    });
  }

  app.listen(PORT, "0.0.0.0", () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
