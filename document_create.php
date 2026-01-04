<?php
require_once __DIR__ . '/db.php';
$type = $_GET['type'] ?? 'bill';
if (!in_array($type, ['bill', 'invoice'])) $type = 'bill';
$title = $type === 'bill' ? 'Create Bill' : 'Create Invoice';
require_once __DIR__ . '/partials/header.php';
?>
<div class="bg-white rounded-lg shadow p-6">
  <h1 class="text-xl font-semibold mb-4"><?php echo htmlspecialchars($title); ?></h1>
  <form method="post" action="/billing-web/document_store.php" id="docForm" class="space-y-6">
    <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>"/>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Customer Name</label>
        <input name="customer_name" required class="mt-1 w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Customer Email</label>
        <input name="customer_email" type="email" class="mt-1 w-full border rounded px-3 py-2" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Date</label>
        <input name="date" type="date" required class="mt-1 w-full border rounded px-3 py-2" value="<?php echo date('Y-m-d'); ?>" />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700">Due Date</label>
        <input name="due_date" type="date" class="mt-1 w-full border rounded px-3 py-2" />
      </div>
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700">Notes</label>
      <textarea name="notes" rows="3" class="mt-1 w-full border rounded px-3 py-2"></textarea>
    </div>

   <div>
  <div class="flex items-center justify-between mb-2">
    <label class="text-sm font-medium text-gray-700">Items</label>
    <button
      type="button"
      id="addItem"
      class="px-3 py-1 bg-gray-100 rounded hover:bg-gray-200 text-sm"
    >
      Add Item
    </button>
  </div>

  <!-- Column labels -->
  <div class="grid grid-cols-12 gap-2 mb-1 text-xs font-medium text-gray-600">
    <div class="col-span-6">Description</div>
    <div class="col-span-2">Qty</div>
    <div class="col-span-2">Unit Price</div>
    <div class="col-span-2">Amount</div>
  </div>

  <div id="items" class="space-y-3">
    <div class="grid grid-cols-12 gap-2 item-row">
      <input
        class="col-span-6 border rounded px-2 py-2"
        name="items[0][description]"
        placeholder="Item description"
        required
      />

      <input
        class="col-span-2 border rounded px-2 py-2 qty"
        name="items[0][quantity]"
        type="number"
        step="0.01"
        min="0"
        placeholder="0"
        value="1"
        required
      />

      <input
        class="col-span-2 border rounded px-2 py-2 price"
        name="items[0][unit_price]"
        type="number"
        step="0.01"
        min="0"
        placeholder="0.00"
        value="0"
        required
      />

      <input
        class="col-span-2 border rounded px-2 py-2 amount bg-gray-50"
        name="items[0][amount]"
        type="number"
        step="0.01"
        readonly
        placeholder="0.00"
        value="0"
      />
    </div>
  </div>
</div>


    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
      <div></div><div></div>
      <div class="bg-gray-50 rounded p-4">
        <div class="flex justify-between mb-2">
          <span class="text-sm text-gray-700">Subtotal</span>
          <span id="subtotal" class="font-medium">0.00</span>
        </div>
        <div class="flex justify-between mb-2">
          <label class="text-sm text-gray-700">Tax (%)</label>
          <input id="taxRate" name="tax_rate" type="number" step="0.01" min="0" value="0" class="w-24 border rounded px-2 py-1 text-right" />
        </div>
        <div class="flex justify-between mb-2">
          <span class="text-sm text-gray-700">Tax Amount</span>
          <span id="taxAmount" class="font-medium">0.00</span>
        </div>
        <div class="flex justify-between">
          <span class="text-sm text-gray-700">Total</span>
          <span id="total" class="font-semibold">0.00</span>
        </div>
      </div>
    </div>

    <div class="flex gap-3">
      <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Save</button>
      <a href="/billing-web/index.php" class="px-4 py-2 bg-gray-100 rounded hover:bg-gray-200">Cancel</a>
    </div>
  </form>
</div>

<script>
(function(){
  const itemsEl = document.getElementById('items');
  const addBtn = document.getElementById('addItem');
  let idx = 1;

  function recalc() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
      const qty = parseFloat(row.querySelector('.qty').value || '0');
      const price = parseFloat(row.querySelector('.price').value || '0');
      const amount = qty * price;
      row.querySelector('.amount').value = amount.toFixed(2);
      subtotal += amount;
    });
    const taxRate = parseFloat(document.getElementById('taxRate').value || '0');
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount;
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = taxAmount.toFixed(2);
    document.getElementById('total').textContent = total.toFixed(2);
  }

  addBtn.addEventListener('click', () => {
    const row = document.createElement('div');
    row.className = 'grid grid-cols-12 gap-2 item-row';
    row.innerHTML = `
      <input class="col-span-6 border rounded px-2 py-2" name="items[${idx}][description]" placeholder="Description" required />
      <input class="col-span-2 border rounded px-2 py-2 qty" name="items[${idx}][quantity]" type="number" step="0.01" min="0" placeholder="Qty" value="1" required />
      <input class="col-span-2 border rounded px-2 py-2 price" name="items[${idx}][unit_price]" type="number" step="0.01" min="0" placeholder="Unit price" value="0" required />
      <div class="col-span-2 flex gap-2">
        <input class="flex-1 border rounded px-2 py-2 amount bg-gray-50" name="items[${idx}][amount]" type="number" step="0.01" readonly placeholder="Amount" value="0" />
        <button type="button" class="px-2 bg-red-100 text-red-700 rounded remove">✕</button>
      </div>
    `;
    itemsEl.appendChild(row);
    idx++;
    recalc();
  });

  itemsEl.addEventListener('input', (e) => {
    if (e.target.classList.contains('qty') || e.target.classList.contains('price') || e.target.id === 'taxRate') {
      recalc();
    }
  });

  itemsEl.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove')) {
      e.target.closest('.item-row').remove();
      recalc();
    }
  });

  document.getElementById('taxRate').addEventListener('input', recalc);
  recalc();
})();
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>