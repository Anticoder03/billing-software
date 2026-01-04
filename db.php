<?php
require_once __DIR__ . '/config.php';
define("DB_HOST","localhost");
define("DB_NAME","billing_app");
define("DB_USER","root");
define("DB_PASS","");
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    }
    return $pdo;
}

function generate_number(string $type): string {
    $prefix = $type === 'bill' ? 'B' : 'I';
    $date = date('Ymd');
    $pdo = db();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE type = ? AND DATE(created_at) = CURDATE()");
    $stmt->execute([$type]);
    $count = (int)$stmt->fetchColumn() + 1;
    return $prefix . '-' . $date . '-' . str_pad((string)$count, 3, '0', STR_PAD_LEFT);
}

function get_document(int $id): ?array {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$id]);
    $doc = $stmt->fetch();
    if (!$doc) return null;

    $items = $pdo->prepare("SELECT * FROM document_items WHERE document_id = ? ORDER BY id ASC");
    $items->execute([$id]);
    $doc['items'] = $items->fetchAll();
    return $doc;
}