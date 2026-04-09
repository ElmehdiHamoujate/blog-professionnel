<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db/database.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'list') {
    $limit    = (int) ($_GET['limit']    ?? 6);
    $offset   = (int) ($_GET['offset']   ?? 0);
    $category = $_GET['category'] ?? '';
    echo json_encode(['posts' => getPosts($limit, $offset, $category)]);
    exit;
}

if ($method === 'GET' && $action === 'single') {
    $slug = $_GET['slug'] ?? '';
    $post = $slug ? getPost($slug) : false;
    if (!$post) { http_response_code(404); echo json_encode(['error' => 'Not found']); exit; }
    echo json_encode(['post' => $post]);
    exit;
}

// Admin-only routes
session_start();
if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($method === 'DELETE' && $action === 'delete') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }
    deletePost($id);
    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'POST' && $action === 'toggle') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id    = (int) ($input['id'] ?? 0);
    if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }
    $db   = getDB();
    $db->prepare('UPDATE posts SET published = 1 - published WHERE id = :id')->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Unknown action']);
