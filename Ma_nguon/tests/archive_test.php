<?php
declare(strict_types=1);
require_once __DIR__.'/../src/ArchiveRepository.php';
require_once __DIR__.'/../src/HtmlCleaner.php';

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $testName, bool $condition): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "PASS: $testName\n";
        $testsPassed++;
    } else {
        echo "FAIL: $testName\n";
        $testsFailed++;
    }
}

try {
    $db = db();
    $repo = new ArchiveRepository($db);

    echo "=== BẮT ĐẦU CHẠY BỘ KIỂM THỬ ĐỀ TÀI 16 ===\n";

    // Test 1: Kiểm tra làm sạch HTML (Chặn script độc hại - Yêu cầu kỹ thuật 2)
    $dirtyHtml = '<html><head><title>Test Title</title></head><body><script>alert("hack");</script><p>Nội dung sạch PostgreSQL.</p></body></html>';
    $cleanText = HtmlCleaner::extractText($dirtyHtml);
    assertTest("Chặn và làm sạch script", !str_contains($cleanText, 'alert') && str_contains($cleanText, 'PostgreSQL'));

    // Test 2: Lưu trang và tìm kiếm từ khóa trong thân bài (Yêu cầu 4)
    $pageId = $repo->savePage(1, 'https://example.com/test-page', $dirtyHtml);
    $searchResults = $repo->searchPages(1, 'PostgreSQL');
    assertTest("Tìm kiếm từ khóa trong thân bài", count($searchResults) > 0);

    // Test 3: Cập nhật phiên bản mới giữ bản cũ (Yêu cầu 5)
    $newHtml = '<html><head><title>Test Title V2</title></head><body><p>Nội dung phiên bản mới.</p></body></html>';
    $newRepoId = $repo->savePage(1, 'https://example.com/test-page', $newHtml);
    assertTest("Tạo phiên bản snapshot mới thành công", $newRepoId === $pageId);

    // Test 4: Kiểm tra quyền xuất dữ liệu theo thư viện (Yêu cầu 5 & Rubric an toàn)
    $exportData = $repo->exportPage($pageId, 1);
    assertTest("Xuất dữ liệu đúng phạm vi thư viện", $exportData !== null && $exportData['title'] === 'Test Title V2');

    echo "\nKẾT QUẢ: TESTS=" . ($testsPassed + $testsFailed) . " FAILED=$testsFailed\n";

} catch (Throwable $e) {
    echo "Lỗi ngoại lệ trong quá trình test: " . $e->getMessage() . "\n";
}