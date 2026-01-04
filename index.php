<?php require_once __DIR__ . '/partials/header.php'; ?>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-2">Bills</h2>
    <p class="text-sm text-gray-600 mb-4">Create and manage bills.</p>
    <div class="flex gap-3">
      <a href="/billing-web/document_create.php?type=bill" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Bill</a>
      <a href="/billing-web/documents_list.php?type=bill" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">See Bills</a>
    </div>
  </div>
  <div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-lg font-semibold mb-2">Invoices</h2>
    <p class="text-sm text-gray-600 mb-4">Create and manage invoices.</p>
    <div class="flex gap-3">
      <a href="/billing-web/document_create.php?type=invoice" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Create Invoice</a>
      <a href="/billing-web/documents_list.php?type=invoice" class="px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">See Invoices</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/partials/footer.php'; ?>