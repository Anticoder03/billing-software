<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../config.php';

// Resolve by id or number
$doc = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id=? AND type='invoice'");
    $stmt->execute([ (int)$_GET['id'] ]);
    $doc = $stmt->fetch();
} elseif (isset($_GET['number'])) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE number=? AND type='invoice'");
    $stmt->execute([ $_GET['number'] ]);
    $doc = $stmt->fetch();
}

if (!$doc) {
    http_response_code(404);
    echo "Invoice not found.";
    exit;
}

$itemsStmt = $pdo->prepare("SELECT * FROM document_items WHERE document_id=? ORDER BY id ASC");
$itemsStmt->execute([ $doc['id'] ]);
$items = $itemsStmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?= htmlspecialchars($doc['number']) ?></title>
    <link rel="stylesheet" href="/billing-web/assets/bill.css">
</head>
<body>
<div class="bill-wrap">
    <div class="bill-header">
        <div class="brand">
            <span class="brand-title"><?= htmlspecialchars($app['company_name']) ?></span>
        </div>
    </div>
    <div class="bill-subheader">
        <?= htmlspecialchars($app['company_address']) ?>
    </div>

    <div class="bill-meta">
        <div class="to-line">Bill To: <?= htmlspecialchars($doc['customer_name']) ?></div>
        <div class="meta-box">
            <div><strong>Invoice No.:</strong> <?= htmlspecialchars($doc['number']) ?></div>
            <div><strong>Date:</strong> <?= htmlspecialchars(date('d-m-Y', strtotime($doc['date']))) ?></div>
            <div><strong>Status:</strong> <?= htmlspecialchars($doc['status']) ?></div>
        </div>
    </div>

    <table class="bill-items">
        <thead>
        <tr>
            <th style="width:50px">Sr</th>
            <th>Description</th>
            <th style="width:120px; text-align:right">Qty</th>
            <th style="width:140px; text-align:right">Unit Price (₹)</th>
            <th style="width:160px; text-align:right">Amount (₹)</th>
        </tr>
        </thead>
        <tbody>
        <?php $sr = 1; foreach ($items as $it): ?>
            <tr>
                <td><?= $sr++ ?></td>
                <td><?= htmlspecialchars($it['description']) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['quantity'], 2) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['unit_price'], 2) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4" style="text-align:right"><strong>Subtotal</strong></td>
            <td style="text-align:right"><?= number_format((float)$doc['subtotal'], 2) ?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right"><strong>Tax</strong></td>
            <td style="text-align:right"><?= number_format((float)$doc['tax'], 2) ?></td>
        </tr>
        <tr>
            <td colspan="4" style="text-align:right"><strong>Total</strong></td>
            <td style="text-align:right"><strong><?= number_format((float)$doc['total'], 2) ?></strong></td>
        </tr>
        </tfoot>
    </table>

    <div class="bill-actions">
        <button onclick="window.print()">Print</button>
        <a class="back-link" href="/billing-web/invoices.php">Back to Invoices</a>
    </div>
</div>
</body>
</html>