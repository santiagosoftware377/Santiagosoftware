<?php
// ============================================================================
// CONEXION A SUPABASE REST API & PDO DATABASE HELPER (SANTIAGO SOFTWARE)
// ============================================================================

if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2) + [NULL, NULL];
        if ($name && $value) {
            putenv(trim($name) . "=" . trim($value));
            $_ENV[trim($name)] = trim($value);
        }
    }
}

define('SUPABASE_URL', getenv('SUPABASE_URL') ?: 'https://vfngujnetjibgqgnjpox.supabase.co');
define('SUPABASE_KEY', getenv('SUPABASE_ANON_KEY') ?: 'sb_publishable_E2waoH8UOXywSDrM3RVqvg_AuP-q6MY');

/**
 * Petición cURL genérica a Supabase REST API
 */
function supabase_request($method, $endpoint, $data = null) {
    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/' . ltrim($endpoint, '/');
    $ch = curl_init($url);
    
    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
        'Prefer: return=representation'
    ];
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    
    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }
    
    return null;
}

/**
 * Conexión PDO opcional a PostgreSQL
 */
function get_pdo_connection() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    
    $host = getenv('DB_HOST') ?: 'vfngujnetjibgqgnjpox.supabase.co';
    $port = getenv('DB_PORT') ?: '5432';
    $dbname = getenv('DB_NAME') ?: 'postgres';
    $user = getenv('DB_USER') ?: 'postgres';
    $password = getenv('DB_PASSWORD') ?: '';
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}
