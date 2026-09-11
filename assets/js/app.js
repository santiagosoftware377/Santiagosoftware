// ============================================================================
// SANTIAGO SOFTWARE - CLIENT JS CONTROLLER & INTERACTIVE ENGINE v2.4.0
// Politécnico Santiago Mariño - Extensión Porlamar
// Software Architects: Ing. Oscar Franco & Ing. Jesus Villalba
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initApp();
    initMiniFooter();
});

let currentUser = null;
let allLeads = [];
let allReincorporaciones = [];
let allSchools = [];
let allSubjects = [];

function initApp() {
    checkCurrentUser();
    bindEvents();
}

function checkCurrentUser() {
    fetch('api/auth.php?action=current_user')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.user) {
                currentUser = data.user;
                localStorage.setItem('santiago_user', JSON.stringify(data.user));
                updateUserUI(data.user);
                loadLeads();
                loadReincorporaciones();
                loadAcademic();
                if (currentUser.role === 'admin') loadAuditLogs();
            } else {
                const stored = localStorage.getItem('santiago_user');
                if (stored) {
                    currentUser = JSON.parse(stored);
                    updateUserUI(currentUser);
                    loadLeads();
                    loadReincorporaciones();
                    loadAcademic();
                    if (currentUser.role === 'admin') loadAuditLogs();
                } else if (!window.location.pathname.endsWith('/') && !window.location.pathname.endsWith('login')) {
                    window.location.href = './';
                }
            }
        })
        .catch(() => {
            const stored = localStorage.getItem('santiago_user');
            if (stored) {
                currentUser = JSON.parse(stored);
                updateUserUI(currentUser);
                loadLeads();
                loadReincorporaciones();
                loadAcademic();
                if (currentUser.role === 'admin') loadAuditLogs();
            }
        });
}

function updateUserUI(user) {
    const nameEl = document.getElementById('user-display-name');
    const roleEl = document.getElementById('user-display-role');
    if (nameEl) nameEl.textContent = user.full_name;
    if (roleEl) {
        roleEl.textContent = user.role === 'admin' ? 'Super Admin' : 'Profesor';
        roleEl.className = 'user-role-badge ' + (user.role === 'admin' ? 'badge-admin' : 'badge-profesor');
    }
    
    if (user.role !== 'admin') {
        document.querySelectorAll('.admin-only').forEach(el => el.style.display = 'none');
        switchTab('tab-profesor');
    }
}

function initTabs() {
    document.querySelectorAll('.nav-item[data-tab]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tabId = btn.getAttribute('data-tab');
            switchTab(tabId);
        });
    });
}

function switchTab(tabId) {
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    
    const targetNav = document.querySelector(`.nav-item[data-tab="${tabId}"]`);
    const targetTab = document.getElementById(tabId);
    
    if (targetNav) targetNav.classList.add('active');
    if (targetTab) targetTab.style.display = 'block';
}

