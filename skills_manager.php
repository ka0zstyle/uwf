<?php
/**
 * Skills Manager - Backend API for managing skills
 * Handles CRUD operations for skills displayed on the website
 */

header('Content-Type: application/json');

// Simple authentication - change this password in production
define('ADMIN_PASSWORD', 'ultrawebforge2024');

// Skills data file
$skills_file = __DIR__ . '/data/skills.json';

// Ensure data directory exists
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

// Initialize skills file if it doesn't exist
if (!file_exists($skills_file)) {
    $default_skills = [
        [
            'id' => 1,
            'name_en' => 'Website Development',
            'name_es' => 'Desarrollo de Sitios Web',
            'percentage' => 84
        ],
        [
            'id' => 2,
            'name_en' => 'SEO & Marketing',
            'name_es' => 'SEO y Marketing',
            'percentage' => 88
        ],
        [
            'id' => 3,
            'name_en' => 'Performance Optimization',
            'name_es' => 'Optimización de Rendimiento',
            'percentage' => 94
        ]
    ];
    file_put_contents($skills_file, json_encode($default_skills, JSON_PRETTY_PRINT));
}

// Get action from request
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

// Check authentication for write operations
if (in_array($action, ['add', 'update', 'delete'])) {
    $password = $_POST['password'] ?? '';
    if ($password !== ADMIN_PASSWORD) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
}

// Load current skills
function loadSkills() {
    global $skills_file;
    $data = file_get_contents($skills_file);
    return json_decode($data, true) ?: [];
}

// Save skills
function saveSkills($skills) {
    global $skills_file;
    return file_put_contents($skills_file, json_encode($skills, JSON_PRETTY_PRINT));
}

// Handle different actions
switch ($action) {
    case 'list':
        $skills = loadSkills();
        echo json_encode(['success' => true, 'skills' => $skills]);
        break;

    case 'add':
        $name_en = trim($_POST['name_en'] ?? '');
        $name_es = trim($_POST['name_es'] ?? '');
        $percentage = intval($_POST['percentage'] ?? 0);

        if (empty($name_en) || empty($name_es)) {
            echo json_encode(['success' => false, 'message' => 'Skill names are required']);
            exit;
        }

        if ($percentage < 0 || $percentage > 100) {
            echo json_encode(['success' => false, 'message' => 'Percentage must be between 0 and 100']);
            exit;
        }

        $skills = loadSkills();
        $new_id = empty($skills) ? 1 : max(array_column($skills, 'id')) + 1;
        
        $new_skill = [
            'id' => $new_id,
            'name_en' => $name_en,
            'name_es' => $name_es,
            'percentage' => $percentage
        ];

        $skills[] = $new_skill;
        saveSkills($skills);

        echo json_encode(['success' => true, 'message' => 'Skill added successfully', 'skill' => $new_skill]);
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name_en = trim($_POST['name_en'] ?? '');
        $name_es = trim($_POST['name_es'] ?? '');
        $percentage = intval($_POST['percentage'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid skill ID']);
            exit;
        }

        if (empty($name_en) || empty($name_es)) {
            echo json_encode(['success' => false, 'message' => 'Skill names are required']);
            exit;
        }

        if ($percentage < 0 || $percentage > 100) {
            echo json_encode(['success' => false, 'message' => 'Percentage must be between 0 and 100']);
            exit;
        }

        $skills = loadSkills();
        $found = false;

        foreach ($skills as &$skill) {
            if ($skill['id'] == $id) {
                $skill['name_en'] = $name_en;
                $skill['name_es'] = $name_es;
                $skill['percentage'] = $percentage;
                $found = true;
                break;
            }
        }

        if (!$found) {
            echo json_encode(['success' => false, 'message' => 'Skill not found']);
            exit;
        }

        saveSkills($skills);
        echo json_encode(['success' => true, 'message' => 'Skill updated successfully']);
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid skill ID']);
            exit;
        }

        $skills = loadSkills();
        $skills = array_filter($skills, function($skill) use ($id) {
            return $skill['id'] != $id;
        });

        // Reindex array
        $skills = array_values($skills);
        
        saveSkills($skills);
        echo json_encode(['success' => true, 'message' => 'Skill deleted successfully']);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
