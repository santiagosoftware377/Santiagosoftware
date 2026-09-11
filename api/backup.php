<?php
// ============================================================================
// API DE RESPALDO Y RESTAURACION DE BASE DE DATOS (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Acceso restringido a administradores.']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. EXPORTAR RESPALDO DE SEGURIDAD (JSON/SQL DUMP)
if ($action === 'export') {
    $backupData = [
        'metadata' => [
            'system' => 'Politécnico Santiago Mariño - Extensión Porlamar',
            'version' => '2.0.0',
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => $_SESSION['user']['email'] ?? 'admin'
        ],
        'schools' => $_SESSION['schools'] ?? [],
        'subjects' => $_SESSION['subjects'] ?? [],
        'leads' => $_SESSION['leads'] ?? [],
        'reincorporaciones' => $_SESSION['reincorporaciones'] ?? [],
        'grades' => $_SESSION['grades'] ?? [],
        'users' => $_SESSION['users'] ?? [],
        'audit_logs' => $_SESSION['audit_logs'] ?? []
    ];

    log_audit('system', 'EXPORT_BACKUP', null, null, ['files_exported' => count($backupData['leads'])]);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="santiago_software_backup_' . date('Y-m-d_H-i') . '.json"');
    echo json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// 2. RESTAURAR RESPALDO
if ($action === 'restore') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    
    if (empty($input['backup_data'])) {
        echo json_encode(['success' => false, 'error' => 'Archivo de respaldo inválido o vacío.']);
        exit;
    }

    $data = $input['backup_data'];
    if (is_string($data)) {
        $data = json_decode($data, true);
    }

    if (!is_array($data)) {
        echo json_encode(['success' => false, 'error' => 'El formato del archivo de respaldo no es válido.']);
        exit;
    }

    if (isset($data['leads'])) $_SESSION['leads'] = $data['leads'];
    if (isset($data['reincorporaciones'])) $_SESSION['reincorporaciones'] = $data['reincorporaciones'];
    if (isset($data['schools'])) $_SESSION['schools'] = $data['schools'];
    if (isset($data['subjects'])) $_SESSION['subjects'] = $data['subjects'];
    if (isset($data['grades'])) $_SESSION['grades'] = $data['grades'];

    log_audit('system', 'RESTORE_BACKUP', null, null, ['restored_by' => $_SESSION['user']['email']]);

    echo json_encode(['success' => true, 'message' => 'Base de datos restaurada correctamente.']);
    exit;
}

// 3. REINICIALIZAR / VACIAR REGISTROS EN 0
if ($action === 'reset_zero') {
    $_SESSION['leads'] = [];
    $_SESSION['reincorporaciones'] = [];
    $_SESSION['grades'] = [];
    
    log_audit('system', 'RESET_ZERO_RECORDS', null, null, ['cleared_by' => $_SESSION['user']['email']]);
    
    echo json_encode(['success' => true, 'message' => 'Todos los registros han sido inicializados en 0.']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Acción no válida.']);
