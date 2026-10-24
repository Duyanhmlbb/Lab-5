<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__.'/../src/ArchiveRepository.php';

$repo = new ArchiveRepository(db());
$message = '';

// Xử lý lưu trang web mới khi có yêu cầu POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');
    $tagName = trim($_POST['tag'] ?? '');
    $rawHtml = trim($_POST['html_content'] ?? '');

    // Nếu chỉ nhập URL mà chưa có HTML, tự động tải nội dung trang bằng file_get_contents
    if ($url !== '' && $rawHtml === '') {
        $options = [
            'http' => [
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ]
        ];
        $context = stream_context_create($options);
        @$fetched = file_get_contents($url, false, $context);

        if (is_string($fetched) && $fetched !== '') {
            $rawHtml = $fetched;
        }
    }

    if ($url !== '' && $rawHtml !== '') {
        try {
            $pageId = $repo->savePage(1, $url, $rawHtml);
            if ($tagName !== '') {
                $repo->addTagToPage($pageId, $tagName);
            }
            $message = "Lưu trữ trang web và tạo snapshot thành công (Mã bản lưu: $pageId)!";
        } catch (Throwable $e) {
            $message = "Lỗi khi lưu trang: " . $e->getMessage();
        }
    } else {
        $message = "Vui lòng nhập URL hợp lệ hoặc dán nội dung HTML thủ công!";
    }
}

// Xử lý tìm kiếm và lấy danh sách
$keyword = $_GET['q'] ?? '';
$filterTag = $_GET['tag'] ?? '';
$results = $repo->searchPages(1, $keyword !== '' ? $keyword : null, $filterTag !== '' ? $filterTag : null);

require_once __DIR__.'/../templates/header.php';
?>

<section style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px;">
    <h2>1. Lưu trữ trang Web mới</h2>
    <?php if ($message !== ''): ?>
        <div style="padding: 10px; background: #e2f0d9; color: #155724; margin-bottom: 15px; border-radius: 4px; border: 1px solid #c3e6cb;">
            <strong><?= htmlspecialchars($message) ?></strong>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <div style="margin-bottom: 12px;">
            <label style="display:block; font-weight:bold; margin-bottom: 4px;">Đường dẫn URL gốc:</label>
            <input type="url" name="url" placeholder="https://vi.wikipedia.org/wiki/..." required style="width: 100%; padding: 8px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 12px;">
            <label style="display:block; font-weight:bold; margin-bottom: 4px;">Nhãn (Tag):</label>
            <input type="text" name="tag" placeholder="Ví dụ: wikipedia, php, tai-lieu" style="width: 100%; padding: 8px; box-sizing: border-box;">
        </div>
        <div style="margin-bottom: 12px;">
            <label style="display:block; font-weight:bold; margin-bottom: 4px;">Mã nguồn HTML (Tùy chọn - Nếu để trống hệ thống sẽ tự tải từ URL):</label>
            <textarea name="html_content" rows="4" placeholder="Dán mã HTML thủ công vào đây nếu trang chặn tải tự động..." style="width: 100%; padding: 8px; box-sizing: border-box;"></textarea>
        </div>
        <button type="submit" style="background: #0066cc; color: #fff; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; font-weight: bold;">Lưu trang Web</button>
    </form>
</section>

<section style="background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd;">
    <h2>2. Kho lưu trữ và Tìm kiếm</h2>
    <form method="GET" action="" style="display: flex; gap: 10px; margin-bottom: 20px;">
        <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>" placeholder="Tìm từ khóa trong tiêu đề hoặc nội dung..." style="flex: 1; padding: 8px; box-sizing: border-box;">
        <input type="text" name="tag" value="<?= htmlspecialchars($filterTag) ?>" placeholder="Lọc theo tag..." style="width: 180px; padding: 8px; box-sizing: border-box;">
        <button type="submit" style="background: #28a745; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: bold;">Tìm kiếm</button>
    </form>

    <h3>Danh sách bản lưu (Kết quả: <?= count($results) ?>)</h3>
    <?php if (empty($results)): ?>
        <p style="color: #666;">Chưa có tài liệu nào trong kho lưu trữ.</p>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <?php foreach ($results as $item): ?>
                <div style="padding: 12px; border: 1px solid #eee; background: #fafafa; border-radius: 6px;">
                    <h4 style="margin: 0 0 6px 0;">
                        <a href="/view.php?id=<?= $item['id'] ?>" style="color: #0066cc; text-decoration: none; font-weight: bold;">
                            <?= htmlspecialchars($item['title'] ?? 'Không có tiêu đề') ?>
                        </a>
                        <span style="font-size: 12px; color: #555;">(Phiên bản: <?= htmlspecialchars((string)($item['version_number'] ?? 1)) ?>)</span>
                    </h4>
                    <p style="margin: 0; font-size: 13px; color: #666;">
                        Nguồn: <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" style="color: #888;"><?= htmlspecialchars($item['url']) ?></a> | Lưu lúc: <?= htmlspecialchars($item['fetched_at'] ?? '') ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__.'/../templates/footer.php'; ?>