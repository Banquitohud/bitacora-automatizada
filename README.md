# 📋 Bitácora GSI

Aplicación web interna para la gestión, seguimiento y generación de reportes del área **GSI**.

La aplicación permite centralizar el registro del **flujo diario de casos**, administrar **proyectos y sus tareas**, realizar seguimiento de tiempos y estados, mantener trazabilidad mediante auditoría y generar automáticamente indicadores y reportes a partir de la información registrada.

Está diseñada inicialmente para ser utilizada por aproximadamente **4 usuarios dentro de una red local**, utilizando un PC como servidor interno.

---

## 🎯 Objetivo

La **Bitácora GSI** busca reemplazar el seguimiento manual de actividades mediante una herramienta centralizada que permita:

- Registrar casos del flujo diario.
- Asignar casos a analistas.
- Realizar seguimiento de estados.
- Controlar fechas límite y tiempos de atención.
- Registrar conceptos y resultados GSI.
- Gestionar proyectos especiales.
- Crear tareas dentro de proyectos.
- Relacionar casos con proyectos.
- Generar indicadores automáticamente.
- Consultar productividad.
- Mantener historial y trazabilidad.
- Generar reportes.
- Exportar información.
- Importar información histórica desde Excel/CSV.

La información ingresada por los usuarios alimenta automáticamente los indicadores y reportes del sistema.

---

# 🏢 Contexto GSI

La aplicación está orientada a las actividades del área **GSI**, especialmente aquellas relacionadas con:

- Gestión de accesos.
- Creación de usuarios.
- Modificación de accesos.
- Retiro de accesos.
- Solicitudes de acceso.
- Aplicaciones.
- Perfiles.
- Permisos.
- Grupos.
- Familias de grupos.
- Revisiones de riesgo.
- Casos masivos.
- Depuración de accesos.
- Normalización.
- Análisis de permisos.
- Proyectos especiales.

La estructura es configurable para permitir agregar nuevos tipos de solicitudes, estados, aplicaciones, perfiles, grupos y otros catálogos sin modificar directamente el código.

---

# 🚀 Funcionalidades principales

## 📊 Dashboard

El dashboard presenta automáticamente información como:

- Casos recibidos.
- Casos pendientes.
- Casos en análisis.
- Casos en gestión.
- Casos finalizados.
- Casos vencidos.
- Casos próximos a vencer.
- Casos por analista.
- Casos por aplicación.
- Casos por tipo de solicitud.
- Proyectos activos.
- Proyectos finalizados.
- Avance de proyectos.
- Actividad reciente.

Los indicadores se calculan directamente a partir de los registros existentes en la base de datos.

---

## 📥 Flujo Diario

Módulo destinado al registro y seguimiento de los casos que llegan diariamente al equipo GSI.

Cada caso puede contener información como:

- Número de caso / ticket.
- Fecha de recepción.
- Hora de recepción.
- Fecha límite.
- Solicitante.
- Usuario afectado.
- Cargo.
- Tipo de solicitud.
- Aplicación.
- Perfil solicitado.
- Permiso / transacción.
- Grupo / familia.
- Prioridad.
- Estado.
- Analista asignado.
- Fecha de inicio.
- Fecha de finalización.
- Concepto GSI.
- Resultado.
- Observaciones.
- Comentarios.

### Estados iniciales

- Recibido
- Pendiente
- En análisis
- En gestión
- En espera de información
- Escalado
- Finalizado
- No viable
- Cancelado

Los estados son configurables.

---

# 🚦 Control de tiempos

La aplicación permite realizar seguimiento de los tiempos de atención.

Se contemplan:

- Tiempo desde recepción hasta inicio.
- Tiempo de gestión.
- Tiempo total.
- Días abiertos.
- Casos vencidos.
- Casos próximos a vencer.

También existe un sistema visual de semáforo:

🟢 Dentro del tiempo  
🟡 Próximo a vencer  
🔴 Vencido  
⚪ Sin fecha límite

La configuración de SLA puede adaptarse posteriormente a las necesidades del área.

---

# 📁 Proyectos

