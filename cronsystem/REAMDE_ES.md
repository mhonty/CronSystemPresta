# CronSystem – Gestor de tareas programadas para PrestaShop

**CronSystem** es un módulo para PrestaShop que permite registrar y ejecutar tareas programadas (cron jobs) cada vez que se carga una página, ya sea en el Back Office o en el Front Office. Es ideal para automatizaciones ligeras o de baja frecuencia que no requieren un sistema externo de cron.

## 🚀 Características

- Interfaz sencilla para gestionar tareas programadas desde el Back Office.
- Soporta ejecuciones recurrentes o únicas.
- Posibilidad de ejecutar tareas desde visitas al Front Office o Back Office.
- Indicadores visuales del estado de la última ejecución.
- Lógica de ejecución integrada mediante `hookActionDispatcher`.

## 📦 Instalación

1. Copia la carpeta `cronsystem/` dentro del directorio `/modules/` de tu instalación de PrestaShop.
2. Accede al **Back Office > Módulos** e instala **Cron System**.
3. Aparecerá una nueva sección **CronSystem** dentro de **Parámetros de la tienda** con:
   - **Tareas** (Listado de tareas)
   - **Añadir** (Formulario para añadir nueva tarea)

## 🛠 Uso

### Añadir una tarea

Ve a **Parámetros de la tienda > CronSystem > Añadir** y rellena:

- **Nombre**: Nombre identificativo para la tarea.
- **Ruta tarea**: URL interna a ejecutar, por ejemplo `module/mimodulo/micontroller?foo=bar`.
- **Frecuencia (s)**: Tiempo en segundos entre ejecuciones.
  - `0` significa "ejecutar solo una vez".
- **¿Única ejecución?**: Si debe ejecutarse una sola vez.
- **Back Office / Front Office**: Indica desde dónde puede activarse la tarea.

### Notas sobre la ruta

- Debe comenzar con `module/`, `admin/`, `index.php/` o `api/`.
- No puede contener `..` ni caracteres peligrosos en los parámetros.
- Es relativa a la base del sitio, se completa automáticamente.

### Comportamiento de ejecución

Cada vez que se carga una página:
- Se ejecutan las tareas activas cuya frecuencia se haya cumplido.
- El resultado (`OK`, `KO`, HTTP 4xx, 5xx, etc.) queda registrado.
- Si la tarea es única, se desactiva tras ejecutarse.

### Iconos en el listado

En la pestaña **Tareas**:
- ✅ Check verde: Tarea activa.
- ❌ Cruz roja: Tarea inactiva.
- Etiquetas como `OK`, `KO`, `Timeout` muestran el resultado de la última ejecución.

## 🧩 Extensión y desarrollo

Los desarrolladores pueden adaptar la lógica:

- Modificando el `hookActionDispatcher` en `CronSystem.php`.
- Ajustando la validación de rutas (`isRutaTareaValida`).
- Añadiendo campos a la tabla `cron_jobs` y actualizando el modelo `CronJob` y los controladores.

## 🧽 Desinstalación

Al desinstalar el módulo:
- Se elimina la tabla `cron_jobs`.
- Se borran las pestañas de administración creadas.

## ⚠️ Limitaciones

- Las tareas solo se ejecutan si alguien accede al sitio.
- No sirve para tareas de alta frecuencia ni ejecución precisa. Para eso, usa un cron real del sistema.

---

Creado por [Pedro Montalvo](https://github.com/mhonty) · Licencia MIT
