<?php
/**
 * Skills Loader - Helper function to load skills from JSON file
 */

function loadSkills() {
    $skills_file = __DIR__ . '/data/skills.json';
    
    // Default skills if file doesn't exist
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
    
    // Check if file exists and is readable
    if (file_exists($skills_file) && is_readable($skills_file)) {
        $data = file_get_contents($skills_file);
        $skills = json_decode($data, true);
        
        if (is_array($skills) && !empty($skills)) {
            return $skills;
        }
    }
    
    return $default_skills;
}

function getSkillClass($index) {
    $classes = ['first-bar', 'second-bar', 'third-bar', 'fourth-bar', 'fifth-bar'];
    return isset($classes[$index]) ? $classes[$index] : 'first-bar';
}
