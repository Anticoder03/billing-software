<?php
require_once __DIR__ . '/db.php';

$type = $_GET['type'] ?? 'bill';
if (!in_array($type, ['bill', 'invoice'])) {
    $type = 'bill';
}

$pdo = db();
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
    $stmt = $pdo->prepare(
        "SELECT * 
         FROM documents 
         WHERE type = ? 
           AND (number LIKE ? OR customer_name LIKE ?) 
         ORDER BY id DESC"
    );
    $like = '%' . $search . '%';
    $stmt->execute([$type, $like, $like]);
} else {
    $stmt = $pdo->prepare(
        "SELECT * 
         FROM documents 
         WHERE type = ? 
         ORDER BY id DESC"
    );
    $stmt->execute([$type]);
}

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$title = $type === 'bill' ? 'Bills' : 'Invoices';

function h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= h($title) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .tabs a {
            margin-right: 10px;
            text-decoration: none;
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            color: #333;
        }
        .tabs a.active {
            background: #333;
            color: #fff;
            border-color: #333;
        }
        form.search {
            margin: 15px 0;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background: #f5f5f5;
            text-align: left;
        }
        .empty {
            color: #666;
            margin-top: 10px;
        }
        .btn {
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-primary {
            background: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>

<h1><?= h($title) ?></h1>

<div class="tabs">
    <a href="documents_list.php?type=bill<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>"
       class="<?= $type === 'bill' ? 'active' : '' ?>">Bills</a>

    <a href="documents_list.php?type=invoice<?= $search !== '' ? '&q=' . urlencode($search) : '' ?>"
       class="<?= $type === 'invoice' ? 'active' : '' ?>">Invoices</a>
</div>

<form class="search" method="get" action="documents_list.php">
    <input type="hidden" name="type" value="<?= h($type) ?>">
    <input type="text" name="q" value="<?= h($search) ?>"
           placeholder="Search by number or customer name">
    <button type="submit">Search</button>

    <?php if ($search !== ''): ?>
        <a href="documents_list.php?type=<?= urlencode($type) ?>">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($rows)): ?>
    <div class="empty">
        No <?= h(strtolower($title)) ?> found
        <?= $search !== '' ? ' for “' . h($search) . '”' : '' ?>.
    </div>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Number</th>
            <th>Customer</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= h($row['id']) ?></td>
                <td><?= h($row['number']) ?></td>
                <td><?= h($row['customer_name']) ?></td>
                <td>
                    <a href="document_show.php?id=<?= (int)$row['id'] ?>"
                       class="btn btn-primary">
                        View
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

</body>
</html>
