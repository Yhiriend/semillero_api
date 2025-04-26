
# Semillero API

Bienvenidos a **semillero_api**, un proyecto creado con **Laravel** y **PHP** siguiendo una arquitectura limpia, organización modular, y principios SOLID.

## 🚀 Cómo levantar el proyecto

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/Yhiriend/semillero_api.git
   cd semillero_api
   ```

2. **Instalar dependencias PHP:**
   ```bash
   composer install
   ```

3. **Configurar variables de entorno:**
   ```bash
   cp .env.example .env
   ```

4. **Generar application key:**
   ```bash
   php artisan key:generate
   ```

5. **Configurar la conexión a la base de datos en `.env`:**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Correr las migraciones:**
   ```bash
   php artisan migrate
   ```

7. **Levantar el servidor del proyecto:**
   ```bash
   php artisan serve
   ```

   Por defecto estará disponible en: [http://localhost:8000](http://localhost:8000)

---

## 📚 Reglas del Juego para un Código Limpio

### 1. Como nombrar variables, clases y métodos

- Usar **camelCase** para variables y nombres de métodos.
- Usar **PascalCase** para nombres de clases.
- Usar **Inglés** para todo el código: variables, métodos, clases, comentarios.

✅ Correcto:
```php
$userName = 'John Doe';

public function getUserProfile() {}

class EventController {}
```

🚫 Incorrecto:
```php
$user_name = 'John Doe';

public function get_user_profile() {}

class eventcontroller {}
```

---

### 2. Nombres Significativos y Claros

Usar nombres claros y descriptivos para variables, métodos, y clases.

✅ Correcto:
```php
$eventDate = '2024-04-26';

public function calculateProjectDuration() {}

class AuthenticationService {}
```

🚫 Incorrecto:
```php
$e = '2024-04-26';

public function doSomething() {}

class Utils {}
```

---

### 3. Principios SOLID

Sigue e implementa los principios **SOLID** en el código:

| Principle | Rule | Example |
|:---------|:-----|:--------|
| **S** | Single Responsibility | Separate `UserService` from `EmailService` |
| **O** | Open/Closed | Use interfaces for extensible behavior |
| **L** | Liskov Substitution | Subclasses must respect the base class |
| **I** | Interface Segregation | Small, focused interfaces |
| **D** | Dependency Inversion | Depend on abstractions, not implementations |

Ejemplos:

**Single Responsibility**
```php
class UserService {
    public function registerUser(array $userData) { /*...*/ }
}

class EmailService {
    public function sendRegistrationEmail(string $email) { /*...*/ }
}
```

**Dependency Inversion**
```php
interface PaymentGateway {
    public function charge(float $amount);
}

class StripePaymentGateway implements PaymentGateway {
    public function charge(float $amount) { /*...*/ }
}

