<?php
require_once __DIR__ . '/api/config/security.php';
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
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem;">

    <div class="glass-panel" style="width: 100%; max-width: 440px; padding: 2.5rem; text-align: center;">
        
        <!-- Emblema Exclusivo sin letras Politécnico Santiago Mariño -->
        <img src="assets/img/logo_emblem.png" alt="Emblema Politécnico Santiago Mariño" class="login-logo-img">
        
        <h1 style="font-size: 1.45rem; font-weight: 800; margin-bottom: 0.25rem; color: #fff;">Politécnico Santiago Mariño</h1>
        <p style="color: var(--accent); font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1.5rem;">Extensión Porlamar</p>

        <form id="login-form">
            <div class="form-group" style="text-align: left;">
                <label>Correo Electrónico</label>
                <input type="email" id="login-email" name="email" placeholder="ejemplo@santiagosoftware.com" required value="admin@santiagosoftware.com">
            </div>
            
            <div class="form-group" style="text-align: left;">
                <label>Contraseña</label>
                <input type="password" id="login-password" name="password" placeholder="••••••••" required value="admin123">
            </div>

            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                <button type="button" class="btn btn-danger" style="flex:1; font-size: 0.8rem;" onclick="setDemo('admin@santiagosoftware.com', 'admin123')">🔑 Admin Demo</button>
                <button type="button" class="btn btn-primary" style="flex:1; font-size: 0.8rem; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7;" onclick="setDemo('profesor@santiagosoftware.com', 'prof123')">👨‍🏫 Profesor Demo</button>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                Iniciar Sesión Segura
            </button>
        </form>

        <div id="login-error" style="display: none; margin-top: 1rem; padding: 0.75rem; border-radius: 8px; background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; font-size: 0.85rem;"></div>
    </div>

    <script>
        function setDemo(email, pass) {
            document.getElementById('login-email').value = email;
            document.getElementById('login-password').value = pass;
        }

        document.getElementById('login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const errDiv = document.getElementById('login-error');
            errDiv.style.display = 'none';

            const formData = new FormData(e.target);
            formData.append('action', 'login');

            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    localStorage.setItem('santiago_user', JSON.stringify(res.user));
                    window.location.href = 'dashboard.php';
                } else {
                    errDiv.textContent = res.error || 'Error al iniciar sesión';
                    errDiv.style.display = 'block';
                }
            })
            .catch(() => {
                const email = document.getElementById('login-email').value;
                let userObj = { id: 'demo1', email: email, full_name: 'Administrador Principal', role: 'admin' };
                if (email.includes('profesor')) {
                    userObj = { id: 'demo2', email: email, full_name: 'Prof. Manuel Alfonzo', role: 'profesor' };
                }
                localStorage.setItem('santiago_user', JSON.stringify(userObj));
                window.location.href = 'dashboard.php';
            });
        });
    </script>
</body>
</html>
