<?php
declare(strict_types=1);
require_once __DIR__.'/src/ArchiveRepository.php';

try {
    $repo = new ArchiveRepository(db());

    echo "--- THỰC HIỆN XUẤT DỮ LIỆU BÀI VIẾT VÀ NGUỒN ---\n";
    
    // Thử xuất trang có ID = 2 thuộc Thư viện ID = 1
    $exportData = $repo->exportPage(2, 1);

    if ($exportData) {
        echo "Tiêu đề bài viết: " . $exportData['title'] . "\n";
        echo "Đường dẫn nguồn: " . $exportData['url'] . "\n";
        echo "Thuộc thư viện: " . $exportData['library_name'] . " (Quyền: " . $exportData['access_level'] . ")\n";
        echo "Thời điểm lấy mẫu: " . $exportData['fetched_at'] . "\n";
        echo "Trạng thái: Xuất dữ liệu thành công! (Dữ liệu hợp lệ để xuất bản).\n";
    } else {
        echo "Không tìm thấy trang hoặc bạn không có quyền truy cập!\n";
    }

} catch (Throwable $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}