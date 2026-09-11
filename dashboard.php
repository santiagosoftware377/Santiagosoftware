<?php
require_once __DIR__ . '/api/config/security.php';
$user = $_SESSION['user'] ?? ['id' => 'demo', 'full_name' => 'Cargando Usuario...', 'role' => 'admin'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politécnico Santiago Mariño - Control de Gestión & Portal Académico</title>
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>

<div class="app-container">

    <!-- Sidebar Navegación -->
    <aside class="sidebar">
        <div class="brand-header">
            <img src="assets/img/logo_transparent.png" alt="PSM Emblem Logo" class="brand-logo-img">
            <div>
                <div class="brand-title">Santiago Mariño</div>
                <div class="brand-subtitle">Extensión Porlamar</div>
            </div>
        </div>

        <ul class="nav-menu">
            <li>
                <a href="#" class="nav-item active admin-only" data-tab="tab-captacion">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span>CRM Captación</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item admin-only" data-tab="tab-reinc">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span>Reincorporaciones</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item" data-tab="tab-profesor">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422A12.083 12.083 0 0112 21.5a12.083 12.083 0 01-6.16-10.922L12 14z"></path></svg>
                    <span>Carga de Notas</span>
                </a>
            </li>
            <li>
                <a href="#" class="nav-item admin-only" data-tab="tab-auditoria">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Auditoría & SHA-256</span>
                </a>
            </li>
        </ul>

        <div class="user-profile">
            <div class="user-info">
                <span class="user-name" id="user-display-name">Administrador</span>
                <span class="user-role-badge badge-admin" id="user-display-role">Super Admin</span>
            </div>
            <button class="btn btn-danger" id="btn-logout" style="padding: 0.4rem 0.6rem;" title="Cerrar Sesión">🚪</button>
        </div>
    </aside>

    <!-- Área Principal de Contenido -->
    <main class="main-content">

        <!-- Métricas Rápidas -->
        <div class="stats-grid">
            <div class="glass-panel stat-card">
                <span class="stat-title">Período Académico Actual</span>
                <span class="stat-value" style="color: var(--accent);">2026-2</span>
            </div>
            <div class="glass-panel stat-card">
                <span class="stat-title">Aspirantes Registrados</span>
                <span class="stat-value">21</span>
            </div>
            <div class="glass-panel stat-card">
                <span class="stat-title">Inscritos Definitivos</span>
                <span class="stat-value" style="color: var(--success);">1</span>
            </div>
            <div class="glass-panel stat-card">
                <span class="stat-title">Escuelas Activas</span>
                <span class="stat-value" style="color: var(--accent);">9</span>
            </div>
        </div>

        <!-- TAB 1: CRM CAPTACION DE ASPIRANTES -->
        <section id="tab-captacion" class="tab-content" style="display: block;">
            <div class="page-header">
                <div class="page-title">
                    <h1>Control de Gestión de Captación</h1>
                    <p>Seguimiento de aspirantes y control de las 7 banderas de inscripción</p>
                </div>
                <button class="btn btn-primary" id="btn-new-lead">+ Registrar Nuevo Aspirante</button>
            </div>

            <div class="glass-panel controls-bar">
                <input type="text" id="search-lead" class="search-input" placeholder="🔍 Buscar por Nombre, Cédula o Carrera..." style="width: 320px;">
                <span style="font-size: 0.85rem; color: var(--text-secondary);">Período: <strong>2026-2</strong> | Responsable: <strong>Escuelas</strong></span>
            </div>

            <div class="glass-panel table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nombres y Apellidos</th>
                            <th>C.I.</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Carrera</th>
                            <th>Referido Por</th>
                            <th>Canal</th>
                            <th style="text-align:center;">Link Pre-Univ</th>
                            <th style="text-align:center;">Insc. Pre-Univ</th>
                            <th style="text-align:center;">Asist. Pre-Univ</th>
                            <th style="text-align:center;">Tiene Dudas</th>
                            <th style="text-align:center;">Carrera Definida</th>
                            <th style="text-align:center;">No Se Inscribirá</th>
                            <th style="text-align:center; color: var(--success);">SE INSCRIBIÓ</th>
                        </tr>
                    </thead>
                    <tbody id="leads-tbody">
                        <!-- Renderizado vía Javascript -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 2: REINCORPORACIONES -->
        <section id="tab-reinc" class="tab-content" style="display: none;">
            <div class="page-header">
                <div class="page-title">
                    <h1>Control de Reincorporaciones</h1>
                    <p>Seguimiento a alumnos regulares para reingreso en el período 2026-2</p>
                </div>
            </div>

            <div class="glass-panel table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nombres y Apellidos</th>
                            <th>Cédula</th>
                            <th>Teléfono</th>
                            <th>Carrera</th>
                            <th>Remitido Por</th>
                            <th>Estatus del Estudiante</th>
                            <th>Responsable</th>
                            <th style="text-align:center;">¿Se Inscribió?</th>
                        </tr>
                    </thead>
                    <tbody id="reinc-tbody">
                        <!-- Renderizado JS -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 3: PORTAL PROFESORES - CARGA DE NOTAS -->
        <section id="tab-profesor" class="tab-content" style="display: none;">
            <div class="page-header">
                <div class="page-title">
                    <h1>Portal de Profesores - Evaluación Académica</h1>
                    <p>Seleccione su materia para ingresar calificaciones (Corte 1: 30%, Corte 2: 30%, Corte 3: 40%)</p>
                </div>
            </div>

            <div class="glass-panel controls-bar">
                <select id="prof-subject-select" class="select-input" style="min-width: 300px;">
                    <option value="">-- Cargar Materias Asignadas --</option>
                </select>
                <span class="user-role-badge badge-profesor">Escala de 0.00 a 20.00 pts</span>
            </div>

            <div class="glass-panel table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Estudiante</th>
                            <th>Cédula</th>
                            <th>Corte 1 (30%)</th>
                            <th>Corte 2 (30%)</th>
                            <th>Corte 3 (40%)</th>
                            <th>Nota Definitiva</th>
                            <th>Observaciones</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody id="grades-tbody">
                        <tr>
                            <td colspan="8" style="text-align:center; padding: 2rem; color: var(--text-secondary);">
                                Por favor seleccione una materia en el menú superior para comenzar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAB 4: AUDITORIA SUPER ADMIN -->
        <section id="tab-auditoria" class="tab-content" style="display: none;">
            <div class="page-header">
                <div class="page-title">
                    <h1>Consola de Auditoría & Seguridad Criptográfica</h1>
                    <p>Trazabilidad inmutable de todas las acciones con firma SHA-256 en tiempo real</p>
                </div>
            </div>

            <div class="glass-panel table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Fecha / Hora</th>
                            <th>Acción</th>
                            <th>Tabla Afectada</th>
                            <th>Usuario / Rol</th>
                            <th>Checksum SHA-256</th>
                        </tr>
                    </thead>
                    <tbody id="audit-tbody">
                        <!-- Renderizado JS -->
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<!-- Modal Nuevo Aspirante -->
<div class="modal-backdrop" id="modal-lead">
    <div class="modal-card">
        <div class="modal-header">
            <h2>Registrar Nuevo Aspirante</h2>
            <button class="close-btn" id="close-lead-modal">&times;</button>
        </div>
        <form id="form-lead">
            <div class="form-group">
                <label>Nombres y Apellidos *</label>
                <input type="text" name="full_name" required placeholder="Ej. Carlos Eduardo Pérez">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Cédula de Identidad</label>
                    <input type="text" name="ci" placeholder="V-30123456">
                </div>
                <div class="form-group">
                    <label>Teléfono *</label>
                    <input type="text" name="phone" required placeholder="0412-1234567">
                </div>
            </div>
            <div class="form-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" placeholder="aspirante@gmail.com">
            </div>
            <div class="form-group">
                <label>Carrera a Cursar</label>
                <select name="carrera_cursar" class="select-input">
                    <option value="Por Decidir">Por Decidir</option>
                    <option value="Ingeniería Civil">Ingeniería Civil</option>
                    <option value="Ingeniería Electrónica">Ingeniería Electrónica</option>
                    <option value="Ingeniería Eléctrica">Ingeniería Eléctrica</option>
                    <option value="Ingeniería de Sistemas">Ingeniería de Sistemas</option>
                    <option value="Ingeniería Química">Ingeniería Química</option>
                    <option value="Ingeniería Mecánica (Mtto)">Ingeniería Mecánica (Mtto)</option>
                    <option value="Ingeniería Industrial">Ingeniería Industrial</option>
                    <option value="Arquitectura">Arquitectura</option>
                </select>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>Referido Por</label>
                    <input type="text" name="referred_by" placeholder="AURORI ALFONZO">
                </div>
                <div class="form-group">
                    <label>Canal de Contacto</label>
                    <select name="channel" class="select-input">
                        <option value="WhatsApp">WhatsApp</option>
                        <option value="LLAMADA">Llamada Telefónica</option>
                        <option value="LLAMADA_WHATSAPP">Llamada WhatsApp</option>
                        <option value="ATENCION_PERSONALIZADA">Atención Personalizada</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Resultado / Notas del Contacto</label>
                <textarea name="contact_result" rows="3" placeholder="Comentarios sobre la asesoría brindada..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Guardar Aspirante</button>
            </div>
        </form>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
