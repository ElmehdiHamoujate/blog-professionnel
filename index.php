<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db/database.php';

$slug = trim($_GET['post'] ?? '');
$page = max(1, (int) ($_GET['page'] ?? 1));
$cat  = trim($_GET['cat'] ?? '');

// Single post view
if ($slug) {
    $post = getPost($slug);
    if (!$post) { header('Location: /'); exit; }
}

$profile = PROFILE;
$perPage  = 6;
$offset   = ($page - 1) * $perPage;
$posts    = getPosts($perPage, $offset, $cat);
$total    = (int) getDB()->query("SELECT COUNT(*) FROM posts WHERE published=1" . ($cat ? " AND category='$cat'" : ""))->fetchColumn();
$pages    = (int) ceil($total / $perPage);

$categories = ['cybersecurity' => '🔐', 'development' => '💻', 'career' => '🚀', 'tools' => '🛠️', 'general' => '📝'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $slug ? htmlspecialchars($post['title']) . ' — ' : '' ?><?= htmlspecialchars($profile['name']) ?> · Portfolio</title>
    <meta name="description" content="<?= $slug ? htmlspecialchars(strip_tags($post['excerpt'])) : htmlspecialchars($profile['tagline']) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- ── NAV ── -->
<nav class="navbar" id="navbar">
    <div class="nav-inner">
        <a href="/" class="nav-logo"><?= htmlspecialchars($profile['name']) ?></a>
        <ul class="nav-links">
            <li><a href="/#about">About</a></li>
            <li><a href="/#skills">Skills</a></li>
            <li><a href="/#experience">Experience</a></li>
            <li><a href="/#projects">Projects</a></li>
            <li><a href="/#blog">Blog</a></li>
            <li><a href="/#contact">Contact</a></li>
            <li><a href="admin.php" class="btn-nav-admin">Admin</a></li>
        </ul>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<?php if ($slug): ?>
<!-- ═══════════════════════════════════════ SINGLE POST ═══════════════════════════════════════ -->
<main class="post-page">
    <div class="container">
        <a href="/#blog" class="back-link">← Back to Blog</a>
        <article class="post-article">
            <div class="post-cover-emoji"><?= htmlspecialchars($post['cover_emoji']) ?></div>
            <div class="post-meta-top">
                <span class="post-cat cat-<?= htmlspecialchars($post['category']) ?>"><?= htmlspecialchars($post['category']) ?></span>
                <span class="post-date"><?= date('F j, Y', strtotime($post['generated_at'])) ?></span>
                <span class="post-views">👁 <?= number_format($post['views']) ?> views</span>
            </div>
            <h1 class="post-title-large"><?= htmlspecialchars($post['title']) ?></h1>
            <p class="post-excerpt-large"><?= htmlspecialchars($post['excerpt']) ?></p>
            <div class="post-author-bar">
                <img src="<?= htmlspecialchars($profile['photo']) ?>" alt="<?= htmlspecialchars($profile['name']) ?>" class="author-avatar" onerror="this.src='assets/images/avatar-placeholder.svg'">
                <div>
                    <strong><?= htmlspecialchars($profile['name']) ?></strong>
                    <span><?= htmlspecialchars($profile['title']) ?></span>
                </div>
            </div>
            <div class="post-body">
                <?= $post['content'] /* Claude generates safe HTML */ ?>
            </div>
            <?php if ($post['tags']): ?>
            <div class="post-tags">
                <?php foreach (explode(',', $post['tags']) as $tag): ?>
                <span class="tag">#<?= htmlspecialchars(trim($tag)) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </article>
    </div>
</main>

<?php else: ?>
<!-- ═══════════════════════════════════════ HOME PAGE ═══════════════════════════════════════ -->

<!-- HERO -->
<header class="hero" id="hero">
    <div class="hero-bg"></div>
    <div class="container hero-inner">
        <div class="hero-photo-wrap">
            <img
                src="<?= htmlspecialchars($profile['photo']) ?>"
                alt="<?= htmlspecialchars($profile['name']) ?>"
                class="hero-photo"
                onerror="this.onerror=null;this.src='assets/images/avatar-placeholder.svg'"
            >
            <div class="hero-photo-ring"></div>
        </div>
        <div class="hero-content">
            <p class="hero-hello">Hello, I'm</p>
            <h1 class="hero-name"><?= htmlspecialchars($profile['name']) ?></h1>
            <h2 class="hero-title"><?= htmlspecialchars($profile['title']) ?></h2>
            <p class="hero-tagline"><?= htmlspecialchars($profile['tagline']) ?></p>
            <div class="hero-ctas">
                <a href="#blog" class="btn-primary">Read My Blog</a>
                <a href="#contact" class="btn-ghost">Contact Me</a>
            </div>
            <div class="hero-stats">
                <div class="stat"><strong><?= $total ?></strong><span>Posts</span></div>
                <div class="stat"><strong><?= count($profile['skills']) ?></strong><span>Skill Sets</span></div>
                <div class="stat"><strong><?= count($profile['experience']) ?></strong><span>Experiences</span></div>
            </div>
        </div>
    </div>
    <div class="hero-scroll-hint">
        <span>Scroll</span>
        <div class="scroll-line"></div>
    </div>
</header>

<!-- ABOUT -->
<section class="section about-section" id="about">
    <div class="container">
        <div class="section-label">About Me</div>
        <h2 class="section-title">Who I Am</h2>
        <div class="about-grid">
            <div class="about-text">
                <p><?= nl2br(htmlspecialchars($profile['about'])) ?></p>
                <?php if (!empty($profile['certifications'])): ?>
                <div class="certif-list">
                    <h4>Certifications & Ongoing Learning</h4>
                    <ul>
                        <?php foreach ($profile['certifications'] as $c): ?>
                        <li><?= htmlspecialchars($c) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
            <div class="about-card-wrap">
                <div class="about-card">
                    <div class="about-card-item">
                        <span class="aci-icon">📍</span>
                        <span><?= htmlspecialchars($profile['location']) ?></span>
                    </div>
                    <div class="about-card-item">
                        <span class="aci-icon">🎓</span>
                        <span>CS Graduate 2026</span>
                    </div>
                    <div class="about-card-item">
                        <span class="aci-icon">🔐</span>
                        <span>Cybersecurity Focus</span>
                    </div>
                    <div class="about-card-item">
                        <span class="aci-icon">🌍</span>
                        <span>Open to Remote</span>
                    </div>
                    <a href="mailto:mehdihamoujate@gmail.com?subject=Let%27s%20Connect&body=Hello%20Elmehdi%2C%0A%0AI%20found%20your%20profile%20and%20would%20like%20to%20connect." class="btn-primary btn-full">Get In Touch</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SKILLS -->
<section class="section skills-section" id="skills">
    <div class="container">
        <div class="section-label">Technical Skills</div>
        <h2 class="section-title">What I Work With</h2>
        <div class="skills-grid">
            <?php foreach ($profile['skills'] as $category => $items): ?>
            <div class="skill-group">
                <h3 class="skill-group-title"><?= htmlspecialchars($category) ?></h3>
                <div class="skill-tags">
                    <?php foreach ($items as $skill): ?>
                    <span class="skill-tag"><?= htmlspecialchars($skill) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- EXPERIENCE / EDUCATION -->
<section class="section experience-section" id="experience">
    <div class="container">
        <div class="section-label">Background</div>
        <h2 class="section-title">Experience & Education</h2>
        <div class="timeline">
            <?php foreach ($profile['experience'] as $i => $exp): ?>
            <div class="timeline-item <?= $i % 2 === 0 ? 'left' : 'right' ?>">
                <div class="timeline-dot"></div>
                <div class="timeline-card">
                    <span class="timeline-period"><?= htmlspecialchars($exp['period']) ?></span>
                    <h3><?= htmlspecialchars($exp['role']) ?></h3>
                    <p class="timeline-company"><?= htmlspecialchars($exp['company']) ?></p>
                    <p><?= htmlspecialchars($exp['description']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- PROJECTS -->
<section class="section projects-section" id="projects">
    <div class="container">
        <div class="section-label">Open Source</div>
        <h2 class="section-title">My Projects</h2>
        <p class="section-sub">A selection of academic and personal projects available on GitHub.</p>
        <div class="projects-grid">
            <a href="https://github.com/ElmehdiHamoujate/CyberSecurity-Blog" target="_blank" rel="noopener" class="project-card">
                <div class="project-card-top">
                    <span class="project-emoji">🔐</span>
                    <span class="project-lang php">PHP</span>
                </div>
                <h3>CyberSecurity-Blog</h3>
                <p>This very blog — AI-powered cybersecurity portfolio built with PHP, SQLite and Claude.</p>
                <span class="project-link">View on GitHub →</span>
            </a>
            <a href="https://github.com/ElmehdiHamoujate/hr-connect-elite" target="_blank" rel="noopener" class="project-card">
                <div class="project-card-top">
                    <span class="project-emoji">👥</span>
                    <span class="project-lang js">JavaScript</span>
                </div>
                <h3>HR Connect Elite</h3>
                <p>Human resources management web application built with JavaScript.</p>
                <span class="project-link">View on GitHub →</span>
            </a>
            <a href="https://github.com/ElmehdiHamoujate/IOS-Projet---Swift-" target="_blank" rel="noopener" class="project-card">
                <div class="project-card-top">
                    <span class="project-emoji">📱</span>
                    <span class="project-lang swift">Swift</span>
                </div>
                <h3>iOS Project</h3>
                <p>Mobile application developed in Swift as part of the DEC curriculum at Collège La Cité.</p>
                <span class="project-link">View on GitHub →</span>
            </a>
            <a href="https://github.com/ElmehdiHamoujate/Projet-Final" target="_blank" rel="noopener" class="project-card">
                <div class="project-card-top">
                    <span class="project-emoji">🌐</span>
                    <span class="project-lang html">HTML</span>
                </div>
                <h3>Projet Final</h3>
                <p>Final web development project demonstrating front-end skills acquired during the DEC program.</p>
                <span class="project-link">View on GitHub →</span>
            </a>
            <a href="https://github.com/ElmehdiHamoujate/hello-github" target="_blank" rel="noopener" class="project-card">
                <div class="project-card-top">
                    <span class="project-emoji">⚙️</span>
                    <span class="project-lang csharp">C#</span>
                </div>
                <h3>Hello GitHub</h3>
                <p>Introductory C# project — first steps in .NET development and version control with Git.</p>
                <span class="project-link">View on GitHub →</span>
            </a>
            <a href="https://github.com/ElmehdiHamoujate" target="_blank" rel="noopener" class="project-card project-card-more">
                <div class="project-card-top">
                    <span class="project-emoji">🐙</span>
                </div>
                <h3>More on GitHub</h3>
                <p>Explore all my repositories and contributions on my GitHub profile.</p>
                <span class="project-link">Visit Profile →</span>
            </a>
        </div>
    </div>
</section>

<!-- BLOG -->
<section class="section blog-section" id="blog">
    <div class="container">
        <div class="section-label">Blog</div>
        <h2 class="section-title">Latest Articles</h2>

        <!-- Category Filter -->
        <div class="cat-filter">
            <a href="/" class="cat-btn <?= !$cat ? 'active' : '' ?>">All</a>
            <?php foreach ($categories as $key => $emoji): ?>
            <a href="/?cat=<?= $key ?>" class="cat-btn <?= $cat === $key ? 'active' : '' ?>"><?= $emoji ?> <?= ucfirst($key) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($posts)): ?>
        <div class="posts-grid" id="postsGrid">
            <?php foreach ($posts as $p): ?>
            <article class="post-card" onclick="window.location='/?post=<?= urlencode($p['slug']) ?>'">
                <div class="post-card-emoji"><?= htmlspecialchars($p['cover_emoji']) ?></div>
                <div class="post-card-body">
                    <div class="post-card-meta">
                        <span class="post-cat cat-<?= htmlspecialchars($p['category']) ?>"><?= htmlspecialchars($p['category']) ?></span>
                        <span class="post-card-date"><?= date('M j, Y', strtotime($p['generated_at'])) ?></span>
                    </div>
                    <h3 class="post-card-title"><?= htmlspecialchars($p['title']) ?></h3>
                    <p class="post-card-excerpt"><?= htmlspecialchars($p['excerpt']) ?></p>
                    <div class="post-card-footer">
                        <span class="post-card-views">👁 <?= number_format($p['views']) ?></span>
                        <span class="read-more">Read article →</span>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="/?page=<?= $page - 1 ?><?= $cat ? "&cat=$cat" : '' ?>" class="pag-btn">← Previous</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="/?page=<?= $i ?><?= $cat ? "&cat=$cat" : '' ?>" class="pag-btn <?= $i === $page ? 'pag-active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if ($page < $pages): ?>
            <a href="/?page=<?= $page + 1 ?><?= $cat ? "&cat=$cat" : '' ?>" class="pag-btn">Next →</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- CONTACT -->
<section class="section contact-section" id="contact">
    <div class="container">
        <div class="section-label">Get In Touch</div>
        <h2 class="section-title">Let's Connect</h2>
        <div class="contact-grid">
            <div class="contact-info">
                <p>I'm actively looking for opportunities in <strong>cybersecurity</strong>. Whether it's an internship, a junior role, or a collaboration — I'd love to hear from you.</p>
                <div class="contact-links">
                    <a href="mailto:<?= htmlspecialchars($profile['email']) ?>" class="contact-link">
                        <span class="contact-icon">✉️</span>
                        <span><?= htmlspecialchars($profile['email']) ?></span>
                    </a>
                    <a href="<?= htmlspecialchars($profile['github']) ?>" target="_blank" rel="noopener" class="contact-link">
                        <span class="contact-icon">🐙</span>
                        <span>GitHub — ElmehdiHamoujate</span>
                    </a>
                    <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank" rel="noopener" class="contact-link">
                        <span class="contact-icon">💼</span>
                        <span>LinkedIn</span>
                    </a>
                </div>
            </div>
            <div class="contact-cta-box">
                <div class="cta-box-inner">
                    <div class="cta-emoji">🔐</div>
                    <h3>Available for Opportunities</h3>
                    <p>Cybersecurity · Development · Open Source</p>
                    <a href="mailto:mehdihamoujate@gmail.com?subject=Let%27s%20Connect&body=Hello%20Elmehdi%2C%0A%0AI%20found%20your%20profile%20and%20would%20like%20to%20connect." class="btn-primary">Send me an email</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php endif; ?>

<!-- FOOTER -->
<footer class="footer">
    <div class="container footer-inner">
        <p>© <?= date('Y') ?> <?= htmlspecialchars($profile['name']) ?>. All rights reserved.</p>
        <div class="footer-links">
            <a href="<?= htmlspecialchars($profile['github']) ?>" target="_blank" rel="noopener">GitHub</a>
            <a href="<?= htmlspecialchars($profile['linkedin']) ?>" target="_blank" rel="noopener">LinkedIn</a>
        </div>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
