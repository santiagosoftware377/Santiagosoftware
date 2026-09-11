<?php
// ============================================================================
// API DE CARGA Y EVALUACION DE NOTAS DE PROFESORES (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';

require_auth(); // Admins and Teachers can access

function get_initial_grades() {
    return [
        [
            'id' => 'g1',
            'student_id' => 'st1',
            'student_name' => 'MELBIN JAVIER CALDERIN BOADAS',
            'student_ci' => '34162204',
            'subject_id' => 'sub1',
            'subject_code' => 'SIS-101',
            'subject_name' => 'Algoritmos y Programación I',
            'period' => '2026-2',
            'corta1' => 18.50,
            'corta2' => 17.00,
            'corta3' => 19.00,
            'final_grade' => 18.25,
            'observations' => 'Excelente rendimiento académico'
        ],
        [
            'id' => 'g2',
            'student_id' => 'st2',
            'student_name' => 'Alondra Marval',
            'student_ci' => '28570556',
            'subject_id' => 'sub4',
            'subject_code' => 'ARQ-105',
            'subject_name' => 'Diseño Arquitectónico I',
            'period' => '2026-2',
            'corta1' => 16.00,
            'corta2' => 15.50,
            'corta3' => 17.00,
            'final_grade' => 16.25,
            'observations' => 'Entregas completadas a tiempo'
        ],
        [
            'id' => 'g3',
            'student_id' => 'st3',
            'student_name' => 'SAMUEL TORRES DÍAZ',
            'student_ci' => '29864334',
            'subject_id' => 'sub3',
            'subject_code' => 'CIV-101',
            'subject_name' => 'Mecánica de Suelos',
            'period' => '2026-2',
            'corta1' => 14.00,
            'corta2' => 12.00,
            'corta3' => 15.00,
            'final_grade' => 13.80,
            'observations' => 'Regular'
        ]
    ];
}

if (!isset($_SESSION['grades'])) {
    $_SESSION['grades'] = get_initial_grades();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $subject_id = $_GET['subject_id'] ?? '';
    $list = $_SESSION['grades'];
    if (!empty($subject_id)) {
        $list = array_filter($list, function($g) use ($subject_id) {
            return $g['subject_id'] === $subject_id;
        });
    }
    echo json_encode(['success' => true, 'grades' => array_values($list)]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    $id = $input['id'] ?? '';
    $corta1 = floatval($input['corta1'] ?? 0);
    $corta2 = floatval($input['corta2'] ?? 0);
    $corta3 = floatval($input['corta3'] ?? 0);
    $observations = trim($input['observations'] ?? '');
    
    // Validación estricta de límites (0 a 20 pts)
    if ($corta1 < 0 || $corta1 > 20 || $corta2 < 0 || $corta2 > 20 || $corta3 < 0 || $corta3 > 20) {
        echo json_encode(['success' => false, 'error' => 'Las notas deben estar entre 0.00 y 20.00 puntos.']);
        exit;
    }
    
    // Cálculo de nota ponderada final
    $final_grade = round(($corta1 * 0.30) + ($corta2 * 0.30) + ($corta3 * 0.40), 2);
    
    $updatedGrade = null;
    foreach ($_SESSION['grades'] as &$g) {
        if ($g['id'] === $id) {
            $old = $g;
            $g['corta1'] = $corta1;
            $g['corta2'] = $corta2;
            $g['corta3'] = $corta3;
            $g['final_grade'] = $final_grade;
            $g['observations'] = $observations;
            $updatedGrade = $g;
            
            // Registro de auditoría
            log_audit('grades', 'UPDATE_GRADE', $id, $old, $g);
            break;
        }
    }
    
    if ($updatedGrade) {
        supabase_request('PATCH', 'grades?id=eq.' . urlencode($id), [
            'corta1' => $corta1,
            'corta2' => $corta2,
            'corta3' => $corta3,
            'final_grade' => $final_grade,
            'observations' => $observations
        ]);
        echo json_encode(['success' => true, 'grade' => $updatedGrade]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registro de nota no encontrado.']);
    }
    exit;
}
