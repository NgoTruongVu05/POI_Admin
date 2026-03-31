<?php
require_once __DIR__ . '/../auth.php';
require_login('login.php');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../layout.php';

$pois = [];
try {
    $stmt = $conn->query('SELECT id, name, description, lat, lng, category FROM pois ORDER BY id DESC');
    $pois = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $pois = [];
}

layout_start('pois', 'Quản lý POIs | POI Admin');
?>

<div class="flex items-start justify-between gap-6">
    <div>
        <h1 class="text-2xl font-semibold">Quản lý POIs</h1>
        <p class="text-sm text-slate-500 mt-1">Thêm, sửa, xoá các điểm tham quan trên bản đồ.</p>
    </div>

    <a href="<?php echo htmlspecialchars(app_url('poi_form.php'), ENT_QUOTES, 'UTF-8'); ?>" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 text-white px-4 py-2 text-sm font-semibold hover:bg-blue-700 transition">
        <i class="bi bi-plus-lg"></i>
        <span>Thêm POI mới</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
    <section class="lg:col-span-4">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                <i class="bi bi-search"></i>
            </span>
            <input id="poiSearch" type="text" class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:outline-none transition" placeholder="Tìm kiếm POI..." autocomplete="off" />
        </div>

        <div class="mt-4 bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div id="poiList" class="divide-y divide-slate-100">
                <?php if (empty($pois)) : ?>
                    <div class="px-5 py-6 text-sm text-slate-500 text-center">Không tìm thấy POI nào.</div>
                <?php else : ?>
                    <?php foreach ($pois as $poi) :
                        $id = (int)$poi['id'];
                        $name = (string)($poi['name'] ?? '');
                        $desc = (string)($poi['description'] ?? '');
                        $lat = (float)($poi['lat'] ?? 0);
                        $lng = (float)($poi['lng'] ?? 0);
                        $category = (string)($poi['category'] ?? '');
                        $searchTextRaw = $name . ' ' . $desc . ' ' . $category;
                        $searchText = function_exists('mb_strtolower')
                            ? mb_strtolower($searchTextRaw, 'UTF-8')
                            : strtolower($searchTextRaw);
                    ?>
                        <button
                            type="button"
                            class="poi-item w-full text-left px-5 py-4 hover:bg-slate-50 transition"
                            data-id="<?php echo $id; ?>"
                            data-name="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                            data-desc="<?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>"
                            data-lat="<?php echo $lat; ?>"
                            data-lng="<?php echo $lng; ?>"
                            data-search="<?php echo htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mt-0.5">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold truncate"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></div>
                                    <?php if (!empty($category)) : ?>
                                        <div class="mt-2">
                                            <span class="inline-flex items-center rounded-full bg-slate-100 text-slate-600 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide">
                                                <?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="lg:col-span-8">
        <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div id="map" class="h-[540px] w-full"></div>
        </div>
        <div class="text-[11px] text-slate-400 mt-2">Bản đồ dùng dữ liệu từ OpenStreetMap.</div>
    </section>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
  const pois = <?php echo json_encode($pois, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

  const map = L.map('map');
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  const markersById = new Map();
  const bounds = [];

  for (const poi of pois) {
    const id = Number(poi.id);
    const lat = Number(poi.lat);
    const lng = Number(poi.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) continue;

    const marker = L.marker([lat, lng]).addTo(map);
    const safeName = (poi.name ?? '').toString();
    const safeDesc = (poi.description ?? '').toString();
    marker.bindPopup(`<div style="font-weight:600">${escapeHtml(safeName)}</div><div style="font-size:12px;color:#64748b">${escapeHtml(safeDesc)}</div>`);

    markersById.set(id, marker);
    bounds.push([lat, lng]);
  }

  if (bounds.length > 0) {
    map.fitBounds(bounds, { padding: [30, 30] });
  } else {
    map.setView([10.8231, 106.6297], 12);
  }

  const items = Array.from(document.querySelectorAll('.poi-item'));
  items.forEach((btn) => {
    btn.addEventListener('click', () => {
      const id = Number(btn.dataset.id);
      const lat = Number(btn.dataset.lat);
      const lng = Number(btn.dataset.lng);

      if (Number.isFinite(lat) && Number.isFinite(lng)) {
        map.setView([lat, lng], 15, { animate: true });
      }

      const marker = markersById.get(id);
      if (marker) {
        marker.openPopup();
      }
    });
  });

  const search = document.getElementById('poiSearch');
  if (search) {
    search.addEventListener('input', () => {
      const q = (search.value || '').trim().toLowerCase();
      let visibleCount = 0;

      for (const btn of items) {
        const hay = (btn.dataset.search || '');
        const isVisible = q === '' ? true : hay.includes(q);
        btn.style.display = isVisible ? '' : 'none';
        if (isVisible) visibleCount++;
      }

      const emptyRow = document.getElementById('poiEmptyRow');
      if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
      }
    });
  }

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

<?php layout_end(); ?>
