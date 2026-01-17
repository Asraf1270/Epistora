<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
require_once 'config.php';
require_once 'db_engine.php';

$all_posts = DBEngine::readJSON("posts.json") ?? [];
$user_id   = $_SESSION['user_id'] ?? null;
$user_role = $_SESSION['role'] ?? 'guest';

// ────────────────────────────────────────────────
// Shuffle + sort by recency (recent posts have higher chance to appear early)
if (!empty($all_posts)) {
    // Shuffle first
    shuffle($all_posts);
    
    // Then stable sort by date descending (recent first)
    usort($all_posts, function($a, $b) {
        $dateA = strtotime($a['date'] ?? '1970-01-01');
        $dateB = strtotime($b['date'] ?? '1970-01-01');
        return $dateB - $dateA;
    });
}

// Collect unique tags
$all_tags = [];
foreach ($all_posts as $post) {
    if (!empty($post['tags']) && is_array($post['tags'])) {
        foreach ($post['tags'] as $tag) {
            $clean = trim($tag);
            if ($clean && !in_array($clean, $all_tags)) {
                $all_tags[] = $clean;
            }
        }
    }
}
sort($all_tags);

$json_posts = json_encode($all_posts, JSON_UNESCAPED_UNICODE);
$json_tags  = json_encode($all_tags, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Google Search Console verification (keep this) -->
    <meta name="google-site-verification" content="9q2wnm5BVGrFiDYr4pn3hgxs3CWSClztzE9V_i5NzsE" />

    <!-- SEO Basics -->
    <title>Epistora – Free Blog Platform for Knowledge & Ideas</title>
    <meta name="description" content="Epistora is a free, open blog sharing platform for students, creators and thinkers. Share and discover knowledge in technology, education, science, ideas and more — built by Md. Asraful Islam (Asraf).">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Md. Asraful Islam (Asraf)">
    <link rel="canonical" href="https://epistora.free.nf/">

    <!-- Open Graph (Facebook, LinkedIn, Discord, WhatsApp, etc.) -->
    <meta property="og:title" content="Epistora – Free Blog Platform for Knowledge & Ideas">
    <meta property="og:description" content="Discover and publish thoughtful blogs on technology, education, science, lifestyle and more. Free, community-driven platform built by Md. Asraful Islam (Asraf).">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://epistora.free.nf/">
    <meta property="og:site_name" content="Epistora">
    <meta property="og:image" content="https://epistora.free.nf/logo/favicon-32x32.png">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Epistora blog platform homepage">

    <!-- Twitter / X Cards (still used by many tools in 2026) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Epistora – Free Blog Platform for Knowledge & Ideas">
    <meta name="twitter:description" content="Free, open platform to share and discover blogs on technology, education, science and ideas — built by Asraf.">
    <meta name="twitter:image" content="https://epistora.free.nf/logo/favicon-32x32.png">

    <!-- Favicon & App Icons (very important in 2026) -->
    <link rel="icon" href="/logo/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/logo/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/logo/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/logo/favicon-16x16.png">

    <!-- Theme color (browser bar on mobile) -->
    <meta name="theme-color" content="#6366f1">

    <!-- Analytics (Google Tag Manager + GA4) -->
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-W4M4DJXK');</script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PTXBM6QSTV"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-PTXBM6QSTV');
    </script>
    <title>Epistora</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: { primary: '#6366f1', primarydark: '#4f46e5' }
                }
            }
        }
    </script>

    <style>
        .glass { background: rgba(255,255,255,0.75); backdrop-filter: blur(12px); }
        .dark .glass { background: rgba(30,41,59,0.88); }
        .card { transition: all 0.35s ease; }
        .card:hover { transform: translateY(-6px); box-shadow: 0 15px 35px -10px rgba(0,0,0,0.15); }
        .dark .card:hover { box-shadow: 0 15px 35px -10px rgba(0,0,0,0.45); }
        #tags-bar { scrollbar-width: none; }
        #tags-bar::-webkit-scrollbar { display: none; }
        .tag-active { @apply bg-indigo-600 text-white shadow-md scale-105; }
    </style>
</head>

<body class="bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 min-h-screen transition-colors duration-300 font-sans flex flex-col">

