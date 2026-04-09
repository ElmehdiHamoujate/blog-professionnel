<?php
// Load .env manually (no Composer dependency)
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

define('CLAUDE_API_KEY', getenv('CLAUDE_API_KEY') ?: '');
define('CLAUDE_MODEL',   getenv('CLAUDE_MODEL')   ?: 'claude-opus-4-5');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'admin1234');
define('SITE_URL',       getenv('SITE_URL')       ?: 'http://localhost:8080');
define('DB_PATH',        __DIR__ . '/db/blog.sqlite');

// Profile data — edit to match your real info
define('PROFILE', [
    'name'        => 'Elmehdi Hamoujate',
    'title'       => 'Cybersecurity Enthusiast · IT Graduate & Strategic Manager',
    'tagline'     => 'Where technical expertise meets strategic thinking — building secure, impactful solutions.',
    'location'    => 'Ottawa, Canada',
    'email'       => 'mehdihamoujate@gmail.com',
    'github'      => 'https://github.com/ElmehdiHamoujate',
    'linkedin'    => 'https://www.linkedin.com/in/elmehdi-hamoujate-212568140/',
    'about'       => "I am a hybrid professional combining a DEC in Computer Programming (Collège La Cité, April 2026) with a Master's in Strategic Management (Université Hassan II, Casablanca). My technical foundation covers C#, Kotlin, Android development, SQL databases, web development, and Linux systems — all reinforced by real-world operations experience.\n\nBefore pivoting fully into IT, I built strong professional skills as an Operations Manager (team coordination, process optimization), Administrative Assistant (document management, operational support), and Commercial Advisor (client relations, problem-solving). This unique combination of technical and managerial expertise gives me a distinct edge in cybersecurity: I understand both the systems and the organizations I help protect.\n\nI am actively targeting entry-level and junior cybersecurity roles in Canada, where I can apply my technical skills, analytical mindset, and leadership experience to build safer digital environments.",
    'photo'       => 'assets/images/profile.jpg',
    'skills'      => [
        'Cybersecurity'      => ['Network Security', 'Linux Security', 'Vulnerability Assessment', 'OWASP Top 10', 'Wireshark', 'Security Concepts'],
        'Development'        => ['C#', 'Kotlin', 'JavaScript', 'Android (basics)', 'SQL', 'Web Development', 'VS Code'],
        'Systems & Networks' => ['Linux', 'TCP/IP Fundamentals', 'System Administration (basics)', 'Git'],
        'Management'         => ['Team Leadership', 'Operations Management', 'Strategic Analysis', 'Process Optimization', 'Client Relations'],
    ],
    'experience'  => [
        [
            'role'        => 'DEC — Programmation Informatique',
            'company'     => 'Collège La Cité, Ottawa',
            'period'      => '2024 – April 2026',
            'description' => 'Practical programming diploma covering C#, Kotlin, Android development basics, SQL databases, web development, Linux fundamentals, and networking concepts. Graduated April 2026.',
        ],
        [
            'role'        => "Master's — Management Stratégique",
            'company'     => 'Université Hassan II, Casablanca',
            'period'      => '2015 – 2017',
            'description' => "Graduate degree focused on strategic analysis, organizational management, and business decision-making. Developed strong analytical and leadership frameworks that complement my technical IT career transition.",
        ],
        [
            'role'        => 'Gestionnaire Opérationnel',
            'company'     => 'Expérience professionnelle',
            'period'      => 'Expérience antérieure',
            'description' => 'Led team coordination, operational process optimization, and performance monitoring. Developed strong problem-solving and leadership skills applicable to IT project management and security operations.',
        ],
        [
            'role'        => 'Adjoint Administratif & Conseiller Commercial',
            'company'     => 'Expériences variées',
            'period'      => 'Expérience antérieure',
            'description' => 'Document management, operational support, client relations, sales advisory, and conflict resolution. Built communication and organizational skills that translate directly to cybersecurity consulting and stakeholder management.',
        ],
    ],
    'certifications' => [
        'DEC Programmation Informatique — Collège La Cité (April 2026)',
        'Actively pursuing CompTIA Security+ certification',
        'Exploring Google Cybersecurity Certificate',
    ],
]);
