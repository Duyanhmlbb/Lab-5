<?php
declare(strict_types=1);
require_once __DIR__.'/Database.php';

final class RegistrationRepository {
    public function __construct(private PgSql\Connection $connection) {}

    public function save(int $eventId, string $name, string $email): string {
        $email = strtolower(trim($email));
        $name = trim($name);
        
        // Kiểm tra tính hợp lệ của dữ liệu đầu vào
        if ($eventId <= 0 || $name === '' || strlen($name) > 120
            || str_contains($name, "\0") || strlen($email) > 200
            || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('REGISTRATION_INVALID');
        }

        // Thực thi truy vấn chèn dữ liệu, nếu trùng cặp (event_id, email) sẽ bỏ qua nhờ ON CONFLICT
        $result = queryParams($this->connection,
            'INSERT INTO registrations (event_id, name, email) VALUES ($1, $2, $3)
             ON CONFLICT (event_id, email) DO NOTHING RETURNING id',
            [$eventId, $name, $email]);

        // Nếu trả về đúng 1 hàng nghĩa là tạo mới thành công, ngược lại là trùng lặp
        return pg_num_rows($result) === 1 ? 'CREATED' : 'DUPLICATE';
    }

    public function listForEvent(int $eventId): array {
        $result = queryParams($this->connection,
            'SELECT id, event_id, name, email FROM registrations WHERE event_id = $1 ORDER BY id',
            [$eventId]);
        return pg_fetch_all($result) ?: [];
    }
}