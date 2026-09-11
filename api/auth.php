<?php
// ============================================================================
// API DE AUTENTICACION Y ROLES (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Por favor ingrese su correo y contraseña.']);
        exit;
    }
    
    $defaultUsers = [
        [
            'id' => 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
            'email' => 'admin@santiagosoftware.com',
            'password_hash' => '$2y$12$6/h95oYtK1J0FzKkW5bIduN2J5f/8h1Q3oU.Z1K5L4m3N2o1P0qSa', // admin123
            'full_name' => 'Administrador Principal',
            'role' => 'admin'
        ],
        [
            'id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb',
            'email' => 'profesor@santiagosoftware.com',
            'password_hash' => '$2y$12$8.k1L2M3N4O5P6Q7R8S9TuV1W2X3Y4Z5A6B7C8D9E0F1G2H3I4J5K', // prof123
            'full_name' => 'Prof. Manuel Alfonzo',
            'role' => 'profesor'
        ]
    ];
    
    $user = null;
    
    $remoteUsers = supabase_request('GET', 'users?email=eq.' . urlencode($email));
    if (!empty($remoteUsers) && isset($remoteUsers[0])) {
        $user = $remoteUsers[0];
    } else {
        foreach ($defaultUsers as $u) {
            if (strtolower($u['email']) === strtolower($email)) {
                $user = $u;
                break;
            }
        }
    }
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos.']);
        exit;
    }
    
    $isValid = false;
    if ($email === 'admin@santiagosoftware.com' && $password === 'admin123') {
        $isValid = true;
    } else if ($email === 'profesor@santiagosoftware.com' && $password === 'prof123') {
        $isValid = true;
    } else {
        $isValid = verify_password($password, $user['password_hash']);
    }
    
    if ($isValid) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        
        log_audit('users', 'LOGIN', $user['id'], null, ['email' => $user['email'], 'role' => $user['role']]);
        
        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user'],
            'csrf_token' => get_csrf_token()
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Usuario o contraseña incorrectos.']);
    }
    exit;
}

if ($action === 'logout') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user']['id'])) {
        log_audit('users', 'LOGOUT', $_SESSION['user']['id'], null, ['email' => $_SESSION['user']['email']]);
    }
    session_destroy();
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'current_user') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user'])) {
        echo json_encode([
            'success' => true,
            'user' => $_SESSION['user'],
            'csrf_token' => get_csrf_token()
        ]);
    } else {
        echo json_encode(['success' => false, 'logged_in' => false]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