La aplicación permite registrar actividades especiales que no necesariamente pertenecen al flujo diario.

Un proyecto puede contener:

- Nombre.
- Código.
- Descripción.
- Fecha de inicio.
- Fecha estimada de finalización.
- Fecha real de finalización.
- Responsable.
- Equipo.
- Estado.
- Prioridad.
- Porcentaje de avance.
- Observaciones.

### Estados de proyecto

- Planeado
- En ejecución
- En pausa
- En revisión
- Finalizado
- Cancelado

---

# ✅ Tareas de proyectos

Cada proyecto puede tener múltiples tareas.

Ejemplo:

**Proyecto:** Depuración Gestor Documental

**Tareas:**

- Recolección de información.
- Validación.
- Análisis.
- Depuración.
- Carga.
- Ejecución.
- Validación final.
- Cierre.

Cada tarea puede tener:

- Nombre.
- Descripción.
- Responsable.
- Estado.
- Prioridad.
- Fecha inicial.
- Fecha límite.
- Fecha de finalización.
- Porcentaje de avance.
- Comentarios.

El avance del proyecto puede calcularse automáticamente a partir de sus tareas.

---

# 🔗 Relación entre casos y proyectos

Los casos del flujo diario pueden asociarse opcionalmente a un proyecto.

Ejemplo:

```text
Caso #123456
        ↓
Proyecto: Normalización de accesos
```

Esto permite posteriormente conocer cuántos casos están relacionados con un proyecto determinado.

---

# 📈 Reportes

El sistema genera reportes automáticamente utilizando la información registrada.

Los reportes pueden filtrarse por:

- Fecha inicial.
- Fecha final.
- Analista.
- Estado.
- Tipo de solicitud.
- Aplicación.
- Prioridad.
- Proyecto.

### Reporte de flujo diario

Incluye:

- Total recibidos.
- Total finalizados.
- Total pendientes.
- Total en proceso.
- Total vencidos.
- Total por analista.

### Reporte de productividad

Por cada analista:

- Casos gestionados.
- Casos finalizados.
- Casos pendientes.
- Tiempo promedio.
- Tiempo total.

### Reporte por aplicación

Permite identificar la cantidad de casos asociados a cada aplicación.

### Reporte por tipo de solicitud

Permite consultar:

- Creaciones.
- Modificaciones.
- Retiros.
- Accesos.
- Consultas.
- Masivos.
- Otros.

### Reporte de proyectos

Incluye:

- Proyectos activos.
- Proyectos finalizados.
- Avance.
- Tareas pendientes.
- Tareas vencidas.

---

# 📅 Resumen mensual

La aplicación cuenta con un resumen mensual para consultar el comportamiento del área durante un periodo determinado.

Puede mostrar:

- Total de casos recibidos.
- Total de casos cerrados.
- Total pendientes.
- Total vencidos.
- Casos por analista.
- Casos por aplicación.
- Casos por tipo.
- Tiempo promedio.
- Proyectos activos.
- Proyectos finalizados.
- Avance de proyectos.

---

# 📤 Exportación

Los reportes pueden exportarse a:

- Excel.
- CSV.
- PDF.

Los archivos exportados respetan los filtros aplicados por el usuario.

---

# 📥 Importación

La aplicación permite importar información histórica mediante archivos:

- Excel.
- CSV.

El proceso contempla:

1. Selección del archivo.
2. Lectura de encabezados.
3. Vista previa.
4. Validación.
5. Confirmación.
6. Importación.
7. Reporte de errores.

El sistema evita importar silenciosamente registros inválidos.

También se controla la existencia de casos duplicados mediante el número de caso/ticket.

---

# 👥 Usuarios y permisos

La aplicación contempla inicialmente dos tipos de usuario:

### Administrador

Puede:

- Gestionar usuarios.
- Gestionar catálogos.
- Consultar toda la información.
- Editar registros.
- Acceder a configuración.
- Consultar reportes completos.

### Analista

Puede:

- Crear casos.
- Consultar casos.
- Gestionar casos asignados.
- Actualizar estados.
- Registrar avances.
- Trabajar en proyectos.
- Consultar reportes.

