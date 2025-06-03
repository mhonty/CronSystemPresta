# CronSystem – PrestaShop Scheduled Task Manager

**CronSystem** is a PrestaShop module that allows you to register and execute scheduled tasks (cron jobs) every time a page is loaded, either in the Back Office or Front Office. It is ideal for lightweight, low-frequency automations that don't require external cron services.

## 🚀 Features

- Simple interface to manage scheduled tasks from the Back Office.
- Supports recurring and one-time executions.
- Can run tasks from both Front Office or Back Office visits.
- Visual status indicators for last execution result.
- Execution logic integrated via `hookActionDispatcher` (runs automatically on each request).

## 📦 Installation

1. Copy the module folder `cronsystem/` into your PrestaShop `/modules/` directory.
2. Go to **Back Office > Modules** and install **Cron System**.
3. A new section called **CronSystem** will appear under **Shop Parameters** with:
   - **Tareas** (Task list)
   - **Añadir** (Add new task)

## 🛠 Usage

### Adding a Task

Go to **Shop Parameters > CronSystem > Añadir** and fill in:

- **Nombre**: Name for your task.
- **Ruta tarea**: Internal URL to call, e.g. `module/mymodule/mycontroller?foo=bar`.
- **Frecuencia (s)**: Time in seconds between executions.
  - `0` means "execute only once".
- **¿Única ejecución?**: Disable task after first run (yes/no).
- **Back Office / Front Office**: Where the task is allowed to trigger.

### Notes on URLs

- Must start with `module/`, `admin/`, `index.php/` or `api/`.
- Must not contain `..` or disallowed characters in GET parameters.
- Relative to the base URI of the shop (auto-completed internally).

### Execution Behavior

Every time a page is loaded:
- Tasks with `activo = 1` and frequency criteria met will be executed.
- Tasks return `OK`, `KO`, or an HTTP status, which is logged.
- If the task is marked as "única", it will be deactivated after the first execution.

### Icons in List

In the **Tareas** tab:
- ✅ Green check: Task is active.
- ❌ Red cross: Task is inactive.
- Labels like `OK`, `KO`, `Timeout` show the result of the last execution.

## 🧩 Extending

Developers can modify the behavior by:

- Updating the `hookActionDispatcher` logic inside `CronSystem.php`
- Overriding how tasks are validated (`isRutaTareaValida`)
- Adding custom fields to the `cron_jobs` table and adapting `CronJob` model and controller accordingly.

## 🧽 Uninstallation

Uninstalling the module:
- Drops the `cron_jobs` table.
- Removes admin tabs created during install.

## ⚠️ Limitations

- Tasks are only triggered if a page is loaded (no background daemon).
- For high-frequency or precise scheduling, a real cron system is still recommended.

---

Created by [Pedro Montalvo](https://github.com/mhonty) · Licensed under MIT
