-- Bảng Thư viện (Quản lý quyền truy cập và nhóm tài liệu)
CREATE TABLE IF NOT EXISTS libraries (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    access_level VARCHAR(30) NOT NULL DEFAULT 'private',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Bảng Trang web đã lưu (Nguồn gốc, URL gốc)
CREATE TABLE IF NOT EXISTS saved_pages (
    id SERIAL PRIMARY KEY,
    library_id INTEGER NOT NULL REFERENCES libraries(id) ON DELETE CASCADE,
    url TEXT NOT NULL,
    title VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Bảng Phiên bản nội dung (Snapshot: Giữ bản cũ và bản mới khi cập nhật)
CREATE TABLE IF NOT EXISTS snapshots (
    id SERIAL PRIMARY KEY,
    saved_page_id INTEGER NOT NULL REFERENCES saved_pages(id) ON DELETE CASCADE,
    version_number INTEGER NOT NULL DEFAULT 1,
    content_html TEXT NOT NULL,
    content_text TEXT NOT NULL, -- Dùng để tìm kiếm từ khóa trong thân bài
    fetched_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Bảng Tài nguyên cục bộ (Ảnh, tệp hỗ trợ mở bản lưu không phụ thuộc trang gốc)
CREATE TABLE IF NOT EXISTS assets (
    id SERIAL PRIMARY KEY,
    snapshot_id INTEGER NOT NULL REFERENCES snapshots(id) ON DELETE CASCADE,
    original_url TEXT NOT NULL,
    local_path VARCHAR(255) NOT NULL
);

-- Bảng Nhãn (Tags) và liên kết
CREATE TABLE IF NOT EXISTS tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE IF NOT EXISTS page_tags (
    saved_page_id INTEGER NOT NULL REFERENCES saved_pages(id) ON DELETE CASCADE,
    tag_id INTEGER NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (saved_page_id, tag_id)
);

-- Bảng Ghi chú riêng cho từng bản đã lưu
CREATE TABLE IF NOT EXISTS notes (
    id SERIAL PRIMARY KEY,
    saved_page_id INTEGER NOT NULL REFERENCES saved_pages(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

-- Chèn dữ liệu mẫu ban đầu để kiểm thử
INSERT INTO libraries (id, name, access_level) VALUES 
(1, 'Thư viện Nghiên cứu Mã nguồn mở', 'public') 
ON CONFLICT (id) DO NOTHING;