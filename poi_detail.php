<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_login('pages/login.php');

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/layout.php';

$id = trim((string)($_GET['id'] ?? ''));

$poi = null;
$error = '';

if ($id === '') {
    $error = 'Thiếu POI ID.';
} else {
    try {
        $stmt = $conn->prepare('SELECT p.id, p.name, p.description, p.lat, p.lng, p.categoryId, c.name AS categoryName FROM pois p LEFT JOIN categories c ON c.id = p.categoryId WHERE p.id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (is_array($row) && (string)($row['id'] ?? '') !== '') {
            $poi = $row;
        } else {
            $error = 'Không tìm thấy POI.';
        }
    } catch (Throwable $e) {
        $error = 'Không thể tải chi tiết POI.';
    }
}

layout_start('pois', 'Chi tiết POI | POI Admin');
?>

<div class="flex items-start justify-between gap-6">
    <div>
        <h1 class="text-2xl font-semibold">Chi tiết POI</h1>
        <p class="text-sm text-slate-500 mt-1">Xem thông tin chi tiết điểm tham quan.</p>
    </div>

    <div class="flex items-center gap-3">
        <?php if ($poi) : ?>
            <a href="<?php echo htmlspecialchars(app_url('poi_form.php?id=' . rawurlencode((string)$poi['id'])), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-700 transition">
                <i class="bi bi-pencil"></i>
                <span>Sửa</span>
            </a>
        <?php endif; ?>

        <a href="<?php echo htmlspecialchars(app_url('pages/pois.php'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 text-slate-700 px-4 py-2 text-sm font-semibold hover:bg-slate-50 transition">
            <i class="bi bi-arrow-left"></i>
            <span>Quay lại</span>
        </a>
    </div>
</div>

<?php if ($error !== '') : ?>
    <div class="mt-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php else :
    $name = (string)($poi['name'] ?? '');
    $desc = (string)($poi['description'] ?? '');
    $categoryId = (string)($poi['categoryId'] ?? '');
    $categoryName = (string)($poi['categoryName'] ?? '');
    $categoryLabel = $categoryName !== '' ? $categoryName : $categoryId;
    $lat = (float)($poi['lat'] ?? 0);
    $lng = (float)($poi['lng'] ?? 0);
?>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
        <section class="lg:col-span-5">
            <div class="bg-white border border-slate-200 rounded-2xl p-6">
                <div class="text-sm text-slate-500">Mã POI</div>
                <div class="mt-1 font-semibold"><?php echo htmlspecialchars((string)$poi['id'], ENT_QUOTES, 'UTF-8'); ?></div>

                <div class="mt-5 text-sm text-slate-500">Tên POI</div>
                <div class="mt-1 font-semibold"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>

                <div class="mt-5 text-sm text-slate-500">Thể loại</div>
                <div class="mt-1">
                    <?php if ($categoryLabel !== '') : ?>
                        <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2.5 py-1 text-xs font-semibold">
                            <?php echo htmlspecialchars($categoryLabel, ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                    <?php else : ?>
                        <span class="text-slate-400 text-sm">(Chưa có)</span>
                    <?php endif; ?>
                </div>

                <div class="mt-5 text-sm text-slate-500">Mô tả</div>
                <div class="mt-1 text-sm text-slate-700 whitespace-pre-line"><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></div>

                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-sm text-slate-500">Latitude</div>
                        <div class="mt-1 font-semibold"><?php echo htmlspecialchars((string)$lat, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">Longitude</div>
                        <div class="mt-1 font-semibold"><?php echo htmlspecialchars((string)$lng, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="lg:col-span-7">
            <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
                <div id="detailMap" class="h-[520px] w-full"></div>
            </div>
            <div class="text-[11px] text-slate-400 mt-2">Bản đồ dùng dữ liệu từ OpenStreetMap.</div>
        </section>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    (function () {
      if (typeof L === 'undefined') return;
      const lat = <?php echo json_encode($lat); ?>;
      const lng = <?php echo json_encode($lng); ?>;
      const name = <?php echo json_encode($name, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
      const category = <?php echo json_encode($categoryLabel, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

      const map = L.map('detailMap');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);

      const ll = [Number(lat), Number(lng)];
      map.setView(ll, 16);

      const marker = L.marker(ll).addTo(map);
      const safeName = (name ?? '').toString();
      const safeCat = (category ?? '').toString();

      marker.bindPopup(
        `<div style="font-weight:600">${escapeHtml(safeName)}</div>` +
        (safeCat ? `<div style="font-size:12px;color:#64748b">${escapeHtml(safeCat)}</div>` : '')
      );
      marker.openPopup();

      function escapeHtml(str) {
        return str
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", '&#039;');
      }
    })();
    </script>
<?php endif; ?>

<?php layout_end(); ?>