<!-- Header -->
<header class="sticky top-0 z-[100] glass border-b border-slate-200 dark:border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <div class="flex items-center gap-6 lg:gap-10">
        <a href="index.php" class="font-black text-2xl sm:text-2.5xl lg:text-3xl tracking-tight text-primary">EPISTORA</a>
        <div class="hidden lg:block relative w-72 xl:w-80">
          <input type="text" id="searchInput" placeholder="Search..." 
                 class="w-full bg-slate-100 dark:bg-slate-800/70 border border-transparent focus:border-primary/50 rounded-2xl py-2.5 px-5 pl-11 text-sm focus:ring-2 focus:ring-primary/30 outline-none transition-all">
          <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">🔍</span>
        </div>
      </div>

      <div class="hidden md:flex items-center gap-5 lg:gap-6 text-sm font-medium">
        <a href="index.php" class="hover:text-primary transition-colors">Home</a>
        <button id="themeToggle" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">🌙</button>

        <?php if ($user_id): ?>
          <?php if ($user_role === 'admin'): ?>
            <a href="admin/" class="px-4 py-1.5 bg-amber-50 text-amber-800 rounded-xl border border-amber-200 hover:bg-amber-100 transition-all font-semibold">Admin</a>
          <?php endif; ?>

          <?php if (in_array($user_role, ['writer', 'v_writer', 'admin'])): ?>
            <a href="post/create/" class="hover:text-primary font-semibold">Write</a>
            <a href="user/dashboard/" class="hover:text-primary font-semibold">Dashboard</a>
          <?php endif; ?>

          <?php if ($user_role === 'user'): ?>
            <a href="user/apply_writer/index.php" class="text-primary hover:underline font-semibold">Become Writer</a>
          <?php endif; ?>

          <a href="user/logout.php" class="px-5 py-2 bg-rose-50 text-rose-700 rounded-xl border border-rose-100 hover:bg-rose-100 transition-all font-semibold">Logout</a>
        <?php else: ?>
          <a href="user/login/" class="hover:text-primary font-semibold">Login</a>
          <a href="user/register/" class="px-6 py-2.5 bg-primary text-white rounded-xl shadow-md hover:bg-primary/90 transition-all font-semibold">Join Now</a>
        <?php endif; ?>
      </div>

      <div class="flex md:hidden items-center gap-3">
        <button id="searchToggle" class="p-2 text-2xl">🔍</button>
        <button id="menuBtn" class="p-2 text-2xl">☰</button>
      </div>
    </div>

    <div id="mobileSearch" class="hidden md:hidden py-3 px-2 border-t border-slate-200 dark:border-slate-700">
      <input type="text" id="searchInputMobile" placeholder="Search..." 
             class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 rounded-2xl py-3 px-5 pl-12 focus:ring-2 focus:ring-primary outline-none">
      <span class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 text-xl pointer-events-none">🔍</span>
    </div>
  </div>
</header>

<!-- Mobile Menu -->
<div id="mobile-menu" class="fixed inset-0 z-[999] translate-x-full transition-transform duration-300 md:hidden pointer-events-none">
  <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="menuOverlay"></div>
  <div class="absolute right-0 top-0 h-full w-80 bg-white dark:bg-slate-900 shadow-2xl p-6 flex flex-col pointer-events-auto">
    <div class="flex justify-between items-center mb-10">
      <span class="font-black text-2xl text-primary">EPISTORA</span>
      <button id="closeMenu" class="text-3xl text-slate-500">×</button>
    </div>
    <nav class="flex flex-col gap-6 text-base font-medium flex-1">
      <a href="index.php">🏠 Home</a>
      <?php if ($user_id): ?>
        <?php if ($user_role === 'admin'): ?><a href="admin/">🛡️ Admin Panel</a><?php endif; ?>
        <?php if (in_array($user_role, ['writer', 'v_writer', 'admin'])): ?>
          <a href="post/create/">✍️ Write Story</a>
          <a href="user/dashboard/">📊 Dashboard</a>
        <?php endif; ?>
        <?php if ($user_role === 'user'): ?><a href="user/apply_writer/index.php" class="text-primary">🚀 Become a Writer</a><?php endif; ?>
        <a href="user/logout.php" class="text-rose-600 mt-4 pt-4 border-t">🚪 Logout</a>
      <?php else: ?>
        <a href="user/login/">🔑 Login</a>
        <a href="user/register/" class="text-primary">✨ Join Now</a>
      <?php endif; ?>
    </nav>
    <button id="themeToggleMob" class="mt-auto text-left text-sm font-medium text-slate-500 dark:text-slate-400 py-4">
      Toggle dark mode
    </button>
  </div>
</div>

