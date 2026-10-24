<?php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $configPath = __DIR__ . '/../.local/db.json';
        if (is_file($configPath)) {
            $config = json_decode((string) file_get_contents($configPath), true);
            if (!is_array($config)) {
                throw new RuntimeException('DATABASE_CONFIG_INVALID');
            }
            foreach (['PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD'] as $key) {
                if (!isset($config[$key]) || !is_string($config[$key])) {
                    throw new RuntimeException('DATABASE_CONFIG_INVALID');
                }
                putenv($key . '=' . $config[$key]);
            }
        }

        $settings = [];
        foreach (['PGHOST', 'PGPORT', 'PGDATABASE', 'PGUSER', 'PGPASSWORD'] as $key) {
            $value = getenv($key);
            if ($value === false || $value === '') {
                throw new RuntimeException('DATABASE_CONFIG_MISSING');
            }
            $settings[$key] = $value;
        }

        $dsn = "pgsql:host={$settings['PGHOST']};port={$settings['PGPORT']};dbname={$settings['PGDATABASE']}";
        $pdo = new PDO($dsn, $settings['PGUSER'], $settings['PGPASSWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}