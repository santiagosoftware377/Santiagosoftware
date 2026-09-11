<?php
// ============================================================================
// API DE CONTROL DE GESTION DE CAPTACION - ASPIRANTES (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

require_auth('admin');

$method = $_SERVER['REQUEST_METHOD'];

/**
 * Cargar o sembrar datos iniciales provistos por la institución
 */
function get_initial_leads() {
    return [
        [
            "id" => "c1000000-0000-0000-0000-000000000001",
            "period" => "2026-2",
            "full_name" => "Victoria Novoa Calojero",
            "ci" => "33232308",
            "phone" => "0412-9827554",
            "email" => "novoavctr@gmail.com",
            "address" => "Porlamar, Mcpio. Mariño",
            "high_school" => "UE Educacional Porlamar",
            "carrera_cursar" => "Por Decidir",
            "referred_by" => "ELIFRANK SALAZAR",
            "contact_date" => "2026-08-04",
            "channel" => "WhatsApp",
            "call_date" => "2026-08-05",
            "call_status" => "SI",
            "contact_result" => "SE LE BRINDO ASESORIA A LA ESTUDIANTE EL CUAL NOTIFICO QUE QUERIA ESTUDIAR INGENIERIA GEOLOGICA, YA QUE NO CONTAMOS SE LE OFRECIO INGENIERIA CIVIL Y SE LE BRINDO LA INFORMACION",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000002",
            "period" => "2026-2",
            "full_name" => "JUAN DIEGO ROMERO",
            "ci" => "31.455.814",
            "phone" => "0424-8462796",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería Electrónica",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-05",
            "channel" => "WhatsApp",
            "call_date" => "2026-08-05",
            "call_status" => "NO_RESPONDE",
            "contact_result" => "NO CONTESTO Y SE LE DEJO MENSAJE VIA WHATSAPP",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000003",
            "period" => "2026-2",
            "full_name" => "JEIVER ALEXIS PEREIRA RUBIN",
            "ci" => "",
            "phone" => "0424-4210871",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería Eléctrica",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-05",
            "channel" => "WhatsApp",
            "call_date" => "2026-08-07",
            "call_status" => "EN_ESPERA",
            "contact_result" => "",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000004",
            "period" => "2026-2",
            "full_name" => "ANGEL DAVID GIL",
            "ci" => "31993749",
            "phone" => "0426-5872691",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería de Sistemas",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-05",
            "channel" => "WhatsApp",
            "call_date" => "2026-08-05",
            "call_status" => "NO_RESPONDE",
            "contact_result" => "SE LE DEJO MENSAJE POR WHATSAPP, LEYO MENSAJE SIN SOLICITAR INFORMACION",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000005",
            "period" => "2026-2",
            "full_name" => "ALEJANDRO GABRIEL TURRI",
            "ci" => "33523685",
            "phone" => "0412-0888043",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería de Sistemas",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-05",
            "channel" => "LLAMADA_WHATSAPP",
            "call_date" => "2026-08-05",
            "call_status" => "SI",
            "contact_result" => "QUERIA INFORMACION DEL PREUNIVERSITARIO Y SE LE BRINDO TODA LA INFORMACION ASI COMO DE LA CARRERA",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000006",
            "period" => "2026-2",
            "full_name" => "HABIB GABRIEL OÑATE H.",
            "ci" => "V-30.065.529",
            "phone" => "0412-6032791",
            "email" => "habibonateucv@gmail.com",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería Electrónica",
            "referred_by" => "MILAGROS CEDEÑO",
            "contact_date" => "2026-07-14",
            "channel" => "ATENCION_PERSONALIZADA",
            "call_date" => "2026-08-07",
            "call_status" => "SI",
            "contact_result" => "ATENCION PERSONALIZADA EN LA SEDE",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => true,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000008",
            "period" => "2026-2",
            "full_name" => "MELBIN JAVIER CALDERIN BOADAS",
            "ci" => "34162204",
            "phone" => "0412-7266949",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería Civil",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-05",
            "channel" => "LLAMADA",
            "call_date" => "2026-08-05",
            "call_status" => "SI",
            "contact_result" => "CONFIRMO E INSCRITO EN SISTEMA",
            "se_inscribio_link_pre_univ" => true,
            "se_inscribio_pre_universitario" => true,
            "asistio_pre_universitario" => true,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => true,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => true
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000009",
            "period" => "2026-2",
            "full_name" => "ANDREA MENDEZ",
            "ci" => "",
            "phone" => "0414-9677046",
            "email" => "",
            "address" => "",
            "high_school" => "",
            "carrera_cursar" => "Arquitectura",
            "referred_by" => "MILAGROS CEDEÑO",
            "contact_date" => "2026-08-03",
            "channel" => "LLAMADA",
            "call_date" => "2026-08-05",
            "call_status" => "SI",
            "contact_result" => "INTERESADA EN ARQUITECTURA",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => false,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ],
        [
            "id" => "c1000000-0000-0000-0000-000000000011",
            "period" => "2026-2",
            "full_name" => "SAMUEL TORRES DÍAZ",
            "ci" => "29864334",
            "phone" => "0414-3836006",
            "email" => "sjdd5359@gmail.com",
            "address" => "PAMPATAR",
            "high_school" => "",
            "carrera_cursar" => "Ingeniería Civil",
            "referred_by" => "AURORI ALFONZO",
            "contact_date" => "2026-08-12",
            "channel" => "LLAMADA",
            "call_date" => "2026-08-12",
            "call_status" => "SI",
            "contact_result" => "INFORMACION ENVIADA POR WHATSAPP",
            "se_inscribio_link_pre_univ" => false,
            "se_inscribio_pre_universitario" => false,
            "asistio_pre_universitario" => false,
            "tiene_dudas_carrera" => false,
            "ya_tiene_definida_carrera" => true,
            "manifesto_no_inscribirse" => false,
            "se_inscribio" => false
        ]
    ];
}