<!-- Hero (only if not logged in) -->
<?php if (!$user_id): ?>
<section class="relative py-16 sm:py-24 bg-gradient-to-br from-indigo-50 via-white to-purple-50 dark:from-slate-900 dark:via-slate-950 dark:to-indigo-950/20">
  <div class="max-w-7xl mx-auto px-5 sm:px-8 text-center">
    <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight mb-6">
      Knowledge Shared, Ideas Grown
    </h1>
    <p class="text-lg sm:text-xl text-slate-700 dark:text-slate-300 max-w-3xl mx-auto mb-10">
      Free platform for thoughtful blogs on technology, education, science, and more.
    </p>
    <a href="user/register/" class="inline-block px-8 py-4 bg-primary text-white font-semibold rounded-full shadow-xl hover:shadow-2xl hover:scale-105 transition-all">
      Start Writing — It's Free
    </a>
  </div>
</section>
<?php endif; ?>

<!-- Tags Bar -->
<section class="sticky top-16 z-40 glass border-b border-slate-200 dark:border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div id="tags-bar" class="flex gap-3 py-4 overflow-x-auto scroll-smooth snap-x snap-mandatory">
      <button data-tag="all" class="tag-btn px-6 py-2 bg-indigo-600 text-white rounded-full text-sm font-medium snap-start flex-shrink-0 shadow tag-active transition-all">
        All
      </button>
      <?php foreach ($all_tags as $tag): ?>
        <button data-tag="<?= htmlspecialchars($tag) ?>" class="tag-btn px-6 py-2 bg-slate-200/80 dark:bg-slate-800/70 rounded-full text-sm font-medium hover:bg-slate-300 dark:hover:bg-slate-700 transition-all snap-start flex-shrink-0">
          <?= htmlspecialchars($tag) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Main Content -->
<main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
  <div id="post-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>

  <div id="sentinel" class="py-16 flex items-center justify-center">
    <div id="loader" class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
    <p id="end-msg" class="hidden mt-6 text-slate-400 dark:text-slate-500 text-sm uppercase tracking-wider">
      End of feed
    </p>
  </div>
</main>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W4M4DJXK"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

    
    
    <footer class="mt-auto border-t border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-950/60 backdrop-blur-lg">
  <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 py-12 lg:py-16">
    
    <!-- Main footer content - 4 columns on desktop -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-12 mb-12">
      
      <!-- Column 1: Brand + short description -->
      <div class="col-span-2 md:col-span-1">
        <a href="index.php" class="font-black text-2xl lg:text-3xl tracking-tight bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent inline-block mb-4">
          EPISTORA
        </a>
        <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-6">
          A free, open platform where students, creators and thinkers share knowledge, ideas, technology, education and more.
        </p>
        
        <!-- Social icons (optional – add your links) -->
        <div class="flex gap-4">
  <!-- Twitter / X -->
  <a href="https://x.com/MdAsrafulI20446" class="text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" aria-label="Twitter/X">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
    </svg>
  </a>

  <!-- GitHub -->
  <a href="https://github.com/Asraf1270" class="text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" aria-label="GitHub">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
    </svg>
  </a>

  <!-- Facebook -->
  <a href="https://www.facebook.com/profile.php?id=100066532016987" class="text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" aria-label="Facebook">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M22.675 0h-21.35C.597 0 0 .597 0 1.326v21.348C0 23.403.597 24 1.326 24h11.495v-9.294H9.691V11.01h3.13V8.309c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24h-1.918c-1.505 0-1.796.715-1.796 1.764v2.314h3.587l-.467 3.696h-3.12V24h6.116C23.403 24 24 23.403 24 22.674V1.326C24 .597 23.403 0 22.675 0z"/>
    </svg>
  </a>

  <!-- LinkedIn -->
  <a href="#" class="text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" aria-label="LinkedIn">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M22.23 0H1.77C.79 0 0 .774 0 1.727v20.545C0 23.227.79 24 1.77 24h20.46C23.2 24 24 23.227 24 22.273V1.727C24 .774 23.2 0 22.23 0zM7.09 20.452H3.56V9h3.53v11.452zM5.325 7.433c-1.13 0-2.048-.924-2.048-2.062 0-1.138.918-2.062 2.048-2.062s2.048.924 2.048 2.062c0 1.138-.918 2.062-2.048 2.062zM20.452 20.452h-3.53v-5.569c0-1.328-.027-3.037-1.85-3.037-1.85 0-2.133 1.445-2.133 2.94v5.666h-3.53V9h3.389v1.561h.047c.472-.9 1.625-1.85 3.345-1.85 3.575 0 4.232 2.352 4.232 5.412v6.329z"/>
    </svg>
  </a>

  <!-- Email -->
  <a href="mailto:mdasrafulislam1270@gmail.com" class="text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors" aria-label="Email">
    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
      <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/>
    </svg>
  </a>
