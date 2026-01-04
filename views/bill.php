<?php
require __DIR__ . '/../db.php';
require __DIR__ . '/../config.php';

// Resolve by id or number
$doc = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id=? AND type='bill'");
    $stmt->execute([ (int)$_GET['id'] ]);
    $doc = $stmt->fetch();
} elseif (isset($_GET['number'])) {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE number=? AND type='bill'");
    $stmt->execute([ $_GET['number'] ]);
    $doc = $stmt->fetch();
}

if (!$doc) {
    http_response_code(404);
    echo "Bill not found.";
    exit;
}

$itemsStmt = $pdo->prepare("SELECT * FROM document_items WHERE document_id=? ORDER BY id ASC");
$itemsStmt->execute([ $doc['id'] ]);
$items = $itemsStmt->fetchAll();

// Helper: convert amount to words (simple, Indian numbering format)
function amountToWords($number) {
    $no = floor($number);
    $point = round($number - $no, 2) * 100;
    $hundred = null;
    $digits_1 = strlen($no);
    $i = 0;
    $str = [];
    $words = [
        0 => '', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten',
        11 => 'eleven', 12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen',
        15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen', 18 => 'eighteen',
        19 => 'nineteen', 20 => 'twenty', 30 => 'thirty', 40 => 'forty',
        50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty', 90 => 'ninety'
    ];
    $digits = ['', 'hundred', 'thousand', 'lakh', 'crore'];
    while ($i < $digits_1) {
        $divider = ($i == 2) ? 10 : 100;
        $numberPart = floor($no % $divider);
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;

        if ($numberPart) {
            $plural = (($counter = count($str)) && $numberPart > 9) ? '' : '';
            $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
            if ($numberPart < 21) {
                $str[] = $words[$numberPart] . ' ' . $digits[count($str)];
            } else {
                $str[] = $words[floor($numberPart / 10) * 10] . ' ' . $words[$numberPart % 10] . ' ' . $digits[count($str)];
            }
        } else {
            $str[] = '';
        }
    }
    $str = array_reverse($str);
    $result = trim(implode(' ', array_filter($str)));
    $points = ($point) ? $words[floor($point / 10) * 10] . ' ' . $words[$point % 10] . ' paise' : '';
    return ucfirst($result) . ($points ? " and $points" : '') . ' only';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill <?= htmlspecialchars($doc['number']) ?></title>
    <link rel="stylesheet" href="/billing-web/assets/bill.css">
</head>
<body>
<div class="bill-wrap">
    <div class="bill-top-bar">
        <div>Mob No. <?= htmlspecialchars($app['company_phones'][0]) ?></div>
        <div>Mob No. <?= htmlspecialchars($app['company_phones'][1]) ?></div>
    </div>
    <div class="bill-header">
        <div class="brand">
            <span class="brand-title"><?= htmlspecialchars($app['company_name']) ?></span>
        </div>
    </div>
    <div class="bill-subheader">
        <?= htmlspecialchars($app['company_address']) ?>
    </div>

    <div class="bill-meta">
        <div class="to-line">M/s. <?= htmlspecialchars($doc['customer_name']) ?></div>
        <div class="meta-box">
            <div><strong>Bill No.:</strong> <?= htmlspecialchars($doc['number']) ?></div>
            <div><strong>Date:</strong> <?= htmlspecialchars(date('d-m-y', strtotime($doc['date']))) ?></div>
            <div><strong>Pan No.:</strong> <?= htmlspecialchars($app['company_pan']) ?></div>
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
        <?php
        $sr = 1;
        foreach ($items as $it): ?>
            <tr>
                <td><?= $sr++ ?></td>
                <td><?= htmlspecialchars($it['description']) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['quantity'], 2) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['unit_price'], 2) ?></td>
                <td style="text-align:right"><?= number_format((float)$it['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <!-- pad empty rows for print aesthetics -->
        <?php for ($i = $sr; $i <= max(10, $sr); $i++): ?>
            <tr>
                <td>&nbsp;</td><td></td><td></td><td></td><td></td>
            </tr>
        <?php endfor; ?>
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

    <div class="bill-remarks">
        <strong>REMARKS:</strong>
        <div class="remarks-inline">
            In Words: <?= amountToWords((float)$doc['total']) ?>
        </div>
    </div>

    <div class="bill-footer">
        <div class="bank">
            <div><strong>Bank details:</strong></div>
            <div>Bank: <?= htmlspecialchars($app['bank']['name']) ?></div>
            <div>Name: <?= htmlspecialchars($app['bank']['account_name']) ?></div>
            <div>A/C No: <?= htmlspecialchars($app['bank']['account_no']) ?></div>
            <div>IFSC: <?= htmlspecialchars($app['bank']['ifsc']) ?></div>
        </div>
        <div class="signature">
            <div>For,</div>
            <div class="sign-company"><?= htmlspecialchars($app['company_name']) ?></div>
            <div>(Authorized Signature)</div>
        </div>
    </div>

    <div class="bill-actions">
        <button onclick="window.print()">Print</button>
        <a class="back-link" href="/billing-web/bills.php">Back to Bills</a>
    </div>
</div>
</body>
</html>