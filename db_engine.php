<?php
/**
 * Epistora DB Engine - Optimized for Multi-language (Bangla/Arabic/Hindi)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

// Force UTF-8 internal encoding for multibyte character safety
mb_internal_encoding("UTF-8");

if (!defined('DATA_PATH')) {
    die("Core Engine Error: Configuration constants not loaded.");
}

class DBEngine {

    private static $pdo = null;

    private static function mysqlEnabled() {
        if (!defined('DB_BACKEND') || DB_BACKEND !== 'mysql') return false;
        if (!defined('MYSQL_HOST') || !defined('MYSQL_DATABASE') || !defined('MYSQL_USER')) return false;
        return MYSQL_HOST !== '' && MYSQL_DATABASE !== '' && MYSQL_USER !== '';
    }

    private static function getPdo() {
        if (self::$pdo !== null) return self::$pdo;
        if (!self::mysqlEnabled()) return null;
        if (!class_exists('PDO')) {
            throw new Exception('PDO extension is not available.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            MYSQL_HOST,
            MYSQL_PORT,
            MYSQL_DATABASE
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        self::$pdo = new PDO($dsn, MYSQL_USER, MYSQL_PASSWORD, $options);
        self::initMysqlSchema(self::$pdo);
        return self::$pdo;
    }

    private static function initMysqlSchema($pdo) {
        // Store JSON documents by "relative path" key, e.g. "posts.json", "user_data/123.json"
        $sql = "
            CREATE TABLE IF NOT EXISTS epistora_kv_store (
                `key` VARCHAR(512) NOT NULL,
                `value` LONGTEXT NULL,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ";
        $pdo->exec($sql);
    }

    // List keys like "user_data/123.json" for a given prefix such as "user_data/".
    public static function listKeysByPrefix($prefix) {
        $prefix = (string)$prefix;
        if ($prefix !== '' && substr($prefix, -1) !== '/') $prefix .= '/';

        $pattern = DATA_PATH . $prefix . '*.json';
        $fileKeys = [];
        $files = glob($pattern) ?: [];
        foreach ($files as $file) {
            $fileKeys[] = str_replace(DATA_PATH, '', $file);
        }

        if (!self::mysqlEnabled()) {
            return $fileKeys;
        }

        $pdo = self::getPdo();
        $like = $prefix . '%';
        $stmt = $pdo->prepare("SELECT `key` FROM epistora_kv_store WHERE `key` LIKE ? ORDER BY `key` ASC");
        $stmt->execute([$like]);
        $rows = $stmt->fetchAll();
        $mysqlKeys = array_map(fn($r) => $r['key'], $rows);

        // Merge for smoother migration: JSON may exist only on disk initially.
        $keys = array_values(array_unique(array_merge($mysqlKeys, $fileKeys)));
        return $keys;
    }

    private static function ensureStorage() {
        $folders = [DATA_PATH, USER_DATA_PATH, POST_CONTENT_PATH];
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
        }
    }

    public static function readJSON($filename) {
        $filename = (string)$filename;

        if (self::mysqlEnabled()) {
            $pdo = self::getPdo();
            $stmt = $pdo->prepare("SELECT `value` FROM epistora_kv_store WHERE `key` = ? LIMIT 1");
            $stmt->execute([$filename]);
            $row = $stmt->fetch();
            if ($row && array_key_exists('value', $row) && $row['value'] !== null) {
                $decoded = json_decode($row['value'], true);
                if (json_last_error() !== JSON_ERROR_NONE) return null;
                return $decoded;
            }
        }

        $path = DATA_PATH . $filename;
        if (!file_exists($path)) return null;

        $content = file_get_contents($path);
        if ($content === false) return null;
        $decoded = json_decode($content, true);

        // If JSON is malformed (often due to encoding issues), return null
        if (json_last_error() !== JSON_ERROR_NONE) return null;

        return $decoded;
    }

    public static function writeJSON($filename, $data) {
        $filename = (string)$filename;

        /**
         * FIX: JSON_UNESCAPED_UNICODE keeps Bangla characters readable.
         * FIX: JSON_INVALID_UTF8_SUBSTITUTE prevents the whole save from failing if one char is broken.
         */
        $json_string = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json_string === false) return false;

        // Keep legacy behavior in file mode.
        if (!self::mysqlEnabled()) {
            self::ensureStorage();
            $path = DATA_PATH . $filename;
            return file_put_contents($path, $json_string, LOCK_EX);
        }

        $mysqlOk = true;
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("
            INSERT INTO epistora_kv_store (`key`, `value`)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
        ");
        $mysqlOk = $stmt->execute([$filename, $json_string]);

        if (!self::mysqlEnabled() || (defined('DB_MIRROR_TO_FILES') && DB_MIRROR_TO_FILES)) {
            self::ensureStorage();
            $path = DATA_PATH . $filename;
            $fileOk = file_put_contents($path, $json_string, LOCK_EX);
            return $mysqlOk && $fileOk !== false;
        }

        return $mysqlOk;
    }

    public static function initVault($user_id) {
        $filename = "user_data/" . $user_id . ".json";
        if (self::readJSON($filename) !== null) return false;

        $vault_template = [
            "user_id"    => $user_id,
            "role"       => ROLE_USER,
            "created_at" => date('Y-m-d H:i:s'),
            "profile"    => ["name" => "", "email" => ""],
            "settings"   => ["bg_color" => "#ffffff", "font_style" => "sans-serif", "font_size" => "16px"],
            "history"    => [],
            "following"  => [],
            "notifications" => []
        ];
        return self::writeJSON($filename, $vault_template);
    }

    public static function updateKey($filename, $key, $value) {
        $data = self::readJSON($filename);
        if ($data !== null) {
            $data[$key] = $value;
            return self::writeJSON($filename, $data);
        }
        return false;
    }

    public static function pushNotification($target_user_id, $type, $from_name, $post_id) {
        $filename = "user_data/" . $target_user_id . ".json";
        $vault = self::readJSON($filename);
        if ($vault) {
            $notification = [
                "id"         => uniqid('ntf_'),
                "type"       => $type,
                "from_name"  => $from_name,
                "post_id"    => $post_id,
                "is_read"    => false,
                "timestamp"  => time(),
                "date_human" => date('M d, H:i')
            ];
            array_unshift($vault['notifications'], $notification);
            $vault['notifications'] = array_slice($vault['notifications'], 0, 50);
            return self::writeJSON($filename, $vault);
        }
        return false;
    }

    public static function logAction($admin_id, $admin_name, $action, $details) {
        $log_file = "system_logs.json";
        $logs = self::readJSON($log_file) ?? [];
        $new_log = [
            "id" => uniqid('log_'), "timestamp" => time(), "date" => date('Y-m-d H:i:s'),
            "admin_id" => $admin_id, "admin_name" => $admin_name, "action" => $action,
            "details" => $details, "ip" => $_SERVER['REMOTE_ADDR']
        ];
        array_unshift($logs, $new_log);
        if (count($logs) > 1000) $logs = array_slice($logs, 0, 1000);
        return self::writeJSON($log_file, $logs);
    }
}