La estructura está preparada para ampliar posteriormente el sistema de permisos.

---

# 🔎 Auditoría

Los cambios importantes quedan registrados para mantener trazabilidad.

La auditoría contempla información como:

- Usuario.
- Acción.
- Registro afectado.
- Fecha.
- Hora.
- Valores anteriores.
- Valores nuevos.

Ejemplo:

```text
29/09/2026 08:12
Sebastián creó el caso #1234.

29/09/2026 09:30
Sebastián cambió el estado a "En análisis".

29/09/2026 11:15
Sebastián cambió el estado a "En gestión".

29/09/2026 14:20
Sebastián finalizó el caso.
```

---

# 🛠️ Tecnologías

La aplicación está construida utilizando:

- **Laravel**
- **PHP**
- **Blade**
- **Tailwind CSS**
- **Alpine.js**
- **SQLite**
- **Eloquent ORM**
- **Vite**
- **Composer**
- **NPM**

La base de datos inicial es SQLite para facilitar el despliegue local.

La arquitectura permite migrar posteriormente a MySQL u otro motor compatible con Laravel.

---

# 💻 Requisitos

Para ejecutar el proyecto se requiere:

- Windows, Linux o macOS.
- PHP compatible con la versión del proyecto.
- Composer.
- Node.js y NPM.
- Git.

---

# 📦 Instalación

Clonar el repositorio:

```bash
git clone URL_DEL_REPOSITORIO
```

Ingresar al proyecto:

```bash
cd "bitacora auto"
```

Instalar dependencias de PHP:

```bash
composer install
```

Instalar dependencias frontend:

```bash
npm install
```

---

# ⚙️ Configuración

Crear el archivo `.env` a partir del ejemplo:

```bash
copy .env.example .env
```

En Linux/macOS:

```bash
cp .env.example .env
```

Generar la clave de aplicación:

```bash
php artisan key:generate
```

---

# 🗄️ Base de datos

La aplicación utiliza inicialmente SQLite.

Verificar que exista:

```text
database/database.sqlite
```

Si no existe, crear el archivo.

En Windows:

```powershell
New-Item database/database.sqlite -ItemType File
```

Configurar en `.env`:

```env
DB_CONNECTION=sqlite
```

Ejecutar las migraciones:

```bash
php artisan migrate
```

Para instalar la estructura junto con los datos iniciales:

```bash
php artisan migrate --seed
```

---

# 🎨 Frontend

Para compilar los recursos:

```bash
npm run build
```

Durante el desarrollo puede utilizarse:

```bash
npm run dev
```

---

# ▶️ Ejecutar la aplicación

Iniciar el servidor local:

```bash
php artisan serve
```

La aplicación estará disponible normalmente en:

```text
http://127.0.0.1:8000
```

---

# 🌐 Acceso desde otros computadores

Para utilizar la aplicación dentro de una red local, ejecutar:

```bash
php artisan serve --host=0.0.0.0
```

Consultar la dirección IP del PC anfitrión:

```powershell
ipconfig
```

Buscar la dirección IPv4 de la conexión de red utilizada.

Por ejemplo:

```text
IPv4: 192.168.1.100
```

Los demás computadores podrán acceder mediante:

```text
http://192.168.1.100:8000
```

> La dirección IP utilizada dependerá de la configuración de la red local.

---

# 🔥 Firewall de Windows

Si los demás computadores no pueden acceder al sistema, puede ser necesario permitir el puerto `8000` en el Firewall de Windows.

Ejecutar PowerShell como administrador:

```powershell
New-NetFirewallRule -DisplayName "Bitacora GSI - Laravel 8000" -Direction Inbound -Protocol TCP -LocalPort 8000 -Action Allow
```

Se recomienda permitir el acceso únicamente dentro de la red local correspondiente.

---

# 🔐 Variables de entorno

El archivo `.env` contiene información específica del entorno local.

**NO subir `.env` al repositorio.**

El proyecto debe utilizar:

```text
.env.example
```

como plantilla para otros equipos.

---

# 🧪 Pruebas