</div>

      <!-- Column 2: Quick Links -->
      <div>
        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">Quick Links</h3>
        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
          <li><a href="index.php" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Home</a></li>
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Discover</a></li>
          <li><a href="post/create/" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Write</a></li>
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Topics</a></li>
        </ul>
      </div>

      <!-- Column 3: Community -->
      <div>
        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">Community</h3>
        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Writers</a></li>
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Guidelines</a></li>
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Support</a></li>
          <li><a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Feedback</a></li>
        </ul>
      </div>

      <!-- Column 4: Legal -->
      <div>
        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 mb-4">Legal</h3>
        <ul class="space-y-3 text-sm text-slate-600 dark:text-slate-400">
          <li><a href="/legal/terms" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Terms of Service</a></li>
          <li><a href="/legal/privacy" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Privacy Policy</a></li>
          <li><a href="/legal/cookie" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Cookie Policy</a></li>
          <li><a href="/legal/content" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Content Policy</a></li>
        </ul>
      </div>
    </div>

    <!-- Bottom bar -->
    <div class="pt-8 border-t border-slate-200 dark:border-slate-800 text-center sm:flex sm:justify-between sm:items-center text-sm text-slate-500 dark:text-slate-400">
      <div>
        © <?php echo date("Y"); ?> Epistora. All rights reserved.
      </div>
      <div class="mt-4 sm:mt-0">
        Built with ❤️ by <span class="text-indigo-600 dark:text-indigo-400 font-medium">Md. Asraful Islam (Asraf)</span>
      </div>
    </div>
  </div>
</footer>
<!-- Minimal Footer -->
<!--
<footer class="border-t border-slate-200 dark:border-slate-800 bg-white/60 dark:bg-slate-950/60 backdrop-blur-md">
  <div class="max-w-7xl mx-auto px-5 sm:px-6 lg:px-8 py-6 text-center text-sm text-slate-600 dark:text-slate-400">
    © <?= date('Y') ?> EPISTORA • 
    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Terms</a> • 
    <a href="#" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Privacy</a> • 
    built by Asraf
  </div>
</footer> -->

<script>
// ────────────────────────────────────────────────
// Data & Variables
// ────────────────────────────────────────────────
const posts = <?= $json_posts ?> || [];

if (posts.length === 0) {
  document.getElementById('post-grid').innerHTML = 
    '<p class="col-span-full text-center py-20 text-slate-500 dark:text-slate-400 text-lg">No posts available yet.</p>';
  document.getElementById('loader').classList.add('hidden');
}

let currentPosts = [...posts];
let currentTag   = 'all';
let index        = 0;
const BATCH_SIZE = 9;

const grid       = document.getElementById('post-grid');
const loader     = document.getElementById('loader');
const endMsg     = document.getElementById('end-msg');
const searchInputs = [
  document.getElementById('searchInput'),
  document.getElementById('searchInputMobile')
];

// ────────────────────────────────────────────────
// Render card
// ────────────────────────────────────────────────
function renderCard(post) {
  const initial = post.author ? post.author.charAt(0).toUpperCase() : '?';
  const thumb   = post.thumbnail ? `<img src="${post.thumbnail}" alt="" class="w-full h-52 object-cover rounded-t-2xl">` : '';

  return `
  <article class="card bg-white dark:bg-slate-800 rounded-2xl overflow-hidden shadow-lg border border-slate-200 dark:border-slate-700">
    ${thumb}
    <div class="p-6 flex flex-col">
      <div class="flex items-center gap-3 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase mb-3">
        <span class="text-indigo-600">New</span>
        <span>•</span>
        <time>${post.date || 'Recent'}</time>
      </div>
      <h2 class="text-xl font-semibold leading-tight mb-3 line-clamp-2">
        <a href="post/view/?id=${post.post_id}" class="hover:text-indigo-600 transition-colors">${post.title}</a>
      </h2>
      <p class="text-slate-600 dark:text-slate-300 text-sm line-clamp-3 mb-5 flex-1">
        ${post.preview || 'Read more...'}
      </p>
      <div class="flex items-center justify-between text-sm">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 text-white flex items-center justify-center font-bold shadow-sm">
            ${initial}
          </div>
          <span class="font-medium">${post.author || 'Anonymous'}</span>
        </div>
        <div class="flex gap-4 text-slate-500 dark:text-slate-400 text-xs">
          <span>👁️ ${post.views ?? 0}</span>
          <span>❤️ ${post.reaction_count ?? 0}</span>
        </div>
      </div>
    </div>
  </article>`;
}

