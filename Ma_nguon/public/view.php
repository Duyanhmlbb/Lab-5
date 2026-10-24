<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ArchiveRepository.php';

$repo = new ArchiveRepository(db());
$pageId = (int)($_GET['id'] ?? 0);
$data = $repo->exportPage($pageId, 1);

if (!$data) {
    echo "<h2 style='text-align:center; margin-top:50px;'>404 - Không tìm thấy bản lưu hoặc bạn không có quyền truy cập!</h2>";
    exit;
}

require_once __DIR__.'/../templates/header.php';
?>

<div style="margin-bottom: 15px;">
    <a href="/index.php" style="color: #0066cc; text-decoration: none;">&larr; Quay lại danh sách kho lưu trữ</a>
</div>

<article style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ddd;">
    <header style="border-bottom: 1px solid #eee; padding-bottom: 12px; margin-bottom: 20px;">
        <h1 style="margin: 0 0 10px 0; color: #222;"><?= htmlspecialchars($data['title']) ?></h1>
        <p style="margin: 0; font-size: 13px; color: #666;">
            <strong>URL gốc:</strong> <a href="<?= htmlspecialchars($data['url']) ?>" target="_blank"><?= htmlspecialchars($data['url']) ?></a> | 
            <strong>Thời điểm lưu:</strong> <?= htmlspecialchars($data['fetched_at']) ?> | 
            <strong>Phiên bản:</strong> v<?= htmlspecialchars((string)$data['version_number']) ?>
        </p>
    </header>

    <div class="reader-content" style="line-height: 1.6; color: #333;">
        <?= $data['content_html'] ?>
    </div>
</article>

<?php require_once __DIR__.'/../templates/footer.php'; ?>