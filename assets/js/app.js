// ============================================================================
// SANTIAGO SOFTWARE - CLIENT JS CONTROLLER & INTERACTIVE ENGINE
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    initTabs();
    initApp();
});

let currentUser = null;

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
                updateUserUI(data.user);
                loadLeads();
                loadReincorporaciones();
                loadAcademic();
                if (currentUser.role === 'admin') {
                    loadAuditLogs();
                }
            } else {
                if (!window.location.pathname.endsWith('index.php') && window.location.pathname !== '/') {
                    window.location.href = 'index.php';
                }
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
    
    // Ocultar o mostrar elementos según rol
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
            fetch('api/auth.php?action=logout')
                .then(() => window.location.href = 'index.php');
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

    // Filtros de búsqueda en captación
    const searchLead = document.getElementById('search-lead');
    if (searchLead) {
        searchLead.addEventListener('input', filterLeads);
    }
}

let allLeads = [];

function loadLeads() {
    fetch('api/captacion.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                allLeads = data.leads;
                renderLeads(allLeads);
            }
        });
}

function renderLeads(leads) {
    const tbody = document.getElementById('leads-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (leads.length === 0) {
        tbody.innerHTML = `<tr><td colspan="15" style="text-align:center; color: var(--text-secondary); padding: 2rem;">No hay registros de aspirantes.</td></tr>`;
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
        `;
        tbody.appendChild(tr);
    });

    // Eventos para cambiar checkboxes en tiempo real con auditoría
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
                    if (currentUser.role === 'admin') loadAuditLogs();
                }
            });
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
                renderReincorporaciones(data.reincorporaciones);
            }
        });
}

function renderReincorporaciones(list) {
    const tbody = document.getElementById('reinc-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

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
}

function loadAcademic() {
    fetch('api/academico.php')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
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

        // Recálculo dinámico al teclear las notas
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
                    if (currentUser.role === 'admin') loadAuditLogs();
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
    toast.style.cssText = 'padding: 1rem 1.5rem; border-left: 4px solid var(--success); color: #fff; background: rgba(15, 23, 42, 0.95); box-shadow: 0 10px 25px rgba(0,0,0,0.5); border-radius: 12px; font-weight: 500; font-size: 0.9rem;';
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
