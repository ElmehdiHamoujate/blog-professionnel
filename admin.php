<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db/database.php';

session_start();

$error   = '';
$success = '';

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    }
    $error = 'Wrong password.';
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

$isAdmin = !empty($_SESSION['admin']);
$posts   = $isAdmin ? getAllPostsAdmin() : [];
$profile = PROFILE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — <?= htmlspecialchars($profile['name']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-body">

<?php if (!$isAdmin): ?>
<!-- ── LOGIN ── -->
<div class="login-page">
    <div class="login-card">
        <div class="login-logo">🔐</div>
        <h1>Admin Panel</h1>
        <p><?= htmlspecialchars($profile['name']) ?></p>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST" class="login-form">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter admin password" autofocus required>
            <button type="submit" class="btn-primary btn-full">Login →</button>
        </form>
        <a href="/" class="back-link-small">← Back to site</a>
    </div>
</div>

<?php else: ?>
<!-- ── ADMIN DASHBOARD ── -->
<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="<?= htmlspecialchars($profile['photo']) ?>" alt="" class="sidebar-avatar" onerror="this.src='assets/images/avatar-placeholder.svg'">
            <div>
                <strong><?= htmlspecialchars($profile['name']) ?></strong>
                <span>Admin</span>
            </div>
        </div>
        <nav class="sidebar-nav">
            <a href="#generate" class="sidebar-link active" data-section="generate">✨ Generate Post</a>
            <a href="#posts"    class="sidebar-link"         data-section="posts">📄 All Posts</a>
            <a href="#settings" class="sidebar-link"         data-section="settings">⚙️ Settings</a>
        </nav>
        <a href="/" class="sidebar-link sidebar-site-link">← View Site</a>
        <a href="?logout" class="sidebar-logout">Logout</a>
    </aside>

    <!-- Main content -->
    <main class="admin-main">

        <!-- Stats bar -->
        <div class="admin-stats">
            <div class="admin-stat">
                <strong><?= count($posts) ?></strong>
                <span>Total Posts</span>
            </div>
            <div class="admin-stat">
                <strong><?= count(array_filter($posts, fn($p) => $p['published'])) ?></strong>
                <span>Published</span>
            </div>
            <div class="admin-stat">
                <strong><?= array_sum(array_column($posts, 'views')) ?></strong>
                <span>Total Views</span>
            </div>
            <div class="admin-stat">
                <strong><?= CLAUDE_API_KEY ? '✅' : '❌' ?></strong>
                <span>Claude API</span>
            </div>
        </div>

        <!-- ─── GENERATE SECTION ─── -->
        <section class="admin-section active" id="section-generate">
            <div class="admin-card">
                <h2>✨ Generate New Post with Claude AI</h2>
                <p class="admin-card-sub">Leave the topic blank to let Claude choose a relevant topic automatically.</p>

                <div class="generate-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select id="genCategory">
                                <option value="cybersecurity">🔐 Cybersecurity</option>
                                <option value="development">💻 Development</option>
                                <option value="career">🚀 Career</option>
                                <option value="tools">🛠️ Tools</option>
                                <option value="general">📝 General</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Custom Topic <span class="optional">(optional)</span></label>
                            <input type="text" id="genTopic" placeholder="e.g. SQL injection prevention best practices">
                        </div>
                    </div>
                    <button class="btn-primary btn-generate" id="generateBtn" onclick="generatePost()">
                        <span class="btn-text">✨ Generate with Claude</span>
                        <span class="btn-loading" hidden>⏳ Writing with Claude…</span>
                    </button>
                </div>

                <div id="generateResult" class="generate-result" hidden>
                    <div class="result-success">
                        <span class="result-icon">✅</span>
                        <div>
                            <strong id="resultTitle"></strong>
                            <span id="resultMeta"></span>
                        </div>
                        <a id="resultLink" href="#" target="_blank" class="btn-ghost btn-sm">View →</a>
                    </div>
                </div>
                <div id="generateError" class="generate-error" hidden></div>
            </div>

            <!-- Quick generate buttons -->
            <div class="admin-card">
                <h3>Quick Topics</h3>
                <p class="admin-card-sub">Click any topic to pre-fill and generate instantly.</p>
                <div class="quick-topics">
                    <button class="quick-btn" onclick="quickGenerate('OWASP Top 10 for developers', 'cybersecurity')">🔐 OWASP Top 10</button>
                    <button class="quick-btn" onclick="quickGenerate('Building a home security lab', 'cybersecurity')">🧪 Security Lab</button>
                    <button class="quick-btn" onclick="quickGenerate('My journey into cybersecurity', 'career')">🚀 My Journey</button>
                    <button class="quick-btn" onclick="quickGenerate('Docker for beginners', 'tools')">🐳 Docker Basics</button>
                    <button class="quick-btn" onclick="quickGenerate('PHP secure coding practices', 'development')">💻 Secure PHP</button>
                    <button class="quick-btn" onclick="quickGenerate('Wireshark packet analysis tutorial', 'cybersecurity')">📡 Wireshark</button>
                    <button class="quick-btn" onclick="quickGenerate('How to get your first cybersecurity job', 'career')">💼 First Job</button>
                    <button class="quick-btn" onclick="quickGenerate('Python scripting for security automation', 'development')">🐍 Python Security</button>
                </div>
            </div>
        </section>

        <!-- ─── POSTS SECTION ─── -->
        <section class="admin-section" id="section-posts">
            <div class="admin-card">
                <h2>📄 All Posts</h2>
                <?php if (empty($posts)): ?>
                <p class="empty-state">No posts yet. Generate your first one above!</p>
                <?php else: ?>
                <div class="posts-table-wrap">
                    <table class="posts-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Views</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="postsTableBody">
                            <?php foreach ($posts as $p): ?>
                            <tr id="row-<?= $p['id'] ?>">
                                <td class="post-title-cell"><?= htmlspecialchars($p['title']) ?></td>
                                <td><span class="post-cat cat-<?= htmlspecialchars($p['category']) ?>"><?= htmlspecialchars($p['category']) ?></span></td>
                                <td><?= date('M j, Y', strtotime($p['generated_at'])) ?></td>
                                <td><?= number_format($p['views']) ?></td>
                                <td>
                                    <span class="status-badge <?= $p['published'] ? 'published' : 'draft' ?>">
                                        <?= $p['published'] ? 'Published' : 'Draft' ?>
                                    </span>
                                </td>
                                <td class="actions-cell">
                                    <button class="action-btn toggle-btn" onclick="togglePost(<?= $p['id'] ?>)" title="Toggle publish">
                                        <?= $p['published'] ? '🙈' : '👁' ?>
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deletePost(<?= $p['id'] ?>)" title="Delete">🗑️</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ─── SETTINGS SECTION ─── -->
        <section class="admin-section" id="section-settings">
            <div class="admin-card">
                <h2>⚙️ Settings</h2>
                <div class="settings-grid">
                    <div class="setting-item">
                        <label>Claude API Status</label>
                        <p class="setting-val"><?= CLAUDE_API_KEY ? '✅ Configured' : '❌ Missing — add CLAUDE_API_KEY to .env' ?></p>
                    </div>
                    <div class="setting-item">
                        <label>Claude Model</label>
                        <p class="setting-val"><?= htmlspecialchars(CLAUDE_MODEL) ?></p>
                    </div>
                    <div class="setting-item">
                        <label>Site URL</label>
                        <p class="setting-val"><?= htmlspecialchars(SITE_URL) ?></p>
                    </div>
                    <div class="setting-item">
                        <label>Database</label>
                        <p class="setting-val"><?= file_exists(DB_PATH) ? '✅ SQLite connected (' . round(filesize(DB_PATH)/1024, 1) . ' KB)' : '⚠️ Not yet created' ?></p>
                    </div>
                </div>
                <hr class="setting-divider">
                <h3>Profile Quick Edit</h3>
                <p class="admin-card-sub">To update your profile info, edit <code>config.php</code> and redeploy.</p>
                <div class="settings-grid">
                    <div class="setting-item"><label>Name</label><p class="setting-val"><?= htmlspecialchars($profile['name']) ?></p></div>
                    <div class="setting-item"><label>Title</label><p class="setting-val"><?= htmlspecialchars($profile['title']) ?></p></div>
                    <div class="setting-item"><label>Email</label><p class="setting-val"><?= htmlspecialchars($profile['email']) ?></p></div>
                    <div class="setting-item"><label>Location</label><p class="setting-val"><?= htmlspecialchars($profile['location']) ?></p></div>
                </div>
            </div>
        </section>

    </main>
</div>

<script>
async function generatePost() {
    const btn       = document.getElementById('generateBtn');
    const btnText   = btn.querySelector('.btn-text');
    const btnLoad   = btn.querySelector('.btn-loading');
    const result    = document.getElementById('generateResult');
    const errBox    = document.getElementById('generateError');

    btn.disabled = true;
    btnText.hidden = true;
    btnLoad.hidden = false;
    result.hidden  = true;
    errBox.hidden  = true;

    const topic    = document.getElementById('genTopic').value.trim();
    const category = document.getElementById('genCategory').value;

    try {
        const res  = await fetch('api/generate.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ topic, category }),
        });
        const data = await res.json();

        if (data.error) throw new Error(data.error);

        document.getElementById('resultTitle').textContent = data.title;
        document.getElementById('resultMeta').textContent  = `Category: ${data.category} · ID: ${data.post_id}`;
        document.getElementById('resultLink').href = `/?post=${encodeURIComponent(data.title.toLowerCase().replace(/[^a-z0-9]+/g,'-'))}`;
        result.hidden = false;

        // Refresh page after 2s to update posts table
        setTimeout(() => location.reload(), 2500);
    } catch (err) {
        errBox.textContent = '❌ ' + err.message;
        errBox.hidden = false;
    } finally {
        btn.disabled   = false;
        btnText.hidden = false;
        btnLoad.hidden = true;
    }
}

function quickGenerate(topic, category) {
    document.getElementById('genTopic').value      = topic;
    document.getElementById('genCategory').value   = category;
    showSection('generate');
    generatePost();
}

async function deletePost(id) {
    if (!confirm('Delete this post permanently?')) return;
    const res  = await fetch(`api/posts.php?action=delete&id=${id}`, { method: 'DELETE' });
    const data = await res.json();
    if (data.success) document.getElementById('row-' + id)?.remove();
    else alert('Error: ' + data.error);
}

async function togglePost(id) {
    const res  = await fetch('api/posts.php?action=toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id }),
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert('Error: ' + data.error);
}

function showSection(name) {
    document.querySelectorAll('.admin-section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    document.getElementById('section-' + name)?.classList.add('active');
    document.querySelector(`[data-section="${name}"]`)?.classList.add('active');
}

document.querySelectorAll('.sidebar-link[data-section]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        showSection(link.dataset.section);
    });
});
</script>
<?php endif; ?>

</body>
</html>