class PaymentService {
    public function __construct(private PaymentGateway $paymentGateway) {}
}
```

---

## 📂 Estructura de Carpetas

Este proyecto está organizado por **Módulos** y **Capas** para una mejor escalabilidad.

```
semillero_api/
├── app/
│   ├── Modules/
│   │   ├── Authentication/
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Models/
│   │   ├── Users/
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Models/
│   │   ├── Events/
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Models/
│   │   ├── Projects/
│   │   │   ├── Controllers/
│   │   │   ├── Services/
│   │   │   ├── Repositories/
│   │   │   ├── Requests/
│   │   │   ├── Resources/
│   │   │   └── Models/
│   ├── Http/
│   │   ├── Middleware/
│   ├── Providers/
├── database/
│   ├── migrations/
│   ├── seeders/
├── routes/
│   ├── api.php
│   ├── web.php
├── tests/
│   ├── Feature/
│   ├── Unit/
├── .env
├── composer.json
└── README.md
```

### Ejemplo de Estructura por Módulos (Users)

path `app/Modules/Users/`:

| Folder | Responsibility |
|:-------|:----------------|
| **Controllers** | Handle incoming HTTP requests |
| **Services** | Business logic |
| **Repositories** | Data access (DB queries) |
| **Requests** | Form validation |
| **Resources** | API response formatting |
| **Models** | Eloquent models |

---

## ✅ Checklist antes de solicitar un Pull Request

- [ ] Seguir las convenciones de nombres (camelCase para variables y métodos, PascalCase para clases).
- [ ] Aplicar los principios SOLID en el código.
- [ ] Crear o actualizar las capas correspondientes (Controller, Service, Repository).
- [ ] Agregar validaciones de formulario donde sea necesario (usando clases Request).
- [ ] Utilizar inglés en todo el código (nombres de variables, clases, métodos, comentarios).
- [ ] Probar localmente todos los cambios antes de solicitar el Pull Request.
- [ ] Realizar commits siguiendo esta estructura:
```
:gitmoji: NOMBRE_RAMA: Descripción breve del cambio
```
**Ejemplo correcto:**
```
✨ user_registration: Add registration endpoint and validation
```
- [ ] **Subir los cambios siempre a través de un Pull Request.** No se debe hacer push directo a las ramas principales (`main`, `develop`, `master`, `qa`, etc.).
- [ ] Asignar al menos un revisor al Pull Request para su revisión y aprobación.

### ⚠️ Importante
Antes de subir cambios:
- Asegúrate de actualizar tu rama local con los últimos cambios de `main`, `develop`, `master`, o `qa`.
- Resolver cualquier conflicto antes de enviar el Pull Request.
- Confirmar que las pruebas locales pasan correctamente.
---


# Asignación de Tareas para el Proyecto

Esta sección describe la asignación de tareas para el desarrollo de los módulos del proyecto **Semillero API**. Hay 5 módulos a desarrollar y 7 grupos de trabajo. Algunos módulos serán desarrollados por dos grupos debido a su mayor complejidad.

## Módulos a Desarrollar

1. **Registro/Autenticación (Base para todo el sistema)**  
   - Descripción: Este módulo será la base para el sistema, gestionando el registro de usuarios y la autenticación.
   - **Funcionalidades:**
     - Crear usuario
     - Actualizar datos
     - Consultar usuario
   - **Roles relacionados:**
     - Integrante semillero
     - Líder proyecto

2. **Gestión de Semilleros (CRUD + Inscripciones)**  
   - Descripción: Este módulo gestionará la creación, actualización y consulta de semilleros, así como la inscripción de estudiantes a los mismos.
   - **Funcionalidades:**
     - Crear semillero
     - Inscribir estudiantes
     - Consultar semilleros
   - **Roles relacionados:**
     - Coordinador semillero
     - Integrante semillero

3. **Gestión de Proyectos (Estados, Autores, Relación con Semilleros)**  
   - Descripción: Este módulo manejará los proyectos dentro del sistema, gestionando su estado, autores y su relación con los semilleros.
   - **Funcionalidades:**
     - Crear proyecto
     - Actualizar proyecto
     - Consultar proyectos
     - Relacionar proyectos con semilleros
   - **Roles relacionados:**
     - Líder proyecto
     - Coordinador proyecto
     - Coordinador semillero

4. **Gestión de Eventos (Inscripción de Proyectos)**  
   - Descripción: Este módulo se encargará de la gestión de eventos, permitiendo la inscripción de proyectos en dichos eventos.
   - **Funcionalidades:**
     - Crear evento
     - Inscribir proyectos a eventos
     - Consultar eventos
   - **Roles relacionados:**
     - Coordinador de evento
     - Coordinador proyecto

5. **Evaluaciones (Asignación de Evaluadores y Cálculos)**  
   - Descripción: Este módulo será responsable de asignar evaluadores a proyectos y realizar los cálculos de las evaluaciones.
   - **Funcionalidades:**
     - Asignar evaluadores
     - Calcular puntajes de evaluaciones
     - Consultar evaluaciones
   - **Roles relacionados:**
     - Coordinador semillero
     - Coordinador proyecto
     - Coordinador de evento

---

## Asignación de Grupos

- **Grupo 1: Registro/Autenticación**
  - Módulo sencillo que requiere integración con el sistema de autenticación y gestión de usuarios.

- **Grupo 2: Gestión de Semilleros**
  - Módulo de complejidad moderada que incluye la creación, inscripción, y consulta de semilleros.

- **Grupo 3: Gestión de Proyectos**
  - Módulo de alta complejidad debido a la gestión de estados, autores y la relación con los semilleros. Este módulo será desarrollado junto con el **Grupo 4**.

- **Grupo 4: Gestión de Proyectos (Conjunto con Grupo 3)**
  - Módulo de alta complejidad como se explicó antes. Este grupo trabajará junto con el **Grupo 3**.

- **Grupo 5: Gestión de Eventos**
  - Módulo de complejidad moderada que incluye la creación de eventos y la inscripción de proyectos a esos eventos.

- **Grupo 6: Evaluaciones**
  - Módulo complejo, ya que incluye la asignación de evaluadores y la realización de cálculos. Este grupo trabajará junto con el **Grupo 7**.

- **Grupo 7: Evaluaciones (Conjunto con Grupo 6)**
  - Módulo complejo, trabajará junto con el **Grupo 6** en las asignaciones de evaluadores y cálculos.

---

## Roles de Usuario Relacionados

1. **Integrante Semillero (Estudiantes):**
   - Crear usuario
   - Actualizar datos
   - Consultar datos

2. **Líder Proyecto (Estudiantes):**
   - Crear usuario
   - Actualizar datos
   - Crear proyecto
   - Consultar proyectos

3. **Coordinador Semillero (Profesor):**
   - Inscribir estudiantes
   - Coordinar actividades
   - Consultar proyectos
   - Inscribir proyectos
   - Evaluar proyectos

4. **Coordinador Proyecto (Profesor):**
   - Presentar proyectos
   - Asignar estudiantes a proyectos
   - Evaluar proyectos
   - Revisar proyectos

5. **Coordinador de Evento (Profesor):**
   - Crear eventos
   - Coordinar eventos
   - Asignar evaluadores
   - Coordinar actividades

6. **Administrador (Administrador):**
   - Todas las funciones del sistema, excepto la gestión de evaluaciones.


---
# 🚀 Guía para colaboradores

## 1. Clonar el repositorio
```bash
git clone https://github.com/Yhiriend/semillero_api.git
```

## 2. Crear una nueva rama para tu tarea
**Siempre crea una rama nueva basada en `main`**

```bash
git checkout main
git pull origin main
git checkout -b nombre_de_tu_rama
```

⚫️ **Importante:** Usa nombres de rama en inglés, en formato `kebab-case`, por ejemplo:
- `feature/create-user`
- `bugfix/fix-login-error`
- `hotfix/update-event-model`

## 3. Realizar cambios en tu rama
Haz los cambios necesarios siguiendo las reglas de codificación del proyecto (camelCase, nombres claros, principios SOLID, etc.).

## 4. Hacer commits siguiendo la estructura establecida
Cada commit debe seguir este formato:

```
:gitmoji: NOMBRE_RAMA: Mensaje claro del commit
```

⚫️ **Ejemplo:**

```
✨ feature/create-user: add controller and service for user registration
```

(Usar GITMOJI al inicio ayuda a identificar el tipo de cambio, por ejemplo: ✨ para nueva funcionalidad, 🐛 para correcciones, 🔥 para eliminar código, etc.)

## 5. Subir tus cambios
```bash
git add .
git commit -m ":gitmoji: NOMBRE_RAMA: Mensaje claro del commit"
git push origin nombre_de_tu_rama
```

## 6. Crear un Pull Request
- Ve al repositorio en GitHub.
- Haz clic en **"Compare & Pull Request"**.
- Selecciona que quieres hacer el Pull Request hacia la rama `main`.
- Escribe un mensaje claro describiendo qué hiciste.

⚫️ **Nota:**  
Tu Pull Request **debe ser aprobado** antes de ser fusionado a la rama principal.  
No está permitido hacer "merge" directo.

## 7. Corregir cambios solicitados
Si el revisor solicita cambios, realiza los cambios en tu misma rama y sube los nuevos commits.  
Automáticamente se actualizará el Pull Request.

## 8. No hacer push directo a ramas protegidas
Ramas como `main` y `qa` están protegidas.  
**Está prohibido** hacer `push` directo, **siempre** debe pasar por un Pull Request.

# ✅ Resumen Rápido
- Siempre trabajar en una nueva rama.
- Siempre seguir el formato de commits.
- Siempre hacer Pull Request para mergear cambios.
- Código limpio, claro y probado localmente antes de enviar.

# 🖊️ Tabla de GITMOJIS comunes

| Gitmoji | Descripción | Cuándo usarlo |
|:------:|:-----------|:--------------|
|✨| Nueva funcionalidad | Cuando agregas nuevas funcionalidades |
|🐛| Corrección de bug | Cuando solucionas errores o fallos |
|🔄| Refactorización | Cuando mejoras código sin cambiar funcionalidad |
|🔍| Mejoras de performance | Optimizaciones de rendimiento |
|💾| Actualización de archivos | Cambios en archivos de configuración o datos |
|💡| Documentación | Cambios en README.md u otra documentación |
|🔥| Eliminación de código | Eliminación de código o archivos innecesarios |
|🔒| Seguridad | Cambios relacionados con la seguridad |
|🌐| Deploy/Producción | Cambios para deployar a ambiente de producción |

---

# 🚀 ¡A trabajar en equipo con calidad y orden! 🚀

