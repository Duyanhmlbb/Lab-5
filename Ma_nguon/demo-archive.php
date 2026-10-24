<?php
declare(strict_types=1);
require_once __DIR__.'/src/ArchiveRepository.php';

try {
    $repo = new ArchiveRepository(db());

    // 1. Giả lập nội dung một bài viết HTML có chứa script độc hại và từ khóa cần tìm
    $sampleHtml = '
        <html>
            <head><title>Hướng dẫn Lập trình PHP cơ bản</title></head>
            <body>
                <script>alert("Hacker script");</script>
                <h1>Bài viết hướng dẫn PHP</h1>
                <p>Nội dung cốt lõi của bài viết là hướng dẫn lập trình PHP thuần và kết nối PostgreSQL an toàn.</p>
            </body>
        </html>
    ';

    echo "--- ĐANG THỰC HIỆN LƯU TRANG VÀ LÀM SẠCH HTML ---\n";
    $savedPageId = $repo->savePage(1, 'https://example.com/php-guide', $sampleHtml);
    echo "Đã lưu trang thành công với ID: $savedPageId\n\n";

    // 2. Kiểm tra tìm kiếm từ khóa trong thân bài (Yêu cầu 4)
    echo "--- KIỂM TRA TÌM KIẾM TỪ KHÓA 'PostgreSQL' ---\n";
    $searchResults = $repo->searchPages(1, 'PostgreSQL');
    echo json_encode($searchResults, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

    // 3. Cập nhật phiên bản mới (Yêu cầu 5: Giữ bản cũ)
    $updatedHtml = '
        <html>
            <head><title>Hướng dẫn Lập trình PHP cơ bản - Cập nhật</title></head>
            <body>
                <h1>Phiên bản mới của bài viết</h1>
                <p>Nội dung đã được cập nhật thêm phần xử lý bảo mật nâng cao.</p>
            </body>
        </html>
    ';
    echo "--- TẠO PHIÊN BẢN MỚI (SNAPSHOT V2) ---\n";
    $repo->savePage(1, 'https://example.com/php-guide', $updatedHtml);
    echo "Đã tạo snapshot mới thành công!\n";

} catch (Throwable $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}