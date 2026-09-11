<?php
// ============================================================================
// MOTOR DE SEGURIDAD, AUDITORIA Y CONTROL DE ACCESO EN PHP (SANTIAGO SOFTWARE)
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

/**
 * Hashing seguro de contraseña con Bcrypt
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verificación de contraseña hasheada
 */
function verify_password($password, $hash) {
    if (strpos($hash, '$2y$') !== 0 && strpos($hash, '$2a$') !== 0) {
        return $password === $hash;
    }
    return password_verify($password, $hash);
}

/**
 * Generación de token CSRF
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Auditoría Criptográfica con SHA-256
 */
function log_audit($tableName, $action, $recordId, $oldData = null, $newData = null) {
    require_once __DIR__ . '/database.php';
    
    $userEmail = $_SESSION['user']['email'] ?? 'sistema';
    $userRole = $_SESSION['user']['role'] ?? 'guest';
    $userId = $_SESSION['user']['id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $timestamp = date('Y-m-d H:i:s');
    
    $payload = $tableName . $action . $userEmail . $timestamp . json_encode($newData);
    $hashChecksum = hash('sha256', $payload);
    
    $auditEntry = [
        'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
        'table_name' => $tableName,
        'action' => strtoupper($action),
        'record_id' => $recordId,
        'old_data' => $oldData,
        'new_data' => $newData,
        'performed_by' => $userId,
        'user_email' => $userEmail,
        'user_role' => $userRole,
        'ip_address' => $ipAddress,
        'hash_checksum' => $hashChecksum,
        'created_at' => $timestamp
    ];
    
    if (!isset($_SESSION['audit_logs'])) {
        $_SESSION['audit_logs'] = [];
    }
    array_unshift($_SESSION['audit_logs'], $auditEntry);
    
    supabase_request('POST', 'audit_logs', $auditEntry);
    
    return $auditEntry;
}