// Inicializar sesión de almacenamiento para leads
if (!isset($_SESSION['leads'])) {
    $_SESSION['leads'] = get_initial_leads();
}

if ($method === 'GET') {
    $remoteData = supabase_request('GET', 'leads?order=created_at.desc');
    $leads = (!empty($remoteData) && is_array($remoteData)) ? $remoteData : $_SESSION['leads'];
    echo json_encode(['success' => true, 'leads' => $leads]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    $action = $input['action'] ?? 'save';
    
    if ($action === 'toggle_flag') {
        $id = $input['id'] ?? '';
        $flag = $input['flag'] ?? '';
        $value = filter_var($input['value'] ?? false, FILTER_VALIDATE_BOOLEAN);
        
        $updatedItem = null;
        foreach ($_SESSION['leads'] as &$lead) {
            if ($lead['id'] === $id) {
                $oldData = $lead;
                $lead[$flag] = $value;
                $updatedItem = $lead;
                log_audit('leads', 'UPDATE_FLAG', $id, $oldData, $lead);
                break;
            }
        }
        
        if ($updatedItem) {
            supabase_request('PATCH', 'leads?id=eq.' . urlencode($id), [$flag => $value]);
            echo json_encode(['success' => true, 'lead' => $updatedItem]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Aspirante no encontrado.']);
        }
        exit;
    }
    
    if ($action === 'create') {
        $newLead = [
            'id' => sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)),
            'period' => trim($input['period'] ?? '2026-2'),
            'full_name' => trim($input['full_name'] ?? ''),
            'ci' => trim($input['ci'] ?? ''),
            'phone' => trim($input['phone'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'address' => trim($input['address'] ?? ''),
            'high_school' => trim($input['high_school'] ?? ''),
            'carrera_cursar' => trim($input['carrera_cursar'] ?? 'Por Decidir'),
            'referred_by' => trim($input['referred_by'] ?? ''),
            'contact_date' => $input['contact_date'] ?? date('Y-m-d'),
            'channel' => $input['channel'] ?? 'WhatsApp',
            'call_status' => $input['call_status'] ?? 'EN_ESPERA',
            'contact_result' => trim($input['contact_result'] ?? ''),
            'se_inscribio_link_pre_univ' => false,
            'se_inscribio_pre_universitario' => false,
            'asistio_pre_universitario' => false,
            'tiene_dudas_carrera' => false,
            'ya_tiene_definida_carrera' => false,
            'manifesto_no_inscribirse' => false,
            'se_inscribio' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        array_unshift($_SESSION['leads'], $newLead);
        supabase_request('POST', 'leads', $newLead);
        log_audit('leads', 'INSERT', $newLead['id'], null, $newLead);
        
        echo json_encode(['success' => true, 'lead' => $newLead]);
        exit;
    }
    
    if ($action === 'update') {
        $id = $input['id'] ?? '';
        $updatedItem = null;
        foreach ($_SESSION['leads'] as &$lead) {
            if ($lead['id'] === $id) {
                $oldData = $lead;
                $lead['full_name'] = trim($input['full_name'] ?? $lead['full_name']);
                $lead['ci'] = trim($input['ci'] ?? $lead['ci']);
                $lead['phone'] = trim($input['phone'] ?? $lead['phone']);
                $lead['email'] = trim($input['email'] ?? $lead['email']);
                $lead['carrera_cursar'] = trim($input['carrera_cursar'] ?? $lead['carrera_cursar']);
                $lead['referred_by'] = trim($input['referred_by'] ?? $lead['referred_by']);
                $lead['channel'] = trim($input['channel'] ?? $lead['channel']);
                $lead['contact_result'] = trim($input['contact_result'] ?? $lead['contact_result']);
                $updatedItem = $lead;
                log_audit('leads', 'UPDATE_LEAD', $id, $oldData, $lead);
                break;
            }
        }
        
        if ($updatedItem) {
            supabase_request('PATCH', 'leads?id=eq.' . urlencode($id), $updatedItem);
            echo json_encode(['success' => true, 'lead' => $updatedItem]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Aspirante no encontrado para actualizar.']);
        }
        exit;
    }

    if ($action === 'delete') {
        $id = $input['id'] ?? '';
        $_SESSION['leads'] = array_values(array_filter($_SESSION['leads'], function($l) use ($id) { return $l['id'] !== $id; }));
        supabase_request('DELETE', 'leads?id=eq.' . urlencode($id));
        log_audit('leads', 'DELETE', $id, null, null);
        echo json_encode(['success' => true]);
        exit;
    }
}