function bindEvents() {
    // Logout
    const logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('santiago_user');
            fetch('api/auth.php?action=logout')
                .finally(() => window.location.href = './');
        });
    }

    // Modal nuevo aspirante
    const btnNewLead = document.getElementById('btn-new-lead');
    const modalLead = document.getElementById('modal-lead');
    const closeLeadModal = document.getElementById('close-lead-modal');
    const formLead = document.getElementById('form-lead');

    if (btnNewLead && modalLead) {
        btnNewLead.addEventListener('click', () => modalLead.classList.add('active'));
    }
    if (closeLeadModal && modalLead) {
        closeLeadModal.addEventListener('click', () => modalLead.classList.remove('active'));
    }

    if (formLead) {
        formLead.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formLead);
            formData.append('action', 'create');
            
            fetch('api/captacion.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modalLead.classList.remove('active');
                    formLead.reset();
                    loadLeads();
                    showToast('Aspirante registrado exitosamente con auditoría SHA-256');
                } else {
                    alert(res.error || 'Error al guardar');
                }
            });
        });
    }

    // Modal Editar Aspirante
    const modalEditLead = document.getElementById('modal-edit-lead');
    const closeEditLeadModal = document.getElementById('close-edit-lead-modal');
    const formEditLead = document.getElementById('form-edit-lead');
    if (closeEditLeadModal && modalEditLead) {
        closeEditLeadModal.addEventListener('click', () => modalEditLead.classList.remove('active'));
    }
    if (formEditLead) {
        formEditLead.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formEditLead);
            formData.append('action', 'update');
            fetch('api/captacion.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modalEditLead.classList.remove('active');
                    loadLeads();
                    showToast('Aspirante actualizado exitosamente');
                } else {
                    alert(res.error || 'Error al actualizar');
                }
            });
        });
    }

    // Modal Ver Detalle Aspirante
    const modalViewLead = document.getElementById('modal-view-lead');
    const closeViewLeadModal = document.getElementById('close-view-lead-modal');
    const btnCloseViewLead = document.getElementById('btn-close-view-lead');
    if (closeViewLeadModal && modalViewLead) {
        closeViewLeadModal.addEventListener('click', () => modalViewLead.classList.remove('active'));
    }
    if (btnCloseViewLead && modalViewLead) {
        btnCloseViewLead.addEventListener('click', () => modalViewLead.classList.remove('active'));
    }

    // Modal Reincorporaciones
    const btnNewReinc = document.getElementById('btn-new-reinc');
    const modalReinc = document.getElementById('modal-reinc');
    const closeReincModal = document.getElementById('close-reinc-modal');
    const formReinc = document.getElementById('form-reinc');
    if (btnNewReinc && modalReinc) {
        btnNewReinc.addEventListener('click', () => {
            formReinc.reset();
            document.getElementById('reinc-id').value = '';
            document.getElementById('modal-reinc-title').textContent = 'Registrar Reincorporación';
            modalReinc.classList.add('active');
        });
    }
    if (closeReincModal && modalReinc) {
        closeReincModal.addEventListener('click', () => modalReinc.classList.remove('active'));
    }
    if (formReinc) {
        formReinc.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('reinc-id').value;
            const action = id ? 'update' : 'create';
            const formData = new FormData(formReinc);
            formData.append('action', action);
            fetch('api/reincorporaciones.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modalReinc.classList.remove('active');
                    loadReincorporaciones();
                    showToast(id ? 'Reincorporación actualizada' : 'Reincorporación registrada');
                }
            });
        });
    }

    // 1-Click Reset Registros en 0
    const btnResetZero = document.getElementById('btn-reset-zero');
    if (btnResetZero) {
        btnResetZero.addEventListener('click', () => {
            if (confirm('¿Está seguro de vaciar todos los registros de captación e iniciar en 0?')) {
                fetch('api/backup.php?action=reset_zero')
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            loadLeads();
                            loadReincorporaciones();
                            showToast('Sistema inicializado en 0 registros');
                        }
                    });
            }
        });
    }

    // CRUD Escuelas Modales
    const btnNewSchool = document.getElementById('btn-new-school');
    const modalSchool = document.getElementById('modal-school');
    const closeSchoolModal = document.getElementById('close-school-modal');
    const formSchool = document.getElementById('form-school');

    if (btnNewSchool && modalSchool) btnNewSchool.addEventListener('click', () => {
        formSchool.reset();
        document.getElementById('school-id').value = '';
        modalSchool.classList.add('active');
    });
    if (closeSchoolModal && modalSchool) closeSchoolModal.addEventListener('click', () => modalSchool.classList.remove('active'));

    if (formSchool) {
        formSchool.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('school-id').value;
            const code = document.getElementById('school-code').value;
            const name = document.getElementById('school-name').value;
            const desc = document.getElementById('school-desc').value;

            fetch('api/academico.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_school', id, code, name, description: desc })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modalSchool.classList.remove('active');
                    loadAcademic();
                    showToast('Escuela / Carrera guardada');
                }
            });
        });
    }

    // CRUD Materias Modales
    const btnNewSubject = document.getElementById('btn-new-subject');
    const modalSubject = document.getElementById('modal-subject');
    const closeSubjectModal = document.getElementById('close-subject-modal');
    const formSubject = document.getElementById('form-subject');

    if (btnNewSubject && modalSubject) btnNewSubject.addEventListener('click', () => {
        formSubject.reset();
        document.getElementById('subject-id').value = '';
        modalSubject.classList.add('active');
    });
    if (closeSubjectModal && modalSubject) closeSubjectModal.addEventListener('click', () => modalSubject.classList.remove('active'));

    if (formSubject) {
        formSubject.addEventListener('submit', (e) => {
            e.preventDefault();
            const id = document.getElementById('subject-id').value;
            const code = document.getElementById('subject-code').value;
            const name = document.getElementById('subject-name').value;
            const teacher = document.getElementById('subject-teacher').value;

            fetch('api/academico.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'save_subject', id, code, name, teacher_name: teacher })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    modalSubject.classList.remove('active');
                    loadAcademic();
                    showToast('Materia guardada y asignada');
                }
            });
        });
    }

    // Backup Restore Upload
    const btnTriggerRestore = document.getElementById('btn-trigger-restore');
    const backupInput = document.getElementById('backup-file-input');
    if (btnTriggerRestore && backupInput) {
        btnTriggerRestore.addEventListener('click', () => backupInput.click());
        backupInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (evt) => {
                try {
                    const json = JSON.parse(evt.target.result);
                    fetch('api/backup.php?action=restore', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ backup_data: json })
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            loadLeads();
                            loadReincorporaciones();
                            loadAcademic();
                            showToast('Backup restaurado con éxito');
                        } else {
                            alert(res.error);
                        }
                    });
                } catch(err) {
                    alert('Archivo JSON no válido.');
                }
            };
            reader.readAsText(file);
        });
    }

    // Filtros de búsqueda en captación
    const searchLead = document.getElementById('search-lead');
    if (searchLead) {
        searchLead.addEventListener('input', filterLeads);
    }
}

