<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NewsRoom API</title>
    <meta name="description" content="NewsRoom: a headless CMS REST API built with Laravel 12, with versioned v1/v2 endpoints. Try it live with demo accounts.">
    <style>
        :root { --bg: #fafafa; --surface: #fff; --border: #e4e4e7; --text: #52525b; --text-h: #09090b; --accent: #0e7490; --accent-bg: rgba(14, 116, 144, .08); }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #0b0d12; --surface: #12151c; --border: #232733; --text: #a1a1aa; --text-h: #f4f4f5; --accent: #22d3ee; --accent-bg: rgba(34, 211, 238, .1); }
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font: 16px/1.6 system-ui, 'Segoe UI', Roboto, sans-serif; }
        .wrap { max-width: 880px; margin: 0 auto; padding: 48px 16px 64px; }
        h1 { color: var(--text-h); font-size: 40px; margin: 0; letter-spacing: -.02em; }
        h2 { color: var(--text-h); font-size: 20px; margin: 40px 0 12px; }
        .lead { font-size: 18px; margin: 8px 0 24px; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; }
        .btn { display: inline-block; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: 600; border: 1px solid var(--border); color: var(--text-h); background: var(--surface); }
        .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        @media (prefers-color-scheme: dark) { .btn.primary { color: #0b0d12; } }
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 4px 16px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { text-align: left; padding: 10px 4px; border-bottom: 1px solid var(--border); vertical-align: top; }
        tr:last-child td { border-bottom: 0; }
        th { color: var(--text-h); font-size: 14px; }
        code { font-family: ui-monospace, Consolas, monospace; font-size: 14px; background: var(--accent-bg); color: var(--text-h); padding: 2px 6px; border-radius: 4px; }
        a { color: var(--accent); }
        ol, ul { padding-left: 20px; margin: 0; }
        li { margin: 6px 0; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 12px; }
        .grid .card { padding: 14px 16px; }
        .grid b { color: var(--text-h); display: block; margin-bottom: 4px; }
        .note { font-size: 14px; margin-top: 12px; }
        footer { margin-top: 48px; font-size: 14px; }
        @media (max-width: 560px) { h1 { font-size: 32px; } .hide-sm { display: none; } }
    </style>
</head>
<body>
<main class="wrap">
    <h1>NewsRoom API</h1>
    <p class="lead">A headless CMS REST API for a company newsroom, built with Laravel 12: writers publish articles, readers comment, and admins review content. Web clients use <code>v1</code>, mobile clients use <code>v2</code>.</p>
    <div class="actions">
        <a class="btn primary" href="{{ url('/docs/api') }}">Try the API in your browser →</a>
        <a class="btn" href="https://github.com/hasan123dayoub-del/NewsRoom">Source on GitHub</a>
    </div>

    <h2>Try it in three steps</h2>
    <div class="card" style="padding: 14px 16px">
        <ol>
            <li>Open the <a href="{{ url('/docs/api') }}">interactive docs</a> and send <code>POST /api/login</code>; it is pre-filled with the writer account.</li>
            <li>Copy the <code>access_token</code> from the response.</li>
            <li>Open any protected endpoint, paste the token as the <b>Bearer token</b>, and press <b>Send API Request</b>.</li>
        </ol>
    </div>

    <h2>Demo accounts</h2>
    <div class="card">
        <table>
            <tr><th>Role</th><th>Email</th><th>Password</th><th class="hide-sm">Can</th></tr>
            <tr><td>Writer</td><td><code>writer@technova.com</code></td><td><code>password</code></td><td class="hide-sm">create, edit, publish and delete own articles</td></tr>
            <tr><td>Reader</td><td><code>reader@technova.com</code></td><td><code>password</code></td><td class="hide-sm">read and comment on articles</td></tr>
            <tr><td>Admin</td><td><code>admin@technova.com</code></td><td><code>password</code></td><td class="hide-sm">review and manage all articles</td></tr>
        </table>
    </div>

    <h2>Public endpoints (no token needed)</h2>
    <div class="card">
        <table>
            <tr><td><a href="{{ url('/api/v1/articles') }}"><code>GET /api/v1/articles</code></a></td><td>Published articles for web clients (paginated)</td></tr>
            <tr><td><a href="{{ url('/api/v2/articles') }}"><code>GET /api/v2/articles</code></a></td><td>The same articles with reading time, tags and comment counts</td></tr>
            <tr><td><a href="{{ url('/api/v2/articles/1') }}"><code>GET /api/v2/articles/1</code></a></td><td>One article with its comments</td></tr>
            <tr><td><a href="{{ url('/api/v1/dashboard') }}"><code>GET /api/v1/dashboard</code></a></td><td>Newsroom statistics and top tags (cached)</td></tr>
        </table>
    </div>

    <h2>What's inside</h2>
    <div class="grid">
        <div class="card"><b>Versioned API</b>v1 and v2 share one codebase; v2 adds fields for mobile without breaking web clients.</div>
        <div class="card"><b>Contextual binding</b>Admin and writer controllers get different notification senders from the container.</div>
        <div class="card"><b>No cache stampede</b>An atomic lock lets one request rebuild the dashboard cache while the others wait.</div>
        <div class="card"><b>Rate limiting</b>60 requests per minute per user or IP address.</div>
        <div class="card"><b>Events and jobs</b>Publishing an article notifies employees through queued jobs; scheduled jobs archive old articles and build reports.</div>
        <div class="card"><b>Tested</b>Pest feature tests run on every push with GitHub Actions.</div>
    </div>

    <p class="note">Free hosting: the server sleeps when idle, so the first request can take up to a minute. The demo database is reset on every restart.</p>

    <footer>Built by <a href="https://hasan123dayoub-del.github.io/portfolio/">Hasan Dayoub</a>.</footer>
</main>
</body>
</html>
