<?php
// public/sale_details.php

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/lib/functions.php';
require_once __DIR__ . '/../src/models/ProductModel.php';  // For product list and controller
require_once __DIR__ . '/../src/models/SaleItemModel.php';
require_once __DIR__ . '/../src/controllers/SaleItemController.php';
// You would also have a SaleModel and SaleController if this page manages sale details too
// require_once __DIR__ . '/../src/models/SaleModel.php';
// require_once __DIR__ . '/../src/controllers/SaleController.php';


// 1. Get sales_id from GET parameter - CRITICAL for this page
$sales_id = filter_input(INPUT_GET, 'sales_id', FILTER_VALIDATE_INT);

if (!$sales_id) {
    // If no sales_id, redirect or show error.
    // For now, let's assume we are viewing items for an existing sale.
    // In a real app, you might redirect to a sales listing page.
    $_SESSION['error_message'] = "ID Penjualan tidak valid atau tidak ditemukan.";
    redirect('sales.php'); // Redirect to a page listing all sales
    exit;
}

$page_title = "Detail Penjualan #{$sales_id} - Item";

// 2. Inisialisasi Model dan Controller
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Koneksi database tidak valid.");
}
$productModel = new ProductModel($conn);
$saleItemModel = new SaleItemModel($conn);
$saleItemController = new SaleItemController($saleItemModel, $productModel);
// $saleModel = new SaleModel($conn);
// $saleInfo = $saleModel->getSaleById($sales_id); // Fetch main sale info
// if (!$saleInfo) {
//     $_SESSION['error_message'] = "Data Penjualan #{$sales_id} tidak ditemukan.";
//     redirect('sales.php');
//     exit;
// }


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 3. Handle POST actions for Sale Items
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_result = null;

    // Ensure sales_id from form matches the one in URL for security/consistency
    $form_sales_id = null;
    if (isset($_POST['sales_id'])) $form_sales_id = filter_var($_POST['sales_id'], FILTER_VALIDATE_INT);
    
    // For add actions, we need to ensure the sales_id is correctly passed.
    // For edit/delete, the primary key is sale_item_id.

    if ($_POST['action'] == 'add_sale_item') {
        if ($form_sales_id && $form_sales_id == $sales_id) {
            $action_result = $saleItemController->handleAddSaleItem($_POST);
        } else {
            $action_result = ['error' => true, 'message' => 'ID Penjualan tidak cocok. Penambahan item dibatalkan.'];
        }
    } elseif ($_POST['action'] == 'edit_sale_item') {
        // Edit doesn't strictly need sales_id from form if sale_item_id is unique and sufficient
        $action_result = $saleItemController->handleEditSaleItem($_POST);
    } elseif ($_POST['action'] == 'delete_sale_item') {
        $action_result = $saleItemController->handleDeleteSaleItem($_POST);
    }

    if ($action_result) {
        $_SESSION[($action_result['error'] ?? false) ? 'error_message' : 'message'] = $action_result['message'];
    }
    redirect("sale_details.php?sales_id=" . $sales_id); // Redirect back to the same sale detail page
}

// 4. Ambil data untuk tampilan
$sale_items_list = $saleItemController->getSaleItemsForView($sales_id);
$products_for_dropdown = $productModel->getAllProducts(); // Fetch all products for the add item form

// 5. Render Tampilan
include_once __DIR__ . '/../src/template/header.php';
?>

<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
        <a href="sales.php" class="text-blue-600 hover:text-blue-800 hover:underline">&laquo; Kembali ke Daftar Penjualan</a>
    </div>
    </p>
</div>

<div class="mb-6">
    <button onclick="openModal('addSaleItemModal')" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
        Tambah Item ke Penjualan
    </button>
</div>

<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-slate-700 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">ID Item</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Nama Produk</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-right text-xs font-semibold uppercase tracking-wider">Qty</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-right text-xs font-semibold uppercase tracking-wider">Harga Satuan (Saat Jual)</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-right text-xs font-semibold uppercase tracking-wider">Total Harga</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (!empty($sale_items_list)): ?>
                    <?php foreach ($sale_items_list as $item): ?>
                        <tr class="border-b border-gray-200 hover:bg-slate-50">
                            <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo $item['sale_item_id']; ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?php echo sanitizeInput($item['product_name']); ?>
                                <span class="text-xs text-gray-500">(ID: <?php echo $item['product_id']; ?>)</span>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right"><?php echo $item['quantity']; ?> <?php echo sanitizeInput($item['product_unit']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right"><?php echo formatRupiah($item['unit_price_at_sale']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right font-semibold"><?php echo formatRupiah($item['total_price']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                <button onclick="openEditSaleItemModal(
                                    '<?php echo $item['sale_item_id']; ?>',
                                    '<?php echo $item['product_id']; ?>',
                                    '<?php echo $item['quantity']; ?>'
                                )" class="text-indigo-600 hover:text-indigo-900 mr-3 font-semibold hover:underline">Edit</button>
                                <form method="POST" action="sale_details.php?sales_id=<?php echo $sales_id; ?>" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus item ini dari penjualan?');">
                                    <input type="hidden" name="action" value="delete_sale_item">
                                    <input type="hidden" name="sale_item_id_delete" value="<?php echo $item['sale_item_id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-gray-500">
                            Belum ada item untuk penjualan ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
             <tfoot>
                <?php
                $grand_total = 0;
                foreach ($sale_items_list as $item) {
                    $grand_total += $item['total_price'];
                }
                ?>
                <tr class="bg-slate-100 font-semibold">
                    <td colspan="4" class="px-5 py-3 text-right text-sm uppercase">Grand Total:</td>
                    <td class="px-5 py-3 text-right text-sm"><?php echo formatRupiah($grand_total); ?></td>
                    <td class="px-5 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div id="addSaleItemModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white transform transition-all">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800">Tambah Item ke Penjualan #<?php echo $sales_id; ?></p>
            <button type="button" class="cursor-pointer z-50 p-2" onclick="closeModal('addSaleItemModal')" aria-label="Tutup">✕</button>
        </div>
        <form method="POST" action="sale_details.php?sales_id=<?php echo $sales_id; ?>">
            <input type="hidden" name="action" value="add_sale_item">
            <input type="hidden" name="sales_id" value="<?php echo $sales_id; ?>">
            <div class="space-y-4">
                <div>
                    <label for="add_product_id" class="block text-sm font-medium text-gray-700 mb-1">Produk <span class="text-red-500">*</span></label>
                    <select name="product_id" id="add_product_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products_for_dropdown as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                                <?php echo sanitizeInput($product['name']); ?> (Stok: <?php echo $product['quantity']; ?>) - <?php echo formatRupiah($product['price']);?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="add_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" id="add_quantity" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <small id="add_stock_info" class="text-xs text-gray-500"></small>
                </div>
                </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('addSaleItemModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 bg-green-600 py-2 rounded-lg text-white hover:bg-green-700">Simpan Item</button>
            </div>
        </form>
    </div>
</div>

<div id="editSaleItemModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-md shadow-lg rounded-md bg-white transform transition-all">
         <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800">Edit Item Penjualan</p>
            <button type="button" class="cursor-pointer z-50 p-2" onclick="closeModal('editSaleItemModal')" aria-label="Tutup">✕</button>
        </div>
        <form method="POST" action="sale_details.php?sales_id=<?php echo $sales_id; ?>">
            <input type="hidden" name="action" value="edit_sale_item">
            <input type="hidden" name="sale_item_id_edit" id="edit_sale_item_id">
            <div class="space-y-4">
                <div>
                    <label for="edit_product_id" class="block text-sm font-medium text-gray-700 mb-1">Produk <span class="text-red-500">*</span></label>
                    <select name="product_id_edit" id="edit_product_id" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="">-- Pilih Produk --</option>
                         <?php foreach ($products_for_dropdown as $product): ?>
                            <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                                <?php echo sanitizeInput($product['name']); ?> (Stok: <?php echo $product['quantity']; ?>) - <?php echo formatRupiah($product['price']);?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="edit_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity_edit" id="edit_quantity" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    <small id="edit_stock_info" class="text-xs text-gray-500"></small>
                </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('editSaleItemModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300">Batal</button>
                <button type="submit" class="px-4 bg-blue-600 py-2 rounded-lg text-white hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>


<?php
include_once __DIR__ . '/../src/template/footer.php';
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
<script>
    // Basic modal functions (reuse or adapt from your products.php)
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            // Add animation classes if you have them set up
            const firstInput = modal.querySelector('select, input:not([type=hidden])');
            if (firstInput) firstInput.focus();

            if (modalId === 'addSaleItemModal') {
                document.getElementById('add_product_id').value = '';
                document.getElementById('add_quantity').value = '1';
                document.getElementById('add_stock_info').textContent = '';
            }
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.style.display = 'none';
    }

    // Populate edit modal for Sale Item
    function openEditSaleItemModal(saleItemId, productId, quantity) {
        document.getElementById('edit_sale_item_id').value = saleItemId;
        const productDropdown = document.getElementById('edit_product_id');
        productDropdown.value = productId;
        document.getElementById('edit_quantity').value = quantity;
        
        // Trigger change to update stock info for edit modal
        const event = new Event('change');
        productDropdown.dispatchEvent(event);

        openModal('editSaleItemModal');
    }

    // Close modals with ESC key
    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            if (document.getElementById('addSaleItemModal').style.display === 'flex') {
                closeModal('addSaleItemModal');
            }
            if (document.getElementById('editSaleItemModal').style.display === 'flex') {
                closeModal('editSaleItemModal');
            }
        }
    });

    // Show stock info when product is selected
    function updateStockInfo(productIdDropdownId, quantityInputId, stockInfoId) {
        const productDropdown = document.getElementById(productIdDropdownId);
        const stockInfo = document.getElementById(stockInfoId);
        const quantityInput = document.getElementById(quantityInputId);

        if (productDropdown.value) {
            const selectedOption = productDropdown.options[productDropdown.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            stockInfo.textContent = `Stok tersedia: ${stock} ${selectedOption.text.includes('kg') ? 'kg' : 'pcs/unit'}`; // Basic unit detection
            quantityInput.max = stock; // Prevent ordering more than stock
        } else {
            stockInfo.textContent = '';
            quantityInput.max = null;
        }
    }

    document.getElementById('add_product_id').addEventListener('change', function() {
        updateStockInfo('add_product_id', 'add_quantity', 'add_stock_info');
    });
    document.getElementById('edit_product_id').addEventListener('change', function() {
        updateStockInfo('edit_product_id', 'edit_quantity', 'edit_stock_info');
    });


    // Flash message auto-close (reuse from your products.php)
    function closeFlashMessage(elementId) {
        const flashMessage = document.getElementById(elementId);
        if (flashMessage) {
            flashMessage.style.opacity = '0';
            setTimeout(() => { flashMessage.style.display = 'none'; }, 300);
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const successFlash = document.getElementById('flashMessageSuccess');
        const errorFlash = document.getElementById('flashMessageError');
        if (successFlash) setTimeout(() => closeFlashMessage('flashMessageSuccess'), 3000);
        if (errorFlash) setTimeout(() => closeFlashMessage('flashMessageError'), 5000); // Errors longer
    });

</script>