// ────────────────────────────────────────────────
// Load next batch
// ────────────────────────────────────────────────
function loadBatch() {
  if (index >= currentPosts.length) {
    loader.classList.add('hidden');
    if (currentPosts.length > 0) endMsg.classList.remove('hidden');
    return;
  }

  const chunk = currentPosts.slice(index, index + BATCH_SIZE);
  grid.insertAdjacentHTML('beforeend', chunk.map(renderCard).join(''));
  index += BATCH_SIZE;
}

// ────────────────────────────────────────────────
// Filter by tag + random + recent
// ────────────────────────────────────────────────
function filterByTag(tag) {
  currentTag = tag;
  grid.innerHTML = '';
  index = 0;
  loader.classList.remove('hidden');
  endMsg.classList.add('hidden');

  let filtered = (tag === 'all') 
    ? [...posts] 
    : posts.filter(p => p.tags?.some(t => t.trim().toLowerCase() === tag.toLowerCase()));

  // Recent first
  filtered.sort((a, b) => new Date(b.date || '1970-01-01') - new Date(a.date || '1970-01-01'));

  // Shuffle
  for (let i = filtered.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [filtered[i], filtered[j]] = [filtered[j], filtered[i]];
  }

  currentPosts = filtered;

  // Debug: show how many posts we have
  console.log(`Showing ${currentPosts.length} posts for tag: ${tag}`);

  loadBatch();
}

// ────────────────────────────────────────────────
// Tag buttons
// ────────────────────────────────────────────────
document.querySelectorAll('.tag-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tag-btn').forEach(b => {
      b.classList.remove('tag-active');
      b.classList.add('bg-slate-200/80', 'dark:bg-slate-800/70');
    });
    btn.classList.add('tag-active');
    btn.classList.remove('bg-slate-200/80', 'dark:bg-slate-800/70');
    filterByTag(btn.dataset.tag);
  });
});

// ────────────────────────────────────────────────
// Search
// ────────────────────────────────────────────────
function handleSearch(term) {
  term = (term || '').toLowerCase().trim();
  grid.innerHTML = '';
  index = 0;
  loader.classList.remove('hidden');
  endMsg.classList.add('hidden');

  let base = currentTag === 'all' ? [...posts] : currentPosts;

  if (term) {
    base = base.filter(p =>
      p.title?.toLowerCase().includes(term) ||
      p.author?.toLowerCase().includes(term) ||
      p.preview?.toLowerCase().includes(term) ||
      p.tags?.some(t => t.toLowerCase().includes(term))
    );
  }

  // Re-apply recent + shuffle
  base.sort((a, b) => new Date(b.date || '1970-01-01') - new Date(a.date || '1970-01-01'));
  for (let i = base.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [base[i], base[j]] = [base[j], base[i]];
  }

  currentPosts = base;
  loadBatch();
}

searchInputs.forEach(input => {
  if (input) input.addEventListener('input', e => handleSearch(e.target.value));
});

// ────────────────────────────────────────────────
// Mobile search toggle
// ────────────────────────────────────────────────
document.getElementById('searchToggle')?.addEventListener('click', () => {
  document.getElementById('mobileSearch').classList.toggle('hidden');
  document.getElementById('searchInputMobile')?.focus();
});

// ────────────────────────────────────────────────
// Mobile menu
// ────────────────────────────────────────────────
const mobileMenu = document.getElementById('mobile-menu');
const menuBtn    = document.getElementById('menuBtn');
const closeBtn   = document.getElementById('closeMenu');
const overlay    = document.getElementById('menuOverlay');

function openMenu()  { 
  mobileMenu?.classList.remove('translate-x-full', 'pointer-events-none'); 
  document.body.style.overflow = 'hidden'; 
}
function closeMenu() { 
  mobileMenu?.classList.add('translate-x-full', 'pointer-events-none'); 
  document.body.style.overflow = ''; 
}

menuBtn?.addEventListener('click', openMenu);
closeBtn?.addEventListener('click', closeMenu);
overlay?.addEventListener('click', closeMenu);

// ────────────────────────────────────────────────
// Theme toggle
// ────────────────────────────────────────────────
const toggleTheme = () => {
  document.documentElement.classList.toggle('dark');
  localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
};
document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
document.getElementById('themeToggleMob')?.addEventListener('click', toggleTheme);

if (localStorage.theme === 'dark' || 
    (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
  document.documentElement.classList.add('dark');
}

// ────────────────────────────────────────────────
// Infinite scroll
// ────────────────────────────────────────────────
new IntersectionObserver(entries => {
  if (entries[0].isIntersecting) loadBatch();
}, { threshold: 0.1 }).observe(document.getElementById('sentinel'));

// ────────────────────────────────────────────────
// Start
// ────────────────────────────────────────────────
filterByTag('all');
</script>
</body>
</html>
