<?php
// ============================================================================
// CONEXION A SUPABASE REST API & PDO DATABASE HELPER (SANTIAGO SOFTWARE)
// ============================================================================

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
