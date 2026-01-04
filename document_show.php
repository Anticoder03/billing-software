<?php
require_once __DIR__ . '/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid document ID';
    exit;
}

$doc = get_document($id);
if (!$doc) {
    http_response_code(404);
    echo 'Document not found';
    exit;
}

$typeLabel = isset($doc['type']) && $doc['type'] === 'bill' ? 'Bill' : 'Invoice';
$number = isset($doc['number']) ? $doc['number'] : 'N/A';
$createdAt = isset($doc['created_at']) ? $doc['created_at'] : '';
$customerName = isset($doc['customer_name']) ? $doc['customer_name'] : '';
$customerAddress = isset($doc['customer_address']) ? $doc['customer_address'] : '';

function findKey(array $row, array $candidates): ?string {
    foreach ($candidates as $k) {
        if (array_key_exists($k, $row)) {
            return $k;
        }
    }
    return null;
}

$items = isset($doc['items']) && is_array($doc['items']) ? $doc['items'] : [];
$subtotal = 0.0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title><?php echo htmlspecialchars($typeLabel); ?> #<?php echo htmlspecialchars((string)$number); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        :root {
            --accent: #0d6efd;
            --border: #ddd;
            --text: #222;
            --muted: #666;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            margin: 0;
            background: #f7f7f7;
        }
        .page {
            max-width: 900px;
            margin: 20px auto;
            background: #fff;
            padding: 24px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border);
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: var(--accent);
            margin: 0;
        }
        .meta {
            text-align: right;
            font-size: 14px;
            color: var(--muted);
        }
        .section-title {
            font-weight: bold;
            margin: 20px 0 8px;
        }
        .customer {
            border: 1px solid var(--border);
            padding: 12px;
            border-radius: 6px;
            background: #fafafa;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            border: 1px solid var(--border);
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f0f6ff;
        }
        .numeric {
            text-align: right;
        }
        .totals {
            margin-top: 16px;
            display: flex;
            justify-content: flex-end;
        }
        .totals table {
            width: 300px;
        }
        .actions {
            display: flex;
            gap: 8px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            background: var(--accent);
            color: #fff;
            text-decoration: none;
            border: none;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
        }
        .btn.secondary {
            background: #6c757d;
        }
        @media print {
            .actions { display: none; }
            body { background: #fff; }
            .page { box-shadow: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <div>
            <h1 class="title"><?php echo htmlspecialchars($typeLabel); ?></h1>
            <div>Number: <?php echo htmlspecialchars((string)$number); ?></div>
        </div>
        <div class="meta">
            <div>Date: <?php echo htmlspecialchars($createdAt); ?></div>
            <div>ID: <?php echo htmlspecialchars((string)$doc['id']); ?></div>
        </div>
    </div>

    <div class="section-title">Customer</div>
    <div class="customer">
        <div><strong><?php echo htmlspecialchars($customerName ?: ''); ?></strong></div>
        <div><?php echo nl2br(htmlspecialchars($customerAddress ?: '')); ?></div>
    </div>

    <div class="section-title">Items</div>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="numeric">Qty</th>
                <th class="numeric">Unit Price</th>
                <th class="numeric">Line Total</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($items)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--muted)">No items</td></tr>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <?php
                $descKey  = findKey($item, ['description','name','item','product','title']);
                $qtyKey   = findKey($item, ['qty','quantity','amount']);
                $priceKey = findKey($item, ['unit_price','price','rate']);
                $totalKey = findKey($item, ['total','line_total','amount_total','subtotal']);

                $desc  = $descKey  ? (string)$item[$descKey]  : '';
                $qty   = $qtyKey   ? (float)$item[$qtyKey]    : 0.0;
                $price = $priceKey ? (float)$item[$priceKey]  : 0.0;
                $lineTotal = $totalKey ? (float)$item[$totalKey] : ($qtyKey && $priceKey ? $qty * $price : 0.0);

                $subtotal += $lineTotal;
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($desc); ?></td>
                    <td class="numeric"><?php echo $qtyKey ? number_format($qty, 2) : '-'; ?></td>
                    <td class="numeric"><?php echo $priceKey ? number_format($price, 2) : '-'; ?></td>
                    <td class="numeric"><?php echo number_format($lineTotal, 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal</th>
                <td class="numeric"><?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php
            // Optional tax handling if present in the document row
            $taxPercent = isset($doc['tax_percent']) ? (float)$doc['tax_percent'] : null;
            $taxAmount = null;
            if ($taxPercent !== null) {
                $taxAmount = $subtotal * ($taxPercent / 100.0);
                echo '<tr><th>Tax (' . htmlspecialchars((string)$taxPercent) . '%)</th><td class="numeric">' . number_format($taxAmount, 2) . '</td></tr>';
            }
            $grandTotal = $subtotal + ($taxAmount ?? 0.0);
            ?>
            <tr>
                <th>Total</th>
                <td class="numeric"><strong><?php echo number_format($grandTotal, 2); ?></strong></td>
            </tr>
        </table>
    </div>

    <div class="actions">
        <button class="btn" onclick="window.print()">Print</button>
        <a href="document_list.php" class="btn secondary">Back to List</a>
    </div>
</div>
</body>
</html>