<?php
require_once __DIR__ . '/../config/security.php';

// Si ya está autenticado, redirigir al dashboard
if (!empty($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Santiago Software - Control de Gestión & Portal Académico</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body style="display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 1.5rem;">

    <div class="glass-panel" style="width: 100%; max-width: 440px; padding: 2.5rem; text-align: center;">
        <div style="width: 56px; height: 56px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; font-weight: 800; color: #fff; margin-bottom: 1.25rem; box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);">
            SS
        </div>
        
        <h1 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff;">Santiago Software</h1>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 2rem;">Acceso al Sistema de Captación & Portal de Profesores</p>

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
                    window.location.href = 'dashboard.php';
                } else {
                    errDiv.textContent = res.error || 'Error al iniciar sesión';
                    errDiv.style.display = 'block';
                }
            })
            .catch(() => {
                errDiv.textContent = 'Error de conexión con el servidor.';
                errDiv.style.display = 'block';
            });
        });
    </script>
</body>
</html>