Ejecutar las pruebas automatizadas mediante:

```bash
php artisan test
```

También se recomienda verificar:

```bash
php artisan migrate:fresh --seed
```

únicamente en entornos de desarrollo o prueba, ya que este comando elimina las tablas existentes.

---

# 🧹 Comandos útiles

Limpiar cachés:

```bash
php artisan optimize:clear
```

Consultar las rutas:

```bash
php artisan route:list
```

Consultar el estado de las migraciones:

```bash
php artisan migrate:status
```

Crear una migración:

```bash
php artisan make:migration nombre_de_migracion
```

Crear un modelo:

```bash
php artisan make:model NombreModelo
```

Crear un controlador:

```bash
php artisan make:controller NombreController
```

---

# 📊 Arquitectura general

La aplicación sigue una arquitectura basada en Laravel:

```text
Bitácora GSI
│
├── Dashboard
│
├── Flujo Diario
│   ├── Casos
│   ├── Estados
│   ├── Prioridades
│   └── SLA
│
├── Proyectos
│   ├── Proyectos
│   └── Tareas
│
├── Reportes
│   ├── Flujo diario
│   ├── Productividad
│   ├── Aplicaciones
│   ├── Solicitudes
│   └── Proyectos
│
├── Calendario
│
├── Usuarios
│
├── Configuración
│   ├── Estados
│   ├── Tipos de solicitud
│   ├── Aplicaciones
│   ├── Perfiles
│   ├── Grupos
│   └── SLA
│
└── Auditoría
```

---

# 🗃️ Principales entidades

La base de datos contempla entidades como:

```text
users
daily_cases
case_statuses
request_types
priorities
applications
profiles
groups
group_families
projects
project_tasks
case_project
audits
notifications
sla_configurations
```

Las relaciones se gestionan mediante Eloquent ORM y claves foráneas.

---

# 🔄 Flujo de trabajo

El flujo básico de utilización es:

```text
Caso recibido
      ↓
Registro en Bitácora
      ↓
Asignación de analista
      ↓
Análisis
      ↓
Gestión
      ↓
Concepto / Resultado
      ↓
Finalización
      ↓
Reporte automático
```

Para proyectos:

```text
Proyecto creado
      ↓
Definición de tareas
      ↓
Asignación
      ↓
Ejecución
      ↓
Seguimiento
      ↓
Finalización
      ↓
Reporte
```

---

# 🏢 Uso en red local

La arquitectura inicial está pensada para:

```text
              PC HOST
        ┌─────────────────┐
        │ Laravel         │
        │ PHP             │
        │ SQLite          │
        │ Bitácora GSI    │
        └────────┬────────┘
                 │
        ┌────────┼────────┐
        │        │        │
        ▼        ▼        ▼
      PC 2     PC 3     PC 4
```

Los usuarios acceden mediante navegador desde la misma red local.

---

# ⚠️ Consideraciones

Esta aplicación está diseñada inicialmente para uso interno dentro de una red local.

No se recomienda exponer directamente el servidor de desarrollo de Laravel (`php artisan serve`) a Internet.

Para un despliegue productivo o externo se debería utilizar un servidor web apropiado y una configuración de seguridad adecuada.

---

# 🔮 Próximas mejoras

Algunas funcionalidades que pueden incorporarse posteriormente:

- Notificaciones por correo.
- Integración con sistemas corporativos.
- Integración con APIs.
- MySQL/MariaDB para ambientes productivos.
- Sistema avanzado de permisos.
- Historial más detallado de cambios.
- Indicadores adicionales.
- Automatización de reportes.
- Programación de reportes periódicos.
- Backup automático de la base de datos.
- Panel administrativo avanzado.

---

# 👨‍💻 Desarrollo

**Proyecto:** Bitácora GSI  
**Área:** GSI  
**Tipo:** Aplicación web interna  
**Uso:** Gestión de casos, proyectos y reportes  
**Arquitectura:** Aplicación web Laravel para red local

---

# 📄 Licencia

Este proyecto es de uso interno.

La licencia y condiciones de distribución deberán definirse de acuerdo con las políticas de la organización.
