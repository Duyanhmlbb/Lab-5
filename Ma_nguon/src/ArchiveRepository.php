<?php
declare(strict_types=1);
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/HtmlCleaner.php';

class ArchiveRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function savePage(int $libraryId, string $url, string $rawHtml): int {
        $cleaned = HtmlCleaner::clean($rawHtml);
        $contentText = strip_tags($cleaned['content_html']);

        // Kiểm tra xem URL đã tồn tại trong thư viện chưa
        $stmt = $this->db->prepare("SELECT id FROM saved_pages WHERE library_id = ? AND url = ?");
        $stmt->execute([$libraryId, $url]);
        $page = $stmt->fetch();

        if ($page) {
            $pageId = (int)$page['id'];
            try {
                $stmtUp = $this->db->prepare("UPDATE saved_pages SET title = ? WHERE id = ?");
                $stmtUp->execute([$cleaned['title'], $pageId]);
            } catch (Throwable $e) {}

            $stmtVer = $this->db->prepare("SELECT COALESCE(MAX(version_number), 0) + 1 FROM snapshots WHERE saved_page_id = ?");
            $stmtVer->execute([$pageId]);
            $versionNumber = (int)$stmtVer->fetchColumn();
        } else {
            try {
                $stmtPage = $this->db->prepare("INSERT INTO saved_pages (library_id, url, title) VALUES (?, ?, ?) RETURNING id");
                $stmtPage->execute([$libraryId, $url, $cleaned['title']]);
            } catch (Throwable $e) {
                $stmtPage = $this->db->prepare("INSERT INTO saved_pages (library_id, url) VALUES (?, ?) RETURNING id");
                $stmtPage->execute([$libraryId, $url]);
            }
            $pageId = (int)$stmtPage->fetchColumn();
            $versionNumber = 1;
        }

        // Lưu snapshot bao gồm cả cột content_text theo đúng ràng buộc CSDL
        $stmtSnap = $this->db->prepare("INSERT INTO snapshots (saved_page_id, version_number, content_html, content_text) VALUES (?, ?, ?, ?)");
        $stmtSnap->execute([$pageId, $versionNumber, $cleaned['content_html'], $contentText]);

        return $pageId;
    }

    public function searchPages(int $libraryId, ?string $keyword = null, ?string $tag = null): array {
        $sql = "
            SELECT DISTINCT p.id, p.url, p.title, s.version_number, s.fetched_at 
            FROM saved_pages p
            JOIN snapshots s ON s.saved_page_id = p.id
            WHERE p.library_id = ? 
              AND s.version_number = (SELECT MAX(version_number) FROM snapshots WHERE saved_page_id = p.id)
        ";
        $params = [$libraryId];

        if ($keyword) {
            $sql .= " AND (p.title ILIKE ? OR s.content_text ILIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }

        if ($tag) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM tags t 
                WHERE (t.saved_page_id = p.id OR t.page_id = p.id) 
                  AND t.tag_name ILIKE ?
            )";
            $params[] = "%$tag%";
        }

        $sql .= " ORDER BY s.fetched_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function exportPage(int $pageId, int $libraryId): ?array {
        $stmt = $this->db->prepare("
            SELECT p.url, p.title, s.content_html, s.fetched_at, s.version_number, l.name as library_name
            FROM saved_pages p
            JOIN libraries l ON l.id = p.library_id
            JOIN snapshots s ON s.saved_page_id = p.id
            WHERE p.id = ? AND p.library_id = ?
            ORDER BY s.version_number DESC
            LIMIT 1
        ");
        $stmt->execute([$pageId, $libraryId]);
        return $stmt->fetch() ?: null;
    }

    public function addTagToPage(int $pageId, string $tagName): void {
        try {
            $stmt = $this->db->prepare("INSERT INTO tags (saved_page_id, tag_name) VALUES (?, ?) ON CONFLICT DO NOTHING");
            $stmt->execute([$pageId, trim($tagName)]);
        } catch (Throwable $e) {
            try {
                $stmt = $this->db->prepare("INSERT INTO tags (page_id, tag_name) VALUES (?, ?) ON CONFLICT DO NOTHING");
                $stmt->execute([$pageId, trim($tagName)]);
            } catch (Throwable $ex) {}
        }
    }
}