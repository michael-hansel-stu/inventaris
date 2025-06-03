<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/lib/functions.php';
require_once __DIR__ . '/../src/models/ProductModel.php'; 
require_once __DIR__ . '/../src/controllers/ProductController.php';
require_once __DIR__ . '/../src/models/SaleModel.php';
require_once __DIR__ . '/../src/controllers/SaleController.php';

$page_title = "Edit Penjualan";

// Inisialisasi koneksi dan model/controller
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Koneksi database tidak valid.");
}
$productModel = new ProductModel($conn);
$productController = new ProductController($productModel);
$saleModel = new SaleModel($conn, $productModel);
$saleController = new SaleController($saleModel, $productModel);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$sale_to_edit = null;
$sale_items_to_edit = [];
$products_for_form = $productController->getAllProductsForView(); 

if (!isset($_GET['sale_id'])) {
    $_SESSION['error_message'] = "ID Penjualan tidak disertakan untuk diedit.";
    redirect('sales.php');
}

$sale_id_from_url = filter_var($_GET['sale_id'], FILTER_VALIDATE_INT);
if (!$sale_id_from_url) {
    $_SESSION['error_message'] = "ID Penjualan tidak valid.";
    redirect('sales.php');
}

// Handle Aksi POST untuk update (diproses di halaman ini sendiri)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_sale') {
    if (isset($_POST['sale_id_to_edit'])) {
        $saleIdToUpdate = filter_var($_POST['sale_id_to_edit'], FILTER_VALIDATE_INT);
        if ($saleIdToUpdate == $sale_id_from_url) { // Pastikan ID cocok
            $action_result = $saleController->handleUpdateSale($saleIdToUpdate, $_POST);

            if ($action_result) {
                if (isset($action_result['success']) && $action_result['success']) {
                    $_SESSION['message'] = $action_result['message'];
                } elseif (isset($action_result['error']) && $action_result['error']) {
                    $_SESSION['error_message'] = $action_result['message'];
                }
            }
            redirect('sales.php?view_sale_id=' . $saleIdToUpdate); // Kembali ke detail setelah update
        } else {
             $_SESSION['error_message'] = "ID Penjualan untuk diupdate tidak cocok.";
             redirect('sales.php');
        }
    } else {
         $_SESSION['error_message'] = "ID Penjualan untuk diupdate tidak ditemukan dalam form.";
         redirect('sales.php');
    }
}


// Ambil data untuk form edit HANYA jika bukan POST request untuk update
if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['action']) || $_POST['action'] != 'update_sale') {
    $details = $saleController->getSaleDetailsForView($sale_id_from_url);
    if ($details && isset($details['sale'])) {
        $sale_to_edit = $details['sale'];
        $sale_items_to_edit = $details['items'];
    } else {
        $_SESSION['error_message'] = "Penjualan dengan ID " . htmlspecialchars($_GET['sale_id']) . " tidak ditemukan.";
        redirect('sales.php');
    }
}


