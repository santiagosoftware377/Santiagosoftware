<?php
// ============================================================================
// API DE REINCORPORACIONES (ESTUDIANTES REGULARES) (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

require_auth('admin');

function get_initial_reincorporaciones() {
    return [
        [
            "id" => "r2000000-0000-0000-0000-000000000001",
            "full_name" => "Alondra Marval",
            "ci" => "28570556",
            "phone" => "0424-8966606",
            "email" => "alondramarval09@gmail.com",
            "carrera_cursar" => "Arquitectura",
            "remitido_por" => "Elifrank Salazar",
            "student_status" => "Reincorporación Regular (Por Inscribir)",
            "responsible" => "Manuel",
            "se_inscribio" => false
        ],
        [
            "id" => "r2000000-0000-0000-0000-000000000002",
            "full_name" => "ENYER MARCANO",
            "ci" => "",
            "phone" => "0412-9809261",
            "email" => "",
            "carrera_cursar" => "Ingeniería de Sistemas",
            "remitido_por" => "JAVIER AMUNDARAY",
            "student_status" => "Reincorporación Regular (Por Inscribir)",
            "responsible" => "Manuel",
            "se_inscribio" => false
        ],
        [
            "id" => "r2000000-0000-0000-0000-000000000003",
            "full_name" => "JOSE MOISES",
            "ci" => "",
            "phone" => "0412-3597892",
            "email" => "",
            "carrera_cursar" => "Ingeniería Mecánica (Mtto)",
            "remitido_por" => "JAVIER AMUNDARAY",
            "student_status" => "Reincorporación Regular (Por Inscribir)",
            "responsible" => "Manuel",
            "se_inscribio" => false
        ],
        [
            "id" => "r2000000-0000-0000-0000-000000000004",
            "full_name" => "WILFREDO GONZALEZ",
            "ci" => "",
            "phone" => "0414-8016892",
            "email" => "",
            "carrera_cursar" => "Ingeniería Industrial",
            "remitido_por" => "JAVIER AMUNDARAY",
            "student_status" => "Reincorporación Regular (Por Inscribir)",
            "responsible" => "Manuel",
            "se_inscribio" => false
        ],
        [
            "id" => "r2000000-0000-0000-0000-000000000005",
            "full_name" => "SEBASTIAN SALAZAR",
            "ci" => "28.468.724",
            "phone" => "0424-8133713",
            "email" => "",
            "carrera_cursar" => "Ingeniería Química",
            "remitido_por" => "LOLYMAR JIMENEZ",
            "student_status" => "Reincorporación Regular (Por Inscribir)",
            "responsible" => "Manuel",
            "se_inscribio" => false
        ]
    ];
}

if (!isset($_SESSION['reincorporaciones'])) {
    $_SESSION['reincorporaciones'] = get_initial_reincorporaciones();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(['success' => true, 'reincorporaciones' => $_SESSION['reincorporaciones']]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    $action = $input['action'] ?? 'save';
    
    if ($action === 'toggle_inscripto') {
        $id = $input['id'] ?? '';
        $val = filter_var($input['se_inscribio'] ?? false, FILTER_VALIDATE_BOOLEAN);
        foreach ($_SESSION['reincorporaciones'] as &$item) {
            if ($item['id'] === $id) {
                $old = $item;
                $item['se_inscribio'] = $val;
                log_audit('reincorporaciones', 'UPDATE_STATUS', $id, $old, $item);
                break;
            }
        }
        echo json_encode(['success' => true]);
        exit;
    }
}
