<?php
require_once __DIR__ . '/db.php';

$type = $_POST['type'] ?? 'bill';
if (!in_array($type, ['bill', 'invoice'])) $type = 'bill';

$customer_name = trim($_POST['customer_name'] ?? '');
$customer_email = trim($_POST['customer_email'] ?? '');
$date = $_POST['date'] ?? date('Y-m-d');
$due_date = $_POST['due_date'] ?? null;
$notes = $_POST['notes'] ?? '';
$tax_rate = (float)($_POST['tax_rate'] ?? 0);
$items = $_POST['items'] ?? [];

if ($customer_name === '' || empty($items)) {
    header('Location: /billing-web/document_create.php?type=' . urlencode($type));
    exit;
}

$subtotal = 0.0;
$normalizedItems = [];
foreach ($items as $it) {
    $desc = trim($it['description'] ?? '');
    $qty = (float)($it['quantity'] ?? 0);
    $price = (float)($it['unit_price'] ?? 0);
    if ($desc === '' || $qty <= 0 || $price < 0) continue;
    $amount = $qty * $price;
    $subtotal += $amount;
    $normalizedItems[] = [
        'description' => $desc,
        'quantity' => $qty,
        'unit_price' => $price,
        'amount' => $amount,
    ];
}

$tax = $subtotal * ($tax_rate / 100);
$total = $subtotal + $tax;

$pdo = db();
$pdo->beginTransaction();
try {
    $number = generate_number($type);
    $stmt = $pdo->prepare("INSERT INTO documents
        (type, number, customer_name, customer_email, date, due_date, status, notes, subtotal, tax, total)
        VALUES (?, ?, ?, ?, ?, ?, 'unpaid', ?, ?, ?, ?)");
    $stmt->execute([$type, $number, $customer_name, $customer_email, $date, $due_date, $notes, $subtotal, $tax, $total]);
    $docId = (int)$pdo->lastInsertId();

    $itemStmt = $pdo->prepare("INSERT INTO document_items
        (document_id, description, quantity, unit_price, amount)
        VALUES (?, ?, ?, ?, ?)");
    foreach ($normalizedItems as $ni) {
        $itemStmt->execute([$docId, $ni['description'], $ni['quantity'], $ni['unit_price'], $ni['amount']]);
    }

    $pdo->commit();
    header('Location: /billing-web/document_show.php?id=' . $docId);
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Error saving document.";
}