# Santiagosoftware - Sistema de Control de Gestión de Captación y Portal Académico

Sistema de Control de Gestión de Captación, Reincorporaciones, Estructura Académica y Carga de Calificaciones por Profesores con **PHP Nativo**, **Supabase (PostgreSQL)**, **Seguridad Avanzada (Password Hash BCRYPT)**, y **Consola de Auditoría con Firmas Criptográficas SHA-256**.

## 🚀 Características Principales

1. **Autenticación y Seguridad Estricta:**
   - Hashing seguro de contraseñas mediante `password_hash()` con algoritmo `BCRYPT` y costo elevado.
   - Control de acceso basado en Roles (**Administrador** vs **Profesor**).
   - Protección contra vulnerabilidades CSRF y ataques XSS.

2. **Control de Gestión de Captación (CRM de Aspirantes):**
   - Registro interactivo con las 7 banderas de control booleanas:
     - `Link Pre-Univ.`
     - `Pre-Universitario`
     - `Asistió Pre-Univ.`
     - `Dudas Carrera`
     - `Carrera Definida`
     - `Manifestó No Inscribirse`
     - `Se Inscribió`
   - Filtrado por Escuela/Carrera, Canal de Contacto (WhatsApp, Llamada, Personalizado), Búsqueda en vivo por Cédula o Nombre.

3. **Módulo de Reincorporaciones:**
   - Seguimiento a estudiantes regulares en proceso de reingreso por responsable.

4. **Portal de Carga de Notas para Profesores:**
   - Vista exclusiva para profesores asignados a materias específicas.
   - Calculadora automática de nota final ponderada (Corte 1: 30%, Corte 2: 30%, Corte 3: 40%).
   - Validaciones estrictas de notas (escala de 0.00 a 20.00 puntos).

5. **Auditoría e Inspección de Seguridad Super-Admin:**
   - Captura automática de cada operación `INSERT`, `UPDATE`, `DELETE`.
   - Almacenamiento de valores previos (`old_data`) y nuevos (`new_data`).
   - Generación de firma de verificación **SHA-256** por cada evento.

---

## 🛠️ Credenciales de Supabase Configuradas

- **URL de Supabase:** `https://vfngujnetjibgqgnjpox.supabase.co`
- **Llave Pública:** `sb_publishable_E2waoH8UOXywSDrM3RVqvg_AuP-q6MY`

---

## 🔐 Usuarios de Demonio / Prueba Inicial

- **Super Administrador:**
  - Correo: `admin@santiagosoftware.com`
  - Contraseña: `admin123`

- **Profesor:**
  - Correo: `profesor@santiagosoftware.com`
  - Contraseña: `prof123`

---

## 📦 Despliegue en Vercel

Este proyecto contiene `vercel.json` preconfigurado con el runtime `@vercel/php` y rutas en `/api/*.php`.
