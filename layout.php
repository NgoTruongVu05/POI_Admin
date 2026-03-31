<?php

declare(strict_types=1);

function app_base_path(): string
{
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '/');
    $dir = str_replace('\\', '/', dirname($scriptName));
    $dir = rtrim($dir, '/');

    if ($dir === '') {
        $dir = '/';
    }

    if ($dir !== '/' && substr($dir, -6) === '/pages') {
        $dir = substr($dir, 0, -6);
        $dir = $dir === '' ? '/' : $dir;
    }

    return $dir;
}

function app_url(string $path): string
{
    $path = ltrim($path, '/');
    $base = app_base_path();
    if ($base === '/') {
        return '/' . $path;
    }

    return $base . '/' . $path;
}

function layout_start(string $activePage, string $title = 'POI Admin'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $username = (string)($_SESSION['username'] ?? 'Admin');
    $usernameEsc = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
    $avatarLetter = strtoupper(substr($username, 0, 1));
    $avatarLetterEsc = htmlspecialchars($avatarLetter, ENT_QUOTES, 'UTF-8');

    $navItems = [
        ['href' => app_url('index.php'), 'key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-grid'],
        ['href' => app_url('pages/pois.php'), 'key' => 'pois', 'label' => 'Quản lý POIs', 'icon' => 'bi-geo-alt'],
        ['href' => app_url('pages/tours.php'), 'key' => 'tours', 'label' => 'Quản lý Tours', 'icon' => 'bi-diagram-3'],
        ['href' => app_url('pages/settings.php'), 'key' => 'settings', 'label' => 'Cài đặt', 'icon' => 'bi-gear'],
    ];

    echo "<!DOCTYPE html>\n";
    echo "<html lang=\"vi\">\n";
    echo "<head>\n";
    echo "  <meta charset=\"utf-8\" />\n";
    echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\" />\n";
    echo "  <title>" . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . "</title>\n";
    echo "  <script src=\"https://cdn.tailwindcss.com\"></script>\n";
    echo "  <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css\" />\n";
    echo "</head>\n";

    echo "<body class=\"bg-slate-50 text-slate-900\">\n";
    echo "  <div class=\"min-h-screen flex\">\n";

    // Sidebar
    echo "    <aside class=\"w-72 bg-white border-r border-slate-100 flex flex-col\">\n";
    echo "      <div class=\"px-6 py-5\">\n";
    echo "        <div class=\"text-xl font-semibold text-blue-600\">POI Admin</div>\n";
    echo "      </div>\n";

    echo "      <nav class=\"px-4 space-y-1\">\n";
    foreach ($navItems as $item) {
        $isActive = $activePage === $item['key'];
        $base = 'flex items-center gap-3 px-4 py-3 rounded-xl text-sm transition';
        $cls = $isActive
            ? $base . ' bg-blue-50 text-blue-600'
            : $base . ' text-slate-600 hover:bg-slate-50';

        echo "        <a href=\"" . htmlspecialchars($item['href'], ENT_QUOTES, 'UTF-8') . "\" class=\"" . $cls . "\">";
        echo "<i class=\"bi " . htmlspecialchars($item['icon'], ENT_QUOTES, 'UTF-8') . "\"></i>";
        echo "<span>" . htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') . "</span>";
        echo "</a>\n";
    }
    echo "      </nav>\n";

    echo "      <div class=\"mt-auto px-4 py-4\">\n";
    echo "        <a href=\"" . htmlspecialchars(app_url('pages/logout.php'), ENT_QUOTES, 'UTF-8') . "\" class=\"flex items-center gap-3 px-4 py-3 rounded-xl text-sm text-slate-600 hover:bg-slate-50 transition\">\n";
    echo "          <i class=\"bi bi-box-arrow-right\"></i><span>Đăng xuất</span>\n";
    echo "        </a>\n";
    echo "      </div>\n";
    echo "    </aside>\n";

    // Main
    echo "    <div class=\"flex-1 flex flex-col\">\n";

    // Topbar
    echo "      <header class=\"h-16 bg-white border-b border-slate-100 flex items-center justify-between px-8\">\n";
    echo "        <div class=\"flex items-center gap-3\">\n";
    echo "          <button type=\"button\" class=\"w-9 h-9 rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition flex items-center justify-center\" aria-label=\"Close\">\n";
    echo "            <i class=\"bi bi-x-lg\"></i>\n";
    echo "          </button>\n";
    echo "        </div>\n";

    echo "        <div class=\"flex items-center gap-3\">\n";
    echo "          <div class=\"text-sm text-slate-600\">" . $usernameEsc . "</div>\n";
    echo "          <div class=\"w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold\">" . $avatarLetterEsc . "</div>\n";
    echo "        </div>\n";
    echo "      </header>\n";

    echo "      <main class=\"flex-1 px-8 py-6\">\n";
}

function layout_end(): void
{
    echo "      </main>\n";
    echo "    </div>\n";
    echo "  </div>\n";
    echo "</body>\n";
    echo "</html>\n";
}