function loadLeads() {
    fetch('api/captacion.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                allLeads = data.leads;
                renderLeads(allLeads);
                updateStats();
            }
        });
}

function updateStats() {
    const leadsCount = document.getElementById('stat-leads-count');
    const inscCount = document.getElementById('stat-inscriptos-count');
    const pipeTotal = document.getElementById('pipe-crm-total');
    const pipePreuniv = document.getElementById('pipe-crm-preuniv');
    const pipeInsc = document.getElementById('pipe-crm-inscriptos');
    const pipeDudas = document.getElementById('pipe-crm-dudas');

    const totalInsc = allLeads.filter(l => l.se_inscribio).length;
    const totalPreuniv = allLeads.filter(l => l.se_inscribio_pre_universitario || l.asistio_pre_universitario).length;
    const totalDudas = allLeads.filter(l => l.tiene_dudas_carrera).length;

    if (leadsCount) leadsCount.textContent = allLeads.length;
    if (inscCount) inscCount.textContent = totalInsc;
    if (pipeTotal) pipeTotal.textContent = allLeads.length;
    if (pipePreuniv) pipePreuniv.textContent = totalPreuniv;
    if (pipeInsc) pipeInsc.textContent = totalInsc;
    if (pipeDudas) pipeDudas.textContent = totalDudas;
}

