# Elmehdi Hamoujate — Professional Blog

> A LinkedIn-style professional portfolio & AI-powered blog built with **PHP 8.2**, **SQLite**, and **Claude AI**. Deployed on **Railway**.

---

## Features

- **Professional portfolio** — Hero, About, Skills, Experience, Contact
- **AI Blog** — Claude writes articles in your voice on cybersecurity, dev, and career
- **Admin panel** — Generate posts, manage drafts, view stats
- **SQLite** — Zero-config database, runs anywhere
- **Railway-ready** — One-click deploy with Dockerfile

---

## Local Setup (Windows + PHP)

### Requirements
- PHP 8.2+ with `pdo_sqlite` and `curl` extensions
- A [Claude API key](https://console.anthropic.com/)

### Steps

```bash
# 1. Copy and fill your .env
cp .env.example .env
# Edit .env and add your CLAUDE_API_KEY and a strong ADMIN_PASSWORD

# 2. Start the local server
php -S localhost:8080

# 3. Open in browser
# Portfolio: http://localhost:8080
# Admin:     http://localhost:8080/admin.php  (password from .env)
```

---

## Deploy to Railway

### 1. Push to GitHub
```bash
git init
git add .
git commit -m "Initial commit: professional blog"
git remote add origin https://github.com/ElmehdiHamoujate/blog.git
git push -u origin main
```

### 2. Create Railway project
1. Go to [railway.app](https://railway.app) → **New Project** → **Deploy from GitHub repo**
2. Select your repository
3. Railway detects the `Dockerfile` automatically

### 3. Set environment variables in Railway
In your project → **Variables** tab, add:

| Variable | Value |
|---|---|
| `CLAUDE_API_KEY` | `sk-ant-api03-...` |
| `ADMIN_PASSWORD` | A strong password |
| `SITE_URL` | Your Railway domain |
| `CLAUDE_MODEL` | `claude-opus-4-5` |

### 4. Generate a domain
Railway → **Settings** → **Domains** → **Generate Domain**

Your site is live! 🎉

---

## Project Structure

```
Blog Professionnel/
├── index.php          # Main portfolio + blog page
├── admin.php          # Admin dashboard
├── config.php         # Profile data + env config
├── .env               # Local secrets (not committed)
├── .env.example       # Template for secrets
├── .htaccess          # URL rewriting + security
├── Dockerfile         # Railway / Docker deployment
├── railway.json       # Railway config
├── api/
│   ├── generate.php   # Claude AI post generation
│   └── posts.php      # Posts CRUD API
├── db/
│   ├── database.php   # SQLite layer
│   └── blog.sqlite    # Auto-created on first run
└── assets/
    ├── css/style.css  # Main styles
    ├── css/admin.css  # Admin styles
    ├── js/main.js     # Animations + interactions
    └── images/
        └── profile.jpg  ← ADD YOUR PHOTO HERE
```

---

## Adding Your Profile Photo

1. Copy your photo to `assets/images/profile.jpg`
2. Redeploy or refresh — it appears automatically

---

## Admin Panel

URL: `/admin.php` — Login with your `ADMIN_PASSWORD`

- **Generate Post** — Pick a category + optional topic → Claude writes a full article
- **Quick Topics** — Pre-built prompts for one-click generation
- **All Posts** — Toggle publish/draft, delete posts
- **Settings** — API status, profile overview

---

## Customizing Your Profile

Edit the `PROFILE` constant in `config.php`:
- Name, title, tagline, location, email
- GitHub / LinkedIn URLs
- About paragraph
- Skills and experience timeline
- Certifications

---

## Security Notes

- Never commit `.env` to git (it's in `.gitignore`)
- Change `ADMIN_PASSWORD` before deploying
- The SQLite file is protected by `.htaccess`
