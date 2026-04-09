<?php
require_once __DIR__ . '/../config.php';

function getDB(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL;');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id           INTEGER PRIMARY KEY AUTOINCREMENT,
            title        TEXT    NOT NULL,
            slug         TEXT    UNIQUE NOT NULL,
            excerpt      TEXT,
            content      TEXT    NOT NULL,
            category     TEXT    DEFAULT 'general',
            tags         TEXT,
            cover_emoji  TEXT    DEFAULT '🔐',
            generated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            published    INTEGER  DEFAULT 1,
            views        INTEGER  DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS settings (
            key   TEXT PRIMARY KEY,
            value TEXT
        );

        INSERT OR IGNORE INTO settings (key, value) VALUES
            ('auto_generate', '0'),
            ('last_generated', ''),
            ('generate_interval', 'weekly');
    ");

    return $pdo;
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-') . '-' . substr(md5($text . microtime()), 0, 6);
}

function getPosts(int $limit = 10, int $offset = 0, string $category = ''): array {
    $db = getDB();
    $sql = 'SELECT * FROM posts WHERE published = 1';
    $params = [];
    if ($category) {
        $sql .= ' AND category = :cat';
        $params[':cat'] = $category;
    }
    $sql .= ' ORDER BY generated_at DESC LIMIT :lim OFFSET :off';
    $stmt = $db->prepare($sql);
    foreach ($params as $k => $v) $stmt->bindValue($k, $v);
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getPost(string $slug): array|false {
    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM posts WHERE slug = :slug AND published = 1');
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();
    if ($post) {
        $db->prepare('UPDATE posts SET views = views + 1 WHERE slug = :slug')
           ->execute([':slug' => $slug]);
    }
    return $post;
}

function getPostCount(string $category = ''): int {
    $db  = getDB();
    $sql = 'SELECT COUNT(*) FROM posts WHERE published = 1';
    $params = [];
    if ($category) {
        $sql .= ' AND category = :cat';
        $params[':cat'] = $category;
    }
    return (int) $db->prepare($sql)->execute($params) ? $db->query($sql)->fetchColumn() : 0;
}

function savePost(array $data): int {
    $db   = getDB();
    $slug = slugify($data['title']);
    $stmt = $db->prepare("
        INSERT INTO posts (title, slug, excerpt, content, category, tags, cover_emoji, published)
        VALUES (:title, :slug, :excerpt, :content, :category, :tags, :emoji, :published)
    ");
    $stmt->execute([
        ':title'     => $data['title'],
        ':slug'      => $slug,
        ':excerpt'   => $data['excerpt']   ?? '',
        ':content'   => $data['content'],
        ':category'  => $data['category']  ?? 'general',
        ':tags'      => $data['tags']      ?? '',
        ':emoji'     => $data['cover_emoji'] ?? '🔐',
        ':published' => $data['published']  ?? 1,
    ]);
    return (int) $db->lastInsertId();
}

function deletePost(int $id): void {
    getDB()->prepare('DELETE FROM posts WHERE id = :id')->execute([':id' => $id]);
}

function getSetting(string $key, string $default = ''): string {
    $stmt = getDB()->prepare('SELECT value FROM settings WHERE key = :k');
    $stmt->execute([':k' => $key]);
    $row = $stmt->fetch();
    return $row ? $row['value'] : $default;
}

function setSetting(string $key, string $value): void {
    getDB()->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (:k, :v)')
           ->execute([':k' => $key, ':v' => $value]);
}

function getAllPostsAdmin(): array {
    return getDB()->query('SELECT id, title, category, tags, generated_at, published, views FROM posts ORDER BY generated_at DESC')->fetchAll();
}
