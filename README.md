# CronSystemPresta

**CronSystemPresta** is a lightweight PrestaShop module that lets you create and manage scheduled tasks (cron jobs) that run automatically on every page load. It does not require any external cron configuration on your server.

## 🔧 Features

- Add custom cron tasks with URL and frequency
- Optional one-time execution
- Enable/disable tasks easily
- Back office and front office execution modes
- Execution status logging (OK / KO / timeout / HTTP error)

## 📦 Installation

1. Download or clone this repository
2. Zip the folder as `cronsystem.zip` (make sure the folder `cronsystem/` is at the root)
3. Upload the module via PrestaShop Back Office > Modules > Module Manager > Upload a module
4. Install it as any other module

> 📝 Requires PrestaShop 1.7 or 8.x

## 🚀 Usage

1. Go to `Advanced Parameters > CronSystem`
2. Click "Add new task"
3. Fill in:
   - Task name
   - URL (e.g. `module/yourmodule/controller`)
   - Frequency in seconds (0 for one-time execution)
   - Execution context (Back/Front)
4. Save. The task will be triggered on each page load accordingly.

> For example, setting frequency = `86400` will run the task once per day.

## 🌍 Translations

The module includes translations for:
- 🇪🇸 Spanish
- 🇬🇧 English
- 🇫🇷 French
- 🇮🇹 Italian
- 🇩🇪 German
- 🇵🇹 Portuguese
- 🇷🇺 Russian
- 🇵🇱 Polish
- 🇸🇪 Swedish
- 🇷🇴 Romanian
- 🇳🇱 Dutch
- 🇺🇦 Ukrainian
- 🇨🇳 Chinese
- 🇯🇵 Japanese
- 🇰🇷 Korean
- 🇸🇦 Arabic
- ... and more

## 🤝 Contributing

Feel free to fork this repository, make improvements and submit a pull request. Bug reports and feature requests are welcome!

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

🛠 Developed by Pedro Montalvo  
📬 Contact: [github.com/mhonty](https://github.com/mhonty)

