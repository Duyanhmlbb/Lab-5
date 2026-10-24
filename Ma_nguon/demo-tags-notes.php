<?php
declare(strict_types=1);
require_once __DIR__.'/src/ArchiveRepository.php';

try {
    $repo = new ArchiveRepository(db());

    $html = '<html><head><title>Trang Web Học PHP</title></head><body><h1>Học PHP Nâng Cao</h1><p>Nội dung chi tiết về lập trình PHP và CSDL PostgreSQL.</p></body></html>';
    
    echo "--- LƯU TRANG VÀ GẮN NHÃN, GHI CHÚ ---\n";
    $pageId = $repo->savePage(1, 'https://example.com/php-advanced', $html);
    
    // Thêm nhãn và ghi chú
    $repo->addTagToPage($pageId, 'php');
    $repo->addTagToPage($pageId, 'backend');
    $repo->addNote($pageId, 'Cần đọc kỹ phần kết nối CSDL PostgreSQL.');
    
    echo "Đã lưu trang ID: $pageId kèm nhãn (php, backend) và ghi chú thành công!\n\n";

    // Tìm kiếm có lọc theo nhãn
    echo "--- TÌM KIẾM CÓ LỌC THEO NHÃN 'php' VÀ TỪ KHÓA 'PostgreSQL' ---\n";
    $results = $repo->searchPages(1, 'PostgreSQL', 'php');
    echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

} catch (Throwable $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}