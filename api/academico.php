<?php
// ============================================================================
// API ACADEMICA - ESCUELAS, MATERIAS Y ASIGNACIONES (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';

require_auth();

function get_initial_academic_structure() {
    return [
        'schools' => [
            ['id' => 's1', 'code' => 'ING-CIVIL', 'name' => 'Ingeniería Civil'],
            ['id' => 's2', 'code' => 'ING-ELEC', 'name' => 'Ingeniería Electrónica'],
            ['id' => 's3', 'code' => 'ING-ELEK', 'name' => 'Ingeniería Eléctrica'],
            ['id' => 's4', 'code' => 'ING-SIST', 'name' => 'Ingeniería de Sistemas'],
            ['id' => 's5', 'code' => 'ING-QUIM', 'name' => 'Ingeniería Química'],
            ['id' => 's6', 'code' => 'ING-MEC', 'name' => 'Ingeniería Mecánica (Mtto)'],
            ['id' => 's7', 'code' => 'ING-IND', 'name' => 'Ingeniería Industrial'],
            ['id' => 's8', 'code' => 'ARQ', 'name' => 'Arquitectura'],
            ['id' => 's9', 'code' => 'POR-DECIDIR', 'name' => 'Por Decidir']
        ],
        'subjects' => [
            ['id' => 'sub1', 'school_id' => 's4', 'code' => 'SIS-101', 'name' => 'Algoritmos y Programación I', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'],
            ['id' => 'sub2', 'school_id' => 's4', 'code' => 'SIS-202', 'name' => 'Bases de Datos Avanzadas', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'],
            ['id' => 'sub3', 'school_id' => 's1', 'code' => 'CIV-101', 'name' => 'Mecánica de Suelos', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb'],
            ['id' => 'sub4', 'school_id' => 's8', 'code' => 'ARQ-105', 'name' => 'Diseño Arquitectónico I', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb']
        ]
    ];
}

$structure = get_initial_academic_structure();

echo json_encode([
    'success' => true,
    'schools' => $structure['schools'],
    'subjects' => $structure['subjects']
]);
