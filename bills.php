<?php
require __DIR__ . '/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bills</title>
    <link rel="stylesheet" href="/billing-web/assets/bill.css">
    <style>
        /* Simple table list styling */
        .list-container { max-width: 1100px; margin: 20px auto; padding: 0 12px; }
        table.list { width: 100%; border-collapse: collapse; }
        table.list th, table.list td { border: 1px solid #ddd; padding: 8px; }
        table.list th { background: #f7f7f7; text-align: left; }
        .actions a { margin-right: 8px; }
    </style>
</head>
<body>
<div class="list-container">
    <h2>All Bills</h2>
    <?php
    $stmt = $pdo->query("SELECT id, number, customer_name, date, total, status FROM documents WHERE type='bill' ORDER BY date DESC, id DESC");
    $rows = $stmt->fetchAll();
    ?>
    <table class="list">
        <thead>
            <tr>
                <th>Bill No</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total (₹)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['number']) ?></td>
                <td><?= htmlspecialchars($r['customer_name']) ?></td>
                <td><?= htmlspecialchars(date('d-m-Y', strtotime($r['date']))) ?></td>
                <td><?= htmlspecialchars($r['status']) ?></td>
                <td style="text-align:right"><?= number_format((float)$r['total'], 2) ?></td>
                <td class="actions">
                    <a href="/billing-web/views/bill.php?id=<?= (int)$r['id'] ?>">View</a>
                    <a href="/billing-web/views/bill.php?number=<?= urlencode($r['number']) ?>">By Number</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="6">No bills found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>