function renderLeads(leads) {
    const tbody = document.getElementById('leads-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (leads.length === 0) {
        tbody.innerHTML = `<tr><td colspan="16" style="text-align:center; color: var(--text-secondary); padding: 3rem;">No hay registros de aspirantes. Haga clic en "+ Registrar Nuevo Aspirante" para comenzar.</td></tr>`;
        return;
    }

    leads.forEach((l, index) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><strong>${index + 1}</strong></td>
            <td><strong>${escapeHtml(l.full_name)}</strong></td>
            <td>${escapeHtml(l.ci || 'N/A')}</td>
            <td>${escapeHtml(l.phone || '')}</td>
            <td>${escapeHtml(l.email || '')}</td>
            <td><span class="hash-tag">${escapeHtml(l.carrera_cursar || 'Por Decidir')}</span></td>
            <td>${escapeHtml(l.referred_by || '')}</td>
            <td>${escapeHtml(l.channel || 'WhatsApp')}</td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="se_inscribio_link_pre_univ" ${l.se_inscribio_link_pre_univ ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="se_inscribio_pre_universitario" ${l.se_inscribio_pre_universitario ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="asistio_pre_universitario" ${l.asistio_pre_universitario ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="tiene_dudas_carrera" ${l.tiene_dudas_carrera ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="ya_tiene_definida_carrera" ${l.ya_tiene_definida_carrera ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="manifesto_no_inscribirse" ${l.manifesto_no_inscribirse ? 'checked' : ''}></td>
            <td style="text-align:center;"><input type="checkbox" class="flag-checkbox" data-id="${l.id}" data-flag="se_inscribio" ${l.se_inscribio ? 'checked' : ''}></td>
            <td style="text-align:center;">
                <div class="btn-action-group">
                    <button class="btn-action-icon btn-action-view btn-view-lead" data-id="${l.id}" title="Ver Expediente Completo">👁️</button>
                    <button class="btn-action-icon btn-action-edit btn-edit-lead" data-id="${l.id}" title="Editar Aspirante">✏️</button>
                    <button class="btn-action-icon btn-action-delete btn-del-lead" data-id="${l.id}" title="Eliminar Registro">🗑️</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.flag-checkbox').forEach(cb => {
        cb.addEventListener('change', (e) => {
            const id = e.target.getAttribute('data-id');
            const flag = e.target.getAttribute('data-flag');
            const value = e.target.checked;

            fetch('api/captacion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_flag', id, flag, value })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    showToast('Estado actualizado y registrado en auditoría');
                    const item = allLeads.find(x => x.id === id);
                    if (item) item[flag] = value;
                    updateStats();
                    if (currentUser && currentUser.role === 'admin') loadAuditLogs();
                }
            });
        });
    });

    tbody.querySelectorAll('.btn-view-lead').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const l = allLeads.find(x => x.id === id);
            if (!l) return;
            const body = document.getElementById('view-lead-body');
            body.innerHTML = `
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                    <div><strong>Nombre:</strong> ${escapeHtml(l.full_name)}</div>
                    <div><strong>Cédula:</strong> ${escapeHtml(l.ci || 'N/A')}</div>
                    <div><strong>Teléfono:</strong> ${escapeHtml(l.phone || 'N/A')}</div>
                    <div><strong>Correo:</strong> ${escapeHtml(l.email || 'N/A')}</div>
                    <div><strong>Carrera a Cursar:</strong> ${escapeHtml(l.carrera_cursar)}</div>
                    <div><strong>Referido Por:</strong> ${escapeHtml(l.referred_by || 'N/A')}</div>
                    <div><strong>Canal:</strong> ${escapeHtml(l.channel || 'WhatsApp')}</div>
                    <div><strong>Período:</strong> ${escapeHtml(l.period || '2026-2')}</div>
                </div>
                <div style="background: rgba(10, 17, 40, 0.6); padding: 1rem; border-radius: 10px; border: 1px solid var(--border-color);">
                    <strong>Comentarios / Resultado del Contacto:</strong><br>
                    <p style="margin-top: 0.5rem; color: var(--text-secondary);">${escapeHtml(l.contact_result || 'Sin observaciones registradas.')}</p>
                </div>
            `;
            document.getElementById('modal-view-lead').classList.add('active');
        });
    });

    tbody.querySelectorAll('.btn-edit-lead').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const l = allLeads.find(x => x.id === id);
            if (!l) return;
            document.getElementById('edit-lead-id').value = l.id;
            document.getElementById('edit-lead-fullname').value = l.full_name || '';
            document.getElementById('edit-lead-ci').value = l.ci || '';
            document.getElementById('edit-lead-phone').value = l.phone || '';
            document.getElementById('edit-lead-email').value = l.email || '';
            document.getElementById('edit-lead-carrera').value = l.carrera_cursar || 'Por Decidir';
            document.getElementById('edit-lead-referred').value = l.referred_by || '';
            document.getElementById('edit-lead-channel').value = l.channel || 'WhatsApp';
            document.getElementById('edit-lead-result').value = l.contact_result || '';
            document.getElementById('modal-edit-lead').classList.add('active');
        });
    });

    tbody.querySelectorAll('.btn-del-lead').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (confirm('¿Está seguro de eliminar este registro de aspirante?')) {
                fetch('api/captacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        loadLeads();
                        showToast('Aspirante eliminado');
                    }
                });
            }
        });
    });
}

function filterLeads() {
    const q = document.getElementById('search-lead').value.toLowerCase();
    const filtered = allLeads.filter(l => 
        l.full_name.toLowerCase().includes(q) || 
        (l.ci && l.ci.toLowerCase().includes(q)) ||
        (l.carrera_cursar && l.carrera_cursar.toLowerCase().includes(q))
    );
    renderLeads(filtered);
}

function loadReincorporaciones() {
    fetch('api/reincorporaciones.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                allReincorporaciones = data.reincorporaciones;
                renderReincorporaciones(allReincorporaciones);
            }
        });
}

