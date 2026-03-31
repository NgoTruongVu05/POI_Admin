<?php
require_once __DIR__ . '/../auth.php';
require_login('login.php');

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../layout.php';

$tours = [];
try {
    $conn->query('SELECT 1 FROM tours LIMIT 1');
    $stmt = $conn->query('SELECT id, name, description FROM tours ORDER BY id DESC LIMIT 20');
    $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $tours = [];
}

layout_start('tours', 'Quản lý Tours | POI Admin');
?>

<div class="flex items-start justify-between gap-6">
    <div>
        <h1 class="text-2xl font-semibold">Quản lý Tours</h1>
        <p class="text-sm text-slate-500 mt-1">Tạo và sắp xếp lộ trình tham quan từ các POIs.</p>
    </div>

    <a href="#" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 text-white px-4 py-2 text-sm font-semibold hover:bg-emerald-700 transition">
        <i class="bi bi-plus-lg"></i>
        <span>Tạo Tour mới</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
    <section class="lg:col-span-4">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none transition" placeholder="Tìm kiếm Tour..." />
        </div>

        <div class="mt-4 bg-white border border-slate-200 rounded-2xl overflow-hidden">
            <div class="divide-y divide-slate-100">
                <?php if (empty($tours)) : ?>
                    <div class="px-5 py-6 text-sm text-slate-500 text-center">Chưa có Tour nào.</div>
                <?php else : ?>
                    <?php foreach ($tours as $tour) : ?>
                        <div class="px-5 py-4 hover:bg-slate-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mt-0.5">
                                    <i class="bi bi-diagram-3"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold truncate"><?php echo htmlspecialchars((string)$tour['name'], ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="text-xs text-slate-500 truncate"><?php echo htmlspecialchars((string)($tour['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="lg:col-span-8">
        <div class="bg-white border border-slate-200 rounded-2xl p-10 min-h-[420px] flex items-center justify-center">
            <div class="text-center max-w-md">
                <div class="mx-auto w-14 h-14 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-400">
                    <i class="bi bi-diagram-3 text-xl"></i>
                </div>
                <div class="mt-4 font-semibold">Chưa chọn Tour</div>
                <p class="mt-2 text-sm text-slate-500">Chọn một tour từ danh sách bên trái hoặc tạo tour mới để bắt đầu chỉnh sửa lộ trình.</p>
                <button type="button" class="mt-4 text-sm font-semibold text-emerald-700 hover:text-emerald-800">+ Tạo Tour mới ngay</button>
            </div>
        </div>
    </section>
</div>

<?php layout_end(); ?>