include_once __DIR__ . '/../src/template/header.php';
?>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">
            <?php echo sanitizeInput($page_title); ?> 
            <?php if ($sale_to_edit): ?>
                <?php echo sanitizeInput($sale_to_edit['sales_id']); ?>
            <?php endif; ?>
        </h1>
        <a href="sales.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-md shadow-sm hover:shadow-md transition duration-150 ease-in-out">
            Kembali ke Daftar Penjualan
        </a>
    </div>

    <?php if ($sale_to_edit): ?>
    <form method="POST" action="edit_sale.php?sale_id=<?php echo sanitizeInput($sale_to_edit['sales_id']); ?>" id="editSaleForm">
        <input type="hidden" name="action" value="update_sale">
        <input type="hidden" name="sale_id_to_edit" value="<?php echo sanitizeInput($sale_to_edit['sales_id']); ?>">
        
        <div class="bg-white p-6 rounded-lg shadow-xl space-y-6">
            <div class="grid grid-cols-1 gap-6"> 
                <div>
                    <label for="edit_sale_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penjualan <span class="text-red-500">*</span></label>
                    <input type="date" name="sale_date" id="edit_sale_date" 
                           value="<?php echo sanitizeInput(date('Y-m-d', strtotime($sale_to_edit['date']))); ?>" 
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                </div>

            <div class="border-t border-b border-gray-200 py-4 my-4">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-lg font-semibold text-gray-800">Item Penjualan</h3>
                    <button type="button" id="editAddItemRowBtn" class="bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1.5 px-3 rounded-md text-sm shadow-sm">
                        + Tambah Item Baru
                    </button>
                </div>
                <div id="editSaleItemsContainer" class="space-y-3">
                    <?php if (!empty($sale_items_to_edit)): ?>
                        <?php foreach ($sale_items_to_edit as $index => $item): ?>
                            <div class="edit-sale-item-row flex items-end space-x-2 p-2 border rounded-md bg-gray-50">
                                <input type="hidden" name="item_sale_item_ids[]" value="<?php echo sanitizeInput($item['sale_item_id']); ?>">
                                <div class="flex-grow">
                                    <label class="block text-xs font-medium text-gray-600">Produk <span class="text-red-500">*</span></label>
                                    <select name="item_product_ids[]" class="product-select-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                        <option value="">-- Pilih Produk --</option>
                                        <?php foreach ($products_for_form as $product_option): ?>
                                            <?php
                                                $current_item_original_quantity = 0;
                                                if ($product_option['product_id'] == $item['product_id']) {
                                                    $current_item_original_quantity = $item['quantity'];
                                                }
                                                // Stok yang ditampilkan di dropdown adalah stok produk saat ini + kuantitas item ini yang sudah ada (jika produknya sama)
                                                // Ini membantu validasi di sisi klien agar tidak melebihi stok yang "tersedia" untuk diedit
                                                $display_stock = $product_option['quantity'] + $current_item_original_quantity;
                                            ?>
                                            <option value="<?php echo $product_option['product_id']; ?>" 
                                                    data-price="<?php echo $product_option['price']; ?>" 
                                                    data-available-stock="<?php echo $display_stock; ?>" data-original-db-stock="<?php echo $product_option['quantity']; ?>" <?php echo ($product_option['product_id'] == $item['product_id']) ? 'selected' : ''; ?>>
                                                <?php echo sanitizeInput($product_option['name']); ?> 
                                                (Tersedia: <?php echo $display_stock; ?>) 
                                                - <?php echo formatRupiah($product_option['price']);?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="w-1/4">
                                    <label class="block text-xs font-medium text-gray-600">Kuantitas <span class="text-red-500">*</span></label>
                                    <input type="number" name="item_quantities[]" min="1" value="<?php echo sanitizeInput($item['quantity']); ?>" class="quantity-input-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="Qty">
                                </div>
                                <div class="w-1/4">
                                    <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                                    <input type="text" class="subtotal-display-edit mt-1 block w-full px-3 py-2 border-gray-200 bg-gray-100 rounded-md sm:text-sm" readonly value="<?php echo formatRupiah($item['total_price']);?>">
                                </div>
                                <button type="button" onclick="removeEditSaleItemRow(this)" class="remove-item-btn-edit text-red-500 hover:text-red-700 p-2 rounded-md bg-red-100 hover:bg-red-200 transition duration-150 self-center mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <div class="edit-sale-item-row flex items-end space-x-2 p-2 border rounded-md bg-gray-50">
                            <input type="hidden" name="item_sale_item_ids[]" value="0"> 
                            <div class="flex-grow">
                                <label class="block text-xs font-medium text-gray-600">Produk <span class="text-red-500">*</span></label>
                                <select name="item_product_ids[]" class="product-select-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php foreach ($products_for_form as $product_option): ?>
                                        <option value="<?php echo $product_option['product_id']; ?>" 
                                                data-price="<?php echo $product_option['price']; ?>" 
                                                data-stock="<?php echo $product_option['quantity']; ?>"
                                                data-available-stock="<?php echo $product_option['quantity']; ?>"
                                                data-original-db-stock="<?php echo $product_option['quantity']; ?>">
                                            <?php echo sanitizeInput($product_option['name']); ?> 
                                            (Stok: <?php echo $product_option['quantity']; ?>) 
                                            - <?php echo formatRupiah($product_option['price']);?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="w-1/4">
                                <label class="block text-xs font-medium text-gray-600">Kuantitas <span class="text-red-500">*</span></label>
                                <input type="number" name="item_quantities[]" min="1" value="" class="quantity-input-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="Qty">
                            </div>
                            <div class="w-1/4">
                                <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                                <input type="text" class="subtotal-display-edit mt-1 block w-full px-3 py-2 border-gray-200 bg-gray-100 rounded-md sm:text-sm" readonly placeholder="Rp 0">
                            </div>
                            <button type="button" onclick="removeEditSaleItemRow(this)" class="remove-item-btn-edit text-red-500 hover:text-red-700 p-2 rounded-md bg-red-100 hover:bg-red-200 transition duration-150 self-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

             <div class="text-right mt-4 border-t pt-4">
                <p class="text-lg font-semibold text-gray-800">Total Keseluruhan: <span id="editGrandTotalDisplay" class="text-green-600">Rp 0</span></p>
            </div>

            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <a href="sales.php?view_sale_id=<?php echo sanitizeInput($sale_to_edit['sales_id']); ?>" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</a>
                <button type="submit" class="px-4 bg-yellow-500 py-2 rounded-lg text-white hover:bg-yellow-600 font-medium transition duration-150">Simpan Perubahan</button>
            </div>
        </div>
    </form>
    <?php else: ?>
        <div class="bg-white p-6 rounded-lg shadow-md">
            <p class="text-red-500">Data penjualan tidak dapat dimuat untuk diedit. Silakan kembali ke <a href="sales.php" class="text-blue-500 hover:underline">daftar penjualan</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php