function renderReincorporaciones(list) {
    const tbody = document.getElementById('reinc-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (list.length === 0) {
        tbody.innerHTML = `<tr><td colspan="10" style="text-align:center; padding: 2rem;">No hay estudiantes en lista de reincorporaciones.</td></tr>`;
        return;
    }

    list.forEach((r, i) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${i + 1}</td>
            <td><strong>${escapeHtml(r.full_name)}</strong></td>
            <td>${escapeHtml(r.ci || 'N/A')}</td>
            <td>${escapeHtml(r.phone || '')}</td>
            <td>${escapeHtml(r.carrera_cursar || '')}</td>
            <td>${escapeHtml(r.remitido_por || '')}</td>
            <td><span class="user-role-badge badge-profesor">${escapeHtml(r.student_status || '')}</span></td>
            <td><strong>${escapeHtml(r.responsible || 'Manuel')}</strong></td>
            <td style="text-align:center;"><input type="checkbox" class="reinc-checkbox" data-id="${r.id}" ${r.se_inscribio ? 'checked' : ''}></td>
            <td style="text-align:center;">
                <div class="btn-action-group">
                    <button class="btn-action-icon btn-action-edit btn-edit-reinc" data-id="${r.id}" title="Editar Reincorporación">✏️</button>
                    <button class="btn-action-icon btn-action-delete btn-del-reinc" data-id="${r.id}" title="Eliminar Registro">🗑️</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.reinc-checkbox').forEach(cb => {
        cb.addEventListener('change', (e) => {
            const id = e.target.getAttribute('data-id');
            const val = e.target.checked;
            fetch('api/reincorporaciones.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle_inscripto', id, se_inscribio: val })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) showToast('Reincorporación actualizada');
            });
        });
    });

    tbody.querySelectorAll('.btn-edit-reinc').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const r = allReincorporaciones.find(x => x.id === id);
            if (!r) return;
            document.getElementById('reinc-id').value = r.id;
            document.getElementById('reinc-fullname').value = r.full_name || '';
            document.getElementById('reinc-ci').value = r.ci || '';
            document.getElementById('reinc-phone').value = r.phone || '';
            document.getElementById('reinc-carrera').value = r.carrera_cursar || 'Arquitectura';
            document.getElementById('reinc-remitido').value = r.remitido_por || '';
            document.getElementById('reinc-responsible').value = r.responsible || 'Manuel';
            document.getElementById('reinc-status').value = r.student_status || '';
            document.getElementById('modal-reinc-title').textContent = '✏️ Editar Reincorporación';
            document.getElementById('modal-reinc').classList.add('active');
        });
    });

    tbody.querySelectorAll('.btn-del-reinc').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (confirm('¿Eliminar este registro de reincorporación?')) {
                fetch('api/reincorporaciones.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', id })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        loadReincorporaciones();
                        showToast('Reincorporación eliminada');
                    }
                });
            }
        });
    });
}

function loadAcademic() {
    fetch('api/academico.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                allSchools = data.schools;
                allSubjects = data.subjects;

                renderSchools(allSchools);
                renderSubjects(allSubjects);

                const schoolsCount = document.getElementById('stat-schools-count');
                if (schoolsCount) schoolsCount.textContent = allSchools.length;

                const selectSubject = document.getElementById('prof-subject-select');
                if (selectSubject) {
                    selectSubject.innerHTML = '<option value="">-- Seleccionar Materia --</option>';
                    data.subjects.forEach(s => {
                        selectSubject.innerHTML += `<option value="${s.id}">${s.code} - ${s.name}</option>`;
                    });

                    selectSubject.addEventListener('change', () => {
                        loadGradesForSubject(selectSubject.value);
                    });
                }
            }
        });
}

function renderSchools(schools) {
    const tbody = document.getElementById('schools-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    schools.forEach(s => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="hash-tag">${escapeHtml(s.code)}</span></td>
            <td><strong>${escapeHtml(s.name)}</strong></td>
            <td>${escapeHtml(s.description || '')}</td>
            <td>
                <div class="btn-action-group">
                    <button class="btn-action-icon btn-action-edit btn-edit-school" data-id="${s.id}" title="Editar Escuela">✏️</button>
                    <button class="btn-action-icon btn-action-delete btn-del-school" data-id="${s.id}" title="Eliminar Escuela">🗑️</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.btn-edit-school').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const s = allSchools.find(x => x.id === id);
            if (!s) return;
            document.getElementById('school-id').value = s.id;
            document.getElementById('school-code').value = s.code;
            document.getElementById('school-name').value = s.name;
            document.getElementById('school-desc').value = s.description || '';
            document.getElementById('modal-school').classList.add('active');
        });
    });

    tbody.querySelectorAll('.btn-del-school').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (confirm('¿Borrar esta escuela?')) {
                fetch('api/academico.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_school', id })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        loadAcademic();
                        showToast('Escuela eliminada');
                    }
                });
            }
        });
    });
}

