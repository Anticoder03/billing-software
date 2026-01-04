<?php
$type = $_GET['type'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Billing Web</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <header class="bg-white shadow-sm">
    <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between">
      <a href="/billing-web/index.php" class="text-xl font-semibold">Billing Web</a>
      <nav class="flex gap-4">
        <a class="text-sm text-gray-700 hover:text-black" href="/billing-web/documents_list.php?type=bill">Bills</a>
        <a class="text-sm text-gray-700 hover:text-black" href="/billing-web/documents_list.php?type=invoice">Invoices</a>
        <a class="text-sm text-white bg-blue-600 hover:bg-blue-700 rounded px-3 py-1" href="/billing-web/document_create.php?type=bill">New Bill</a>
        <a class="text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded px-3 py-1" href="/billing-web/document_create.php?type=invoice">New Invoice</a>
      </nav>
    </div>
  </header>
  <main class="max-w-6xl mx-auto px-4 py-6">