<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

// Admin auth guard
session_start();
if (empty($_SESSION['admin']) && (($_SERVER['HTTP_X_ADMIN_KEY'] ?? '') !== ADMIN_PASSWORD)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true);
$topic    = trim($input['topic']    ?? '');
$category = trim($input['category'] ?? 'cybersecurity');

if (!CLAUDE_API_KEY) {
    echo json_encode(['error' => 'Claude API key not configured. Add CLAUDE_API_KEY to your .env file.']);
    exit;
}

$profile = PROFILE;

$systemPrompt = <<<SYSTEM
You are {$profile['name']}, a {$profile['title']}.
About you: {$profile['about']}

You write professional, insightful blog posts in a LinkedIn-style tone: personal, direct, expert, and engaging.
Your posts mix technical depth with real-world application. You write in the first person.
Always end with a thought-provoking question or call-to-action to spark discussion.

Rules:
- Title: concise, impactful (max 12 words)
- Excerpt: 1-2 punchy sentences, no spoilers
- Content: well-structured HTML using <p>, <h3>, <ul>, <li>, <strong>, <em>, <blockquote>. No <html>/<body>/<head> tags.
- Tags: 3–5 relevant lowercase hashtags separated by commas (no # sign)
- Emoji: single relevant emoji for the post cover
- Category options: cybersecurity | development | career | tools | general
- Language: English

Respond with ONLY valid JSON in this exact structure:
{
  "title": "...",
  "excerpt": "...",
  "content": "...",
  "tags": "...",
  "cover_emoji": "...",
  "category": "..."
}
SYSTEM;

$userMessage = $topic
    ? "Write a professional blog post about: $topic"
    : generateRandomTopic($category);

function generateRandomTopic(string $category): string {
    $topics = [
        'cybersecurity' => [
            'The OWASP Top 10 vulnerabilities every developer must know',
            'How I started learning penetration testing from scratch',
            'Why network security is the first line of defense',
            'SQL injection: still #1 after all these years — here\'s why',
            'Setting up your first home cybersecurity lab with VirtualBox',
            'Wireshark basics: what every junior security analyst must master',
            'The difference between a hacker and a cybersecurity professional',
        ],
        'development' => [
            'Clean code principles that saved my last project',
            'Building RESTful APIs with PHP and best practices I learned the hard way',
            'Docker changed how I think about development environments',
            'Android development lessons from my first real app',
            'Why every developer should learn SQL deeply',
        ],
        'career' => [
            'What I learned transitioning from CS student to cybersecurity professional',
            'Building a portfolio that stands out in the cybersecurity field',
            'How open-source contributions shaped my technical skills',
            'The skills gap in cybersecurity — and how I am bridging it',
        ],
        'tools' => [
            'My essential toolkit for security research in 2025',
            'Why I use Linux as my primary development environment',
            'Git workflows that keep my projects clean and professional',
        ],
    ];

    $list = $topics[$category] ?? $topics['cybersecurity'];
    return "Write a professional blog post about: " . $list[array_rand($list)];
}

// Call Claude API
$payload = [
    'model'      => CLAUDE_MODEL,
    'max_tokens' => 1800,
    'system'     => $systemPrompt,
    'messages'   => [
        ['role' => 'user', 'content' => $userMessage],
    ],
];

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . CLAUDE_API_KEY,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 60,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    $err = json_decode($response, true);
    echo json_encode(['error' => 'Claude API error: ' . ($err['error']['message'] ?? $response)]);
    exit;
}

$apiData = json_decode($response, true);
$raw     = $apiData['content'][0]['text'] ?? '';

// Extract JSON from Claude's response (it may wrap it in markdown)
if (preg_match('/\{[\s\S]*\}/m', $raw, $matches)) {
    $postData = json_decode($matches[0], true);
} else {
    echo json_encode(['error' => 'Could not parse Claude response', 'raw' => $raw]);
    exit;
}

if (!isset($postData['title'], $postData['content'])) {
    echo json_encode(['error' => 'Invalid post structure from Claude', 'raw' => $raw]);
    exit;
}

$id = savePost($postData);
setSetting('last_generated', date('Y-m-d H:i:s'));

echo json_encode([
    'success' => true,
    'post_id' => $id,
    'title'   => $postData['title'],
    'category'=> $postData['category'] ?? $category,
]);
