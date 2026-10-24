<?php
declare(strict_types=1);

class HtmlCleaner {
    public static function clean(string $rawHtml): array {
        // 1. Trích xuất tiêu đề từ thẻ <title> của trang web gốc
        $title = 'Không có tiêu đề';
        if (preg_match('/<title>(.*?)<\/title>/is', $rawHtml, $matches)) {
            $title = trim(strip_tags($matches[1]));
        }

        // 2. Loại bỏ các thẻ mã độc, script, iframe nguy hiểm
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $rawHtml);
        $cleaned = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $cleaned);
        $cleaned = preg_replace('/on\w+\s*=\s*"[^"]*"/i', '', $cleaned);
        $cleaned = preg_replace("/on\w+\s*=\s*'[^']*'/i", '', $cleaned);

        // 3. Trích xuất nội dung text thuần (dùng cho tìm kiếm toàn văn trong thân bài)
        $bodyText = strip_tags($cleaned);

        return [
            'title' => $title,
            'content_html' => $cleaned,
            'body_text' => $bodyText
        ];
    }
}