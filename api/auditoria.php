<?php
// ============================================================================
// API DE CONSOLA DE AUDITORIA Y SEGURIDAD SUPER-ADMIN (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

require_auth('admin'); // Solo Super Admin

$remoteAudit = supabase_request('GET', 'audit_logs?order=created_at.desc');
$logs = (!empty($remoteAudit) && is_array($remoteAudit)) ? $remoteAudit : ($_SESSION['audit_logs'] ?? []);

echo json_encode([
    'success' => true,
    'audit_logs' => $logs
]);