function renderSubjects(subjects) {
    const tbody = document.getElementById('subjects-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    subjects.forEach(sub => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><span class="hash-tag">${escapeHtml(sub.code)}</span></td>
            <td><strong>${escapeHtml(sub.name)}</strong></td>
            <td>${escapeHtml(sub.teacher_name || 'Prof. Manuel Alfonzo')}</td>
            <td>
                <div class="btn-action-group">
                    <button class="btn-action-icon btn-action-edit btn-edit-subject" data-id="${sub.id}" title="Editar Materia">✏️</button>
                    <button class="btn-action-icon btn-action-delete btn-del-subject" data-id="${sub.id}" title="Eliminar Materia">🗑️</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    tbody.querySelectorAll('.btn-edit-subject').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            const sub = allSubjects.find(x => x.id === id);
            if (!sub) return;
            document.getElementById('subject-id').value = sub.id;
            document.getElementById('subject-code').value = sub.code;
            document.getElementById('subject-name').value = sub.name;
            document.getElementById('subject-teacher').value = sub.teacher_name || '';
            document.getElementById('modal-subject').classList.add('active');
        });
    });

    tbody.querySelectorAll('.btn-del-subject').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const id = e.currentTarget.getAttribute('data-id');
            if (confirm('¿Borrar esta materia?')) {
                fetch('api/academico.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_subject', id })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        loadAcademic();
                        showToast('Materia eliminada');
                    }
                });
            }
        });
    });
}

function loadGradesForSubject(subjectId) {
    fetch(`api/notas.php?subject_id=${subjectId}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderGrades(data.grades);
            }
        });
}

function renderGrades(grades) {
    const tbody = document.getElementById('grades-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (grades.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 2rem;">Seleccione una materia para evaluar a los estudiantes.</td></tr>`;
        return;
    }

    grades.forEach(g => {
        const tr = document.createElement('tr');
        const isApproved = g.final_grade >= 9.5;
        const pillClass = isApproved ? 'grade-approved' : 'grade-failed';

        tr.innerHTML = `
            <td><strong>${escapeHtml(g.student_name)}</strong></td>
            <td>${escapeHtml(g.student_ci)}</td>
            <td><input type="number" step="0.5" min="0" max="20" class="search-input corta-1" style="width: 80px;" value="${g.corta1}" data-id="${g.id}"></td>
            <td><input type="number" step="0.5" min="0" max="20" class="search-input corta-2" style="width: 80px;" value="${g.corta2}" data-id="${g.id}"></td>
            <td><input type="number" step="0.5" min="0" max="20" class="search-input corta-3" style="width: 80px;" value="${g.corta3}" data-id="${g.id}"></td>
            <td><span class="final-val ${pillClass}">${g.final_grade.toFixed(2)} pts</span></td>
            <td><input type="text" class="search-input obs-val" style="width: 100%;" value="${escapeHtml(g.observations || '')}"></td>
            <td><button class="btn btn-primary btn-save-grade" data-id="${g.id}">Guardar</button></td>
        `;
        tbody.appendChild(tr);

        const recalculate = () => {
            const c1 = parseFloat(tr.querySelector('.corta-1').value) || 0;
            const c2 = parseFloat(tr.querySelector('.corta-2').value) || 0;
            const c3 = parseFloat(tr.querySelector('.corta-3').value) || 0;
            const finalVal = (c1 * 0.3) + (c2 * 0.3) + (c3 * 0.4);
            const span = tr.querySelector('.final-val');
            span.textContent = finalVal.toFixed(2) + ' pts';
            span.className = 'final-val ' + (finalVal >= 9.5 ? 'grade-approved' : 'grade-failed');
        };

        tr.querySelector('.corta-1').addEventListener('input', recalculate);
        tr.querySelector('.corta-2').addEventListener('input', recalculate);
        tr.querySelector('.corta-3').addEventListener('input', recalculate);
    });

    tbody.querySelectorAll('.btn-save-grade').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tr = e.target.closest('tr');
            const id = e.target.getAttribute('data-id');
            const c1 = tr.querySelector('.corta-1').value;
            const c2 = tr.querySelector('.corta-2').value;
            const c3 = tr.querySelector('.corta-3').value;
            const obs = tr.querySelector('.obs-val').value;

            fetch('api/notas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, corta1: c1, corta2: c2, corta3: c3, observations: obs })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    const span = tr.querySelector('.final-val');
                    span.textContent = res.grade.final_grade.toFixed(2) + ' pts';
                    span.className = 'final-val ' + (res.grade.final_grade >= 9.5 ? 'grade-approved' : 'grade-failed');
                    showToast('Calificación registrada y firmada en auditoría SHA-256');
                    if (currentUser && currentUser.role === 'admin') loadAuditLogs();
                } else {
                    alert(res.error);
                }
            });
        });
    });
}