include_once __DIR__ . '/../src/template/footer.php';
?>
<script>
    // --- JavaScript untuk Modal Umum --- (Salin dari sales.php jika diperlukan)
    // ...

    // --- JavaScript khusus untuk Form Edit Penjualan ---
    const editSaleItemsContainer = document.getElementById('editSaleItemsContainer');
    const editAddItemRowBtn = document.getElementById('editAddItemRowBtn');
    let editProductOptionsHtml = ''; 

    function populateEditProductOptions() {
        if (!editSaleItemsContainer) return;
        // Ambil template opsi produk dari PHP yang sudah ada (bisa dari baris pertama jika ada, atau dari PHP global var)
        const tempSelect = document.createElement('select');
        <?php if (!empty($products_for_form)): ?>
            tempSelect.innerHTML += `<option value="">-- Pilih Produk --</option>`;
            <?php foreach ($products_for_form as $product_option): ?>
                tempSelect.innerHTML += `<option value="<?php echo $product_option['product_id']; ?>" 
                                                data-price="<?php echo $product_option['price']; ?>" 
                                                data-stock="<?php echo $product_option['quantity']; ?>"
                                                data-original-db-stock="<?php echo $product_option['quantity']; ?>">
                                            <?php echo str_replace(["\r", "\n", "'", '"'], ["", "", "\\'", '\\"'], sanitizeInput($product_option['name'])); ?> 
                                            (Stok: <?php echo $product_option['quantity']; ?>) 
                                            - <?php echo formatRupiah($product_option['price']);?>
                                        </option>`;
            <?php endforeach; ?>
        <?php else: ?>
            tempSelect.innerHTML += `<option value="">-- Tidak ada produk --</option>`;
        <?php endif; ?>
        editProductOptionsHtml = tempSelect.innerHTML;
        
        if(editAddItemRowBtn) {
             editAddItemRowBtn.disabled = <?php echo empty($products_for_form) ? 'true' : 'false'; ?> || editProductOptionsHtml.includes("-- Tidak ada produk --");
        }
    }
    
    function createEditSaleItemRow() {
        if (!editProductOptionsHtml || editProductOptionsHtml.includes("-- Tidak ada produk --")) {
             alert("Tidak ada produk yang tersedia untuk ditambahkan.");
             return;
        }
        if (!editSaleItemsContainer) return;

        const newRow = document.createElement('div');
        newRow.className = 'edit-sale-item-row flex items-end space-x-2 p-2 border rounded-md bg-gray-50';
        newRow.innerHTML = `
            <input type="hidden" name="item_sale_item_ids[]" value="0"> 
            <div class="flex-grow">
                <label class="block text-xs font-medium text-gray-600">Produk <span class="text-red-500">*</span></label>
                <select name="item_product_ids[]" class="product-select-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    ${editProductOptionsHtml}
                </select>
            </div>
            <div class="w-1/4">
                <label class="block text-xs font-medium text-gray-600">Kuantitas <span class="text-red-500">*</span></label>
                <input type="number" name="item_quantities[]" min="1" class="quantity-input-edit mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="Qty" value="1">
            </div>
             <div class="w-1/4">
                <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                <input type="text" class="subtotal-display-edit mt-1 block w-full px-3 py-2 border-gray-200 bg-gray-100 rounded-md sm:text-sm" readonly placeholder="Rp 0">
            </div>
            <button type="button" onclick="removeEditSaleItemRow(this)" class="remove-item-btn-edit text-red-500 hover:text-red-700 p-2 rounded-md bg-red-100 hover:bg-red-200 transition duration-150 self-center mt-1">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            </button>
        `;
        editSaleItemsContainer.appendChild(newRow);
        attachEditEventListenersToRow(newRow);
        updateEditSubtotal(newRow); 
        updateEditGrandTotal();
    }

    function removeEditSaleItemRow(button) {
        const row = button.closest('.edit-sale-item-row');
        if (row && editSaleItemsContainer) {
            if (editSaleItemsContainer.querySelectorAll('.edit-sale-item-row').length > 1) {
                row.remove();
            } else { 
                const productSelect = row.querySelector('.product-select-edit');
                const quantityInput = row.querySelector('.quantity-input-edit');
                const saleItemIdInput = row.querySelector('input[name="item_sale_item_ids[]"]');
                if(productSelect) productSelect.value = "";
                if(quantityInput) quantityInput.value = "";
                if(saleItemIdInput) saleItemIdInput.value = "0"; 
                updateEditSubtotal(row); 
            }
            updateEditGrandTotal();
        }
    }

    function updateEditSubtotal(row) {
        const productSelect = row.querySelector('.product-select-edit');
        const quantityInput = row.querySelector('.quantity-input-edit');
        const subtotalDisplay = row.querySelector('.subtotal-display-edit');

        if (!productSelect || !quantityInput || !subtotalDisplay) return;

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = parseFloat(selectedOption?.dataset.price) || 0;
        let quantity = parseInt(quantityInput.value) || 0;
        
        const availableStock = parseInt(selectedOption?.dataset.availableStock) || 0; // Gunakan available-stock

        if (quantity > availableStock && selectedOption && selectedOption.value !== "") {
            alert(`Stok untuk ${selectedOption.text.split(' (Stok Tersedia:')[0]} tidak mencukupi. Stok tersedia: ${availableStock}, diminta: ${quantity}. Kuantitas diatur ke ${availableStock}.`);
            quantity = availableStock;
            quantityInput.value = availableStock;
        }

        if (quantity < 0) { 
            quantity = 0;
            quantityInput.value = 0;
        }

        const subtotal = price * quantity;
        subtotalDisplay.value = `Rp ${subtotal.toLocaleString('id-ID')}`;
        updateEditGrandTotal();
    }
    
    function updateEditGrandTotal() {
        let grandTotal = 0;
        if (!editSaleItemsContainer) {
            if(document.getElementById('editGrandTotalDisplay')) document.getElementById('editGrandTotalDisplay').textContent = `Rp 0`;
            return;
        }

        editSaleItemsContainer.querySelectorAll('.edit-sale-item-row').forEach(row => {
            const productSelect = row.querySelector('.product-select-edit');
            const quantityInput = row.querySelector('.quantity-input-edit');
            if (productSelect && quantityInput) {
                const price = parseFloat(productSelect.options[productSelect.selectedIndex]?.dataset.price) || 0;
                const quantity = parseInt(quantityInput.value) || 0;
                if (quantity > 0) {
                     grandTotal += price * quantity;
                }
            }
        });
        if(document.getElementById('editGrandTotalDisplay')) {
            document.getElementById('editGrandTotalDisplay').textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        }
    }

    function attachEditEventListenersToRow(row) {
        const productSelect = row.querySelector('.product-select-edit');
        const quantityInput = row.querySelector('.quantity-input-edit');
        if (productSelect) productSelect.addEventListener('change', () => updateEditSubtotal(row));
        if (quantityInput) quantityInput.addEventListener('input', () => updateEditSubtotal(row));
    }
    
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('editSaleItemsContainer')) { 
            populateEditProductOptions();
            
            const localEditAddItemRowBtn = document.getElementById('editAddItemRowBtn');
            if (localEditAddItemRowBtn) {
                localEditAddItemRowBtn.addEventListener('click', createEditSaleItemRow);
            }

            document.querySelectorAll('#editSaleItemsContainer .edit-sale-item-row').forEach(row => {
                attachEditEventListenersToRow(row);
                updateEditSubtotal(row); // Panggil untuk hitung subtotal awal
            });
            updateEditGrandTotal(); 
        }
    });
</script>
