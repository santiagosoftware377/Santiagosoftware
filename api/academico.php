<?php
// ============================================================================
// API ACADEMICA COMPLETA - ESCUELAS, MATERIAS Y PROFESORES (SANTIAGO SOFTWARE)
// ============================================================================

header('Content-Type: application/json');
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

function get_initial_schools() {
    return [
        ['id' => 's1', 'code' => 'ING-CIVIL', 'name' => 'Ingeniería Civil', 'description' => 'Escuela de Ingeniería Civil'],
        ['id' => 's2', 'code' => 'ING-ELEC', 'name' => 'Ingeniería Electrónica', 'description' => 'Escuela de Ingeniería Electrónica'],
        ['id' => 's3', 'code' => 'ING-ELEK', 'name' => 'Ingeniería Eléctrica', 'description' => 'Escuela de Ingeniería Eléctrica'],
        ['id' => 's4', 'code' => 'ING-SIST', 'name' => 'Ingeniería de Sistemas', 'description' => 'Escuela de Ingeniería de Sistemas'],
        ['id' => 's5', 'code' => 'ING-QUIM', 'name' => 'Ingeniería Química', 'description' => 'Escuela de Ingeniería Química'],
        ['id' => 's6', 'code' => 'ING-MEC', 'name' => 'Ingeniería Mecánica (Mtto)', 'description' => 'Escuela de Ingeniería Mecánica'],
        ['id' => 's7', 'code' => 'ING-IND', 'name' => 'Ingeniería Industrial', 'description' => 'Escuela de Ingeniería Industrial'],
        ['id' => 's8', 'code' => 'ARQ', 'name' => 'Arquitectura', 'description' => 'Escuela de Arquitectura'],
        ['id' => 's9', 'code' => 'POR-DECIDIR', 'name' => 'Por Decidir', 'description' => 'Sin Escuela Definida']
    ];
}

function get_initial_subjects() {
    return [
        ['id' => 'sub1', 'school_id' => 's4', 'code' => 'SIS-101', 'name' => 'Algoritmos y Programación I', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'teacher_name' => 'Prof. Manuel Alfonzo'],
        ['id' => 'sub2', 'school_id' => 's4', 'code' => 'SIS-202', 'name' => 'Bases de Datos Avanzadas', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'teacher_name' => 'Prof. Manuel Alfonzo'],
        ['id' => 'sub3', 'school_id' => 's1', 'code' => 'CIV-101', 'name' => 'Mecánica de Suelos', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'teacher_name' => 'Prof. Manuel Alfonzo'],
        ['id' => 'sub4', 'school_id' => 's8', 'code' => 'ARQ-105', 'name' => 'Diseño Arquitectónico I', 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'teacher_name' => 'Prof. Manuel Alfonzo']
    ];
}

if (!isset($_SESSION['schools'])) {
    $_SESSION['schools'] = get_initial_schools();
}
if (!isset($_SESSION['subjects'])) {
    $_SESSION['subjects'] = get_initial_subjects();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode([
        'success' => true,
        'schools' => $_SESSION['schools'],
        'subjects' => $_SESSION['subjects']
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?: $_POST;
    $action = $input['action'] ?? '';

    // CRUD ESCUELAS
    if ($action === 'save_school') {
        $id = $input['id'] ?? sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $code = trim($input['code'] ?? '');
        $name = trim($input['name'] ?? '');
        $description = trim($input['description'] ?? '');

        if (empty($code) || empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Código y Nombre de la Escuela son obligatorios.']);
            exit;
        }

        $isEdit = false;
        foreach ($_SESSION['schools'] as &$s) {
            if ($s['id'] === $id) {
                $old = $s;
                $s['code'] = $code;
                $s['name'] = $name;
                $s['description'] = $description;
                $isEdit = true;
                log_audit('schools', 'UPDATE_SCHOOL', $id, $old, $s);
                break;
            }
        }

        if (!$isEdit) {
            $newSchool = ['id' => $id, 'code' => $code, 'name' => $name, 'description' => $description];
            $_SESSION['schools'][] = $newSchool;
            log_audit('schools', 'INSERT_SCHOOL', $id, null, $newSchool);
        }

        echo json_encode(['success' => true, 'schools' => $_SESSION['schools']]);
        exit;
    }

    if ($action === 'delete_school') {
        $id = $input['id'] ?? '';
        $_SESSION['schools'] = array_values(array_filter($_SESSION['schools'], function($s) use ($id) { return $s['id'] !== $id; }));
        log_audit('schools', 'DELETE_SCHOOL', $id, null, null);
        echo json_encode(['success' => true, 'schools' => $_SESSION['schools']]);
        exit;
    }

    // CRUD MATERIAS
    if ($action === 'save_subject') {
        $id = $input['id'] ?? sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
        $code = trim($input['code'] ?? '');
        $name = trim($input['name'] ?? '');
        $school_id = $input['school_id'] ?? '';
        $teacher_name = trim($input['teacher_name'] ?? 'Prof. Manuel Alfonzo');

        if (empty($code) || empty($name)) {
            echo json_encode(['success' => false, 'error' => 'Código y Nombre de la Materia son obligatorios.']);
            exit;
        }

        $isEdit = false;
        foreach ($_SESSION['subjects'] as &$sub) {
            if ($sub['id'] === $id) {
                $old = $sub;
                $sub['code'] = $code;
                $sub['name'] = $name;
                $sub['school_id'] = $school_id;
                $sub['teacher_name'] = $teacher_name;
                $isEdit = true;
                log_audit('subjects', 'UPDATE_SUBJECT', $id, $old, $sub);
                break;
            }
        }

        if (!$isEdit) {
            $newSubject = ['id' => $id, 'code' => $code, 'name' => $name, 'school_id' => $school_id, 'teacher_id' => 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'teacher_name' => $teacher_name];
            $_SESSION['subjects'][] = $newSubject;
            log_audit('subjects', 'INSERT_SUBJECT', $id, null, $newSubject);
        }

        echo json_encode(['success' => true, 'subjects' => $_SESSION['subjects']]);
        exit;
    }

    if ($action === 'delete_subject') {
        $id = $input['id'] ?? '';
        $_SESSION['subjects'] = array_values(array_filter($_SESSION['subjects'], function($sub) use ($id) { return $sub['id'] !== $id; }));
        log_audit('subjects', 'DELETE_SUBJECT', $id, null, null);
        echo json_encode(['success' => true, 'subjects' => $_SESSION['subjects']]);
        exit;
    }
}