function loadAuditLogs() {
    fetch('api/auditoria.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderAuditLogs(data.audit_logs);
            }
        });
}

function renderAuditLogs(logs) {
    const tbody = document.getElementById('audit-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (logs.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center; padding: 2rem;">No hay registros de auditoría.</td></tr>`;
        return;
    }

    logs.forEach(l => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><small>${l.created_at}</small></td>
            <td><span class="user-role-badge badge-admin">${escapeHtml(l.action)}</span></td>
            <td><strong>${escapeHtml(l.table_name)}</strong></td>
            <td>${escapeHtml(l.user_email || 'sistema')} (${escapeHtml(l.user_role || 'guest')})</td>
            <td><span class="hash-tag" title="Firmado SHA-256: ${l.hash_checksum}">${l.hash_checksum.substring(0, 16)}...</span></td>
        `;
        tbody.appendChild(tr);
    });
}

// ============================================================================
// ENCAPSULATED AUTO-HIDING MINI FOOTER ENGINE
// Protected Credits: Ing. Oscar Franco & Ing. Jesus Villalba
// ============================================================================
function initMiniFooter() {
    const footerObj = Object.freeze({
        architects: ['Ing. Oscar Franco', 'Ing. Jesus Villalba'],
        institution: 'Politécnico Santiago Mariño — Extensión Porlamar',
        version: 'v2.4.0-PROD'
    });

    let lastScrollY = window.scrollY;
    const footer = document.getElementById('system-mini-footer');
    if (!footer) return;

    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        if (currentScrollY > lastScrollY && currentScrollY > 60) {
            footer.classList.add('mini-footer-hidden');
        } else {
            footer.classList.remove('mini-footer-hidden');
        }
        lastScrollY = currentScrollY;
    });

    document.addEventListener('mousemove', (e) => {
        if (window.innerHeight - e.clientY < 70) {
            footer.classList.remove('mini-footer-hidden');
        }
    });

    // Anti-tamper Observer
    const observer = new MutationObserver(() => {
        if (!document.getElementById('system-mini-footer')) {
            recreateFooter(footerObj);
        }
    });
    observer.observe(document.body, { childList: true, subtree: true });
}

function recreateFooter(info) {
    const f = document.createElement('footer');
    f.id = 'system-mini-footer';
    f.innerHTML = `
        <div class="mini-footer-credits">
            <span class="eng-badge">⚡ ${info.architects[0]}</span>
            <span class="eng-badge">⚡ ${info.architects[1]}</span>
            <span style="opacity: 0.8; font-weight: 500;">Lead Software Architects</span>
        </div>
        <div class="mini-footer-meta">
            <span>${info.institution}</span>
            <span style="color: var(--accent); font-weight: 700;">${info.version}</span>
            <span style="font-family: monospace; opacity: 0.6;">🔒 SHA-256 Verified</span>
        </div>
    `;
    document.body.appendChild(f);
}

function showToast(msg) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = 'position:fixed; bottom:20px; right:20px; z-index:9999; display:flex; flex-direction:column; gap:10px;';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = 'glass-panel';
    toast.style.cssText = 'padding: 1rem 1.5rem; border-left: 4px solid var(--success); color: #fff; background: rgba(10, 17, 40, 0.95); box-shadow: 0 10px 25px rgba(0,0,0,0.5); border-radius: 12px; font-weight: 500; font-size: 0.9rem; animation: fadeIn 0.3s;';
    toast.innerHTML = `✅ ${escapeHtml(msg)}`;
    container.appendChild(toast);

    setTimeout(() => toast.remove(), 4000);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
