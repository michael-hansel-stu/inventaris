<<<<<<< HEAD
<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/lib/functions.php';
require_once __DIR__ . '/../src/models/ProductModel.php';
require_once __DIR__ . '/../src/controllers/ProductController.php';
require_once __DIR__ . '/../src/models/SaleModel.php';
require_once __DIR__ . '/../src/controllers/SaleController.php';

$page_title = "Manajemen Penjualan";

// Inisialisasi koneksi dan model/controller
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Koneksi database tidak valid. Periksa file src/config/database.php.");
}
$productModel = new ProductModel($conn);
$productController = new ProductController($productModel);
$saleModel = new SaleModel($conn, $productModel);
$saleController = new SaleController($saleModel, $productModel);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle Aksi POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_result = null;
    $redirect_url = 'sales.php'; // Default redirect

    if ($_POST['action'] == 'add_sale') {
        $action_result = $saleController->handleAddSale($_POST);
    } elseif ($_POST['action'] == 'delete_sale_from_detail' || $_POST['action'] == 'delete_sale_from_list') {
        if (isset($_POST['sale_id_to_delete'])) {
            $saleIdToDelete = filter_var($_POST['sale_id_to_delete'], FILTER_VALIDATE_INT);
            if ($saleIdToDelete) {
                $action_result = $saleController->handleDeleteSale($saleIdToDelete);
            } else {
                $action_result = ['error' => true, 'message' => "ID Penjualan untuk dihapus tidak valid."];
            }
        } else {
            $action_result = ['error' => true, 'message' => "ID Penjualan untuk dihapus tidak disertakan."];
        }
    } 
    // Penanganan untuk update_sale (jika form edit_sale.php mengirim ke sini)
    // Jika edit_sale.php memproses POST-nya sendiri, blok ini tidak diperlukan di sales.php
    elseif ($_POST['action'] == 'update_sale') { 
        if (isset($_POST['sale_id_to_edit'])) {
            $saleIdToEdit = filter_var($_POST['sale_id_to_edit'], FILTER_VALIDATE_INT);
            if ($saleIdToEdit) {
                $action_result = $saleController->handleUpdateSale($saleIdToEdit, $_POST);
                $redirect_url = 'sales.php?view_sale_id=' . $saleIdToEdit; // Kembali ke detail setelah update
            } else {
                $action_result = ['error' => true, 'message' => "ID Penjualan untuk diupdate tidak valid."];
            }
        } else {
            $action_result = ['error' => true, 'message' => "ID Penjualan untuk diupdate tidak disertakan."];
        }
    }

    if ($action_result) {
        if (isset($action_result['success']) && $action_result['success']) {
            $_SESSION['message'] = $action_result['message'];
        } elseif (isset($action_result['error']) && $action_result['error']) {
            $_SESSION['error_message'] = $action_result['message'];
        } elseif (isset($action_result['info'])) { 
             $_SESSION['message'] = $action_result['message']; 
        }
    }
    redirect($redirect_url); 
}

// Ambil data untuk tampilan utama
$sales_list = $saleController->getAllSalesForView();
$products_for_sale_form = $productController->getAllProductsForView();

// Logika untuk menangani view_sale_id dan memuat data detail
$sale_details_to_view = null;
$open_detail_modal_script = '';

if (isset($_GET['view_sale_id'])) {
    $sale_id_to_view = filter_var($_GET['view_sale_id'], FILTER_VALIDATE_INT);
    if ($sale_id_to_view) {
        $sale_details_to_view = $saleController->getSaleDetailsForView($sale_id_to_view);
        if ($sale_details_to_view && isset($sale_details_to_view['sale'])) {
            $open_detail_modal_script = "<script>document.addEventListener('DOMContentLoaded', function() { openModal('viewSaleDetailModal'); });</script>";
        } else {
            if (!isset($_SESSION['error_message']) && !isset($_SESSION['message'])) {
                 $_SESSION['error_message'] = "Detail penjualan dengan ID " . htmlspecialchars($_GET['view_sale_id']) . " tidak ditemukan atau tidak valid.";
            }
            if (!headers_sent()) { 
                 echo "<script>
                    if (window.history.replaceState) { 
                        const currentUrl = new URL(window.location.href);
                        currentUrl.searchParams.delete('view_sale_id');
                        window.history.replaceState({path: currentUrl.href}, '', currentUrl.href);
                    }
                </script>";
            }
        }
    } else {
        if (!isset($_SESSION['error_message']) && !isset($_SESSION['message'])) {
            $_SESSION['error_message'] = "ID penjualan untuk detail tidak valid.";
        }
    }
}

include_once __DIR__ . '/../src/template/header.php'; 
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
    <p class="text-gray-600">Kelola transaksi penjualan Anda di sini.</p>
</div>

<div class="mb-6">
    <button onclick="openModal('addSaleModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Tambah Penjualan Baru
    </button>
</div>

<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-slate-700 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">ID Penjualan</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Tanggal</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Total Item</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Grand Total</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (!empty($sales_list)): ?>
                    <?php foreach ($sales_list as $sale): ?>
                        <tr class="border-b border-gray-200 hover:bg-slate-50 transition duration-100">
                            <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($sale['sales_id']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                <?php
                                if (!empty($sale['sale_date']) && $sale['sale_date'] !== '0000-00-00 00:00:00' && $sale['sale_date'] !== null) {
                                    $timestamp = strtotime($sale['sale_date']);
                                    if ($timestamp && $timestamp > 0 && date('Y', $timestamp) > 1970) { 
                                        echo sanitizeInput(date("d F Y", $timestamp)); // HANYA TANGGAL
                                    } else {
                                        echo 'Tidak ada tanggal'; 
                                    }
                                } else {
                                    echo 'Tidak ada tanggal'; 
                                }
                                ?>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($sale['total_quantity_items'] ?? 0); ?> item</td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatRupiah($sale['grand_total'] ?? 0); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                <a href="sales.php?view_sale_id=<?php echo $sale['sales_id']; ?>" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline font-semibold mr-3">
                                   Detail
                                </a>
                                <form method="POST" action="sales.php" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penjualan <?php echo sanitizeInput($sale['sales_id']); ?>? Stok produk akan dikembalikan.');">
                                    <input type="hidden" name="action" value="delete_sale_from_list">
                                    <input type="hidden" name="sale_id_to_delete" value="<?php echo $sale['sales_id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 hover:underline font-semibold">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004.006 18h11.988a1 1 0 00.992-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 011-1h8a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                            Belum ada transaksi penjualan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="addSaleModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-add-sale">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-add-sale">Tambah Penjualan Baru</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('addSaleModal')" aria-label="Tutup modal">
                <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
            </button>
        </div>
        <form method="POST" action="sales.php" id="addSaleFormInternal">
            <input type="hidden" name="action" value="add_sale">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div>
                        <label for="add_sale_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penjualan <span class="text-red-500">*</span></label>
                        <input type="date" name="sale_date" id="add_sale_date" value="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                </div>

                <div class="border-t border-b border-gray-200 py-4 my-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Item Penjualan</h3>
                    <div id="saleItemsContainer" class="space-y-3">
                        <div class="sale-item-row flex items-end space-x-2 p-2 border rounded-md bg-gray-50">
                            <div class="flex-grow">
                                <label class="block text-xs font-medium text-gray-600">Produk <span class="text-red-500">*</span></label>
                                <select name="product_ids[]" class="product-select mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                                    <option value="">-- Pilih Produk --</option>
                                    <?php if (!empty($products_for_sale_form)): ?>
                                        <?php foreach ($products_for_sale_form as $product): ?>
                                            <option value="<?php echo $product['product_id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                                                <?php echo sanitizeInput($product['name']); ?> (Stok: <?php echo $product['quantity']; ?> <?php echo sanitizeInput($product['unit']); ?>) - <?php echo formatRupiah($product['price']);?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="w-1/4">
                                <label class="block text-xs font-medium text-gray-600">Kuantitas <span class="text-red-500">*</span></label>
                                <input type="number" name="quantities[]" min="1" class="quantity-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="Qty">
                            </div>
                            <div class="w-1/4">
                                <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                                <input type="text" class="subtotal-display mt-1 block w-full px-3 py-2 border-gray-200 bg-gray-100 rounded-md sm:text-sm" readonly placeholder="Rp 0">
                            </div>
                            <button type="button" onclick="removeSaleItemRow(this)" class="remove-item-btn text-red-500 hover:text-red-700 p-2 rounded-md bg-red-100 hover:bg-red-200 transition duration-150 self-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </div>
                    <button type="button" id="addSaleItemRowBtn" class="mt-3 bg-sky-500 hover:bg-sky-600 text-white font-semibold py-1.5 px-3 rounded-md text-sm shadow-sm">
                        + Tambah Item Lain
                    </button>
                </div>
                 <div class="text-right mt-4 border-t pt-4">
                    <p class="text-lg font-semibold text-gray-800">Total Keseluruhan: <span id="grandTotalDisplay" class="text-green-600">Rp 0</span></p>
                </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('addSaleModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-green-600 py-2 rounded-lg text-white hover:bg-green-700 font-medium transition duration-150">Simpan Penjualan</button>
            </div>
        </form>
    </div>
</div>

<div id="viewSaleDetailModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-xl shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-view-sale">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-view-sale">Detail Penjualan</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModalAndClearUrl('viewSaleDetailModal')" aria-label="Tutup modal">
                <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
            </button>
        </div>
        <div id="viewSaleDetailModalContent" class="space-y-3 text-sm">
            <?php if ($sale_details_to_view && isset($sale_details_to_view['sale'])): ?>
                <p><strong>ID Penjualan:</strong> <span id="detailSaleId"><?php echo sanitizeInput($sale_details_to_view['sale']['sales_id']); ?></span></p>
                <p><strong>Tanggal:</strong> 
                    <?php 
                    if (!empty($sale_details_to_view['sale']['date']) && $sale_details_to_view['sale']['date'] !== '0000-00-00 00:00:00' && $sale_details_to_view['sale']['date'] !== null) {
                        $detailTimestamp = strtotime($sale_details_to_view['sale']['date']);
                        if ($detailTimestamp && $detailTimestamp > 0 && date('Y', $detailTimestamp) > 1970) {
                            echo sanitizeInput(date("d F Y", $detailTimestamp)); // HANYA TANGGAL
                        } else {
                            echo 'Tidak ada tanggal';
                        }
                    } else {
                        echo 'Tidak ada tanggal';
                    }
                    ?>
                </p>
                
                <h4 class="text-md font-semibold pt-2 mt-2 border-t">Item Terjual:</h4>
                <?php if (!empty($sale_details_to_view['items'])): ?>
                    <ul class="list-disc list-inside space-y-1 pl-1">
                        <?php 
                            $calculated_grand_total_detail = 0;
                            $total_items_quantity_detail = 0;
                        ?>
                        <?php foreach ($sale_details_to_view['items'] as $item_detail): ?>
                            <li>
                                <?php echo sanitizeInput($item_detail['product_name']); ?>
                                (<?php echo sanitizeInput($item_detail['quantity']); ?> <?php echo sanitizeInput($item_detail['product_unit']); ?>
                                @ <?php echo formatRupiah($item_detail['unit_price']); ?>)
                                - Subtotal: <strong><?php echo formatRupiah($item_detail['total_price']); ?></strong>
                            </li>
                            <?php 
                                $calculated_grand_total_detail += $item_detail['total_price'];
                                $total_items_quantity_detail += $item_detail['quantity'];
                            ?>
                        <?php endforeach; ?>
                    </ul>
                    <div class="pt-3 mt-3 border-t text-right">
                        <p class="text-sm">Total Kuantitas Item: <strong><?php echo $total_items_quantity_detail; ?></strong></p>
                        <p class="text-md font-semibold">Grand Total: <strong class="text-green-700"><?php echo formatRupiah($calculated_grand_total_detail); ?></strong></p>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500">Tidak ada item detail untuk penjualan ini.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="text-gray-600">Memuat detail penjualan atau detail tidak ditemukan...</p>
            <?php endif; ?>
        </div>
         <div class="flex justify-between items-center pt-6 space-x-3 border-t mt-6">
            <div>
                <button type="button" onclick="handleEditSale(<?php echo $sale_details_to_view['sale']['sales_id'] ?? 0; ?>)" 
                        class="text-blue-600 hover:text-blue-800 hover:underline font-semibold <?php echo !($sale_details_to_view && isset($sale_details_to_view['sale'])) ? 'opacity-50 cursor-not-allowed !no-underline' : ''; ?>"
                        <?php echo !($sale_details_to_view && isset($sale_details_to_view['sale'])) ? 'disabled' : ''; ?>>
                    Edit
                </button>
                <button type="button" onclick="handleDeleteSale(<?php echo $sale_details_to_view['sale']['sales_id'] ?? 0; ?>)" 
                        class="text-red-600 hover:text-red-800 hover:underline font-semibold ml-3 <?php echo !($sale_details_to_view && isset($sale_details_to_view['sale'])) ? 'opacity-50 cursor-not-allowed !no-underline' : ''; ?>"
                        <?php echo !($sale_details_to_view && isset($sale_details_to_view['sale'])) ? 'disabled' : ''; ?>>
                    Hapus
                </button>
            </div>
            <button type="button" onclick="closeModalAndClearUrl('viewSaleDetailModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Tutup</button>
        </div>
    </div>
</div>

<?php
echo $open_detail_modal_script; 
include_once __DIR__ . '/../src/template/footer.php';
?>
<script>
    // --- JavaScript untuk Modal Umum ---
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                 const dialog = modal.querySelector('.relative');
                 if(dialog) {
                    dialog.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    dialog.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                 }
            });

            if (modalId === 'addSaleModal') {
                resetAddSaleForm();
                const firstInput = modal.querySelector('input[name="sale_date"]');
                if (firstInput) firstInput.focus();
            }
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const dialog = modal.querySelector('.relative');
            if(dialog) {
                dialog.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                dialog.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            }
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    }
    
    function closeModalAndClearUrl(modalId) {
        closeModal(modalId);
        if (history.pushState) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.delete('view_sale_id');
            window.history.replaceState({path: currentUrl.href}, '', currentUrl.href);
        }
    }

    // --- JavaScript khusus untuk Form Tambah Penjualan ---
    const saleItemsContainer = document.getElementById('saleItemsContainer');
    const addSaleItemRowBtn = document.getElementById('addSaleItemRowBtn');
    let productOptionsHtml = ''; 

    function populateProductOptions() {
        if (!saleItemsContainer) return; 
        const firstSelect = saleItemsContainer.querySelector('.product-select');
        if (firstSelect && firstSelect.options.length > 1) {
            productOptionsHtml = Array.from(firstSelect.options)
                                    .map(opt => `<option value="${opt.value}" data-price="${opt.dataset.price || 0}" data-stock="${opt.dataset.stock || 0}">${opt.text}</option>`)
                                    .join('');
            if(addSaleItemRowBtn) addSaleItemRowBtn.disabled = false;
        } else {
            productOptionsHtml = '<option value="">-- Tidak ada produk tersedia --</option>';
            if(addSaleItemRowBtn) addSaleItemRowBtn.disabled = true;
        }
    }
    
    function createSaleItemRow() {
        if (!productOptionsHtml || productOptionsHtml.includes("-- Tidak ada produk tersedia --")) return;
        if (!saleItemsContainer) return;

        const newRow = document.createElement('div');
        newRow.className = 'sale-item-row flex items-end space-x-2 p-2 border rounded-md bg-gray-50';
        newRow.innerHTML = `
            <div class="flex-grow">
                <label class="block text-xs font-medium text-gray-600">Produk <span class="text-red-500">*</span></label>
                <select name="product_ids[]" class="product-select mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    ${productOptionsHtml}
                </select>
            </div>
            <div class="w-1/4">
                <label class="block text-xs font-medium text-gray-600">Kuantitas <span class="text-red-500">*</span></label>
                <input type="number" name="quantities[]" min="1" class="quantity-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required placeholder="Qty">
            </div>
             <div class="w-1/4">
                <label class="block text-xs font-medium text-gray-600">Subtotal</label>
                <input type="text" class="subtotal-display mt-1 block w-full px-3 py-2 border-gray-200 bg-gray-100 rounded-md sm:text-sm" readonly placeholder="Rp 0">
            </div>
            <button type="button" onclick="removeSaleItemRow(this)" class="remove-item-btn text-red-500 hover:text-red-700 p-2 rounded-md bg-red-100 hover:bg-red-200 transition duration-150 self-center mt-1">
                 <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
            </button>
        `;
        saleItemsContainer.appendChild(newRow);
        attachEventListenersToRow(newRow);
        updateGrandTotal();
    }

    function removeSaleItemRow(button) {
        const row = button.closest('.sale-item-row');
        if (row && saleItemsContainer) { 
            if (saleItemsContainer.querySelectorAll('.sale-item-row').length > 1) {
                row.remove();
            } else {
                const productSelect = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                if(productSelect) productSelect.value = "";
                if(quantityInput) quantityInput.value = "";
                updateSubtotal(row);
            }
            updateGrandTotal();
        }
    }

    function updateSubtotal(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const subtotalDisplay = row.querySelector('.subtotal-display');

        if (!productSelect || !quantityInput || !subtotalDisplay) return;

        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const price = parseFloat(selectedOption?.dataset.price) || 0; 
        let quantity = parseInt(quantityInput.value) || 0;
        const stock = parseInt(selectedOption?.dataset.stock) || 0;
        
        if (quantity > stock && selectedOption && selectedOption.value !== "") {
            alert(`Stok untuk ${selectedOption.text.split(' (Stok:')[0]} tidak mencukupi. Stok tersedia: ${stock}, diminta: ${quantity}. Kuantitas diatur ke ${stock}.`);
            quantity = stock;
            quantityInput.value = stock;
        }
        if (quantity < 0) { 
            quantity = 0;
            quantityInput.value = 0;
        }

        const subtotal = price * quantity;
        subtotalDisplay.value = `Rp ${subtotal.toLocaleString('id-ID')}`;
        updateGrandTotal();
    }
    
    function updateGrandTotal() {
        let grandTotal = 0;
        if (!saleItemsContainer) {
            if(document.getElementById('grandTotalDisplay')) document.getElementById('grandTotalDisplay').textContent = `Rp 0`;
            return;
        }

        saleItemsContainer.querySelectorAll('.sale-item-row').forEach(row => {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            if (productSelect && quantityInput) {
                const price = parseFloat(productSelect.options[productSelect.selectedIndex]?.dataset.price) || 0;
                const quantity = parseInt(quantityInput.value) || 0;
                if (quantity > 0) {
                     grandTotal += price * quantity;
                }
            }
        });
        if(document.getElementById('grandTotalDisplay')) {
            document.getElementById('grandTotalDisplay').textContent = `Rp ${grandTotal.toLocaleString('id-ID')}`;
        }
    }

    function attachEventListenersToRow(row) {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        if (productSelect) productSelect.addEventListener('change', () => updateSubtotal(row));
        if (quantityInput) quantityInput.addEventListener('input', () => updateSubtotal(row));
        updateSubtotal(row);
    }

    function resetAddSaleForm() {
        const form = document.getElementById('addSaleFormInternal');
        if (form) form.reset();

        if (saleItemsContainer) { 
            const rows = saleItemsContainer.querySelectorAll('.sale-item-row');
            rows.forEach((row, index) => {
                if (index === 0) {
                    const productSelect = row.querySelector('.product-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    if (productSelect) productSelect.value = "";
                    if (quantityInput) quantityInput.value = "";
                    updateSubtotal(row);
                } else { 
                    row.remove();
                }
            });
        }
        updateGrandTotal();
    }

    // --- Fungsi untuk Handle Aksi Edit dan Hapus di Modal Detail ---
    function handleEditSale(saleId) {
        if (saleId === 0 || saleId === null || typeof saleId === 'undefined') {
            alert("ID Penjualan tidak valid untuk diedit.");
            return;
        }
        // Mengarahkan ke halaman edit_sale.php (Anda perlu membuat file ini)
        window.location.href = `edit_sale.php?sale_id=${saleId}`; 
    }

    function handleDeleteSale(saleId) {
        if (saleId === 0 || saleId === null || typeof saleId === 'undefined') {
            alert("ID Penjualan tidak valid untuk dihapus.");
            return;
        }

        if (confirm(`Apakah Anda yakin ingin menghapus penjualan dengan ID: ${saleId}? Stok produk akan dikembalikan.`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'sales.php'; 
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'delete_sale_from_detail'; 
            form.appendChild(actionInput);

            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'sale_id_to_delete';
            idInput.value = saleId;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // --- FUNGSI UNTUK FLASH MESSAGE ---
    function closeFlashMessage(elementId) {
        const flashMessage = document.getElementById(elementId);
        if (flashMessage) {
            flashMessage.style.transition = 'opacity 0.2s ease-out';
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.style.display = 'none';
            }, 500); 
        }
    }
document.addEventListener('DOMContentLoaded', function() {
    const successMessageId = 'flashMessageSuccess';
    const errorMessageId = 'flashMessageError';

    const successFlash = document.getElementById(successMessageId);
    const errorFlash = document.getElementById(errorMessageId);

    if (successFlash) {
        setTimeout(() => {
            closeFlashMessage(successMessageId);
        }, 1000); 
    }

    if (errorFlash) {
        setTimeout(() => {
            closeFlashMessage(errorMessageId);
        }, 1000); 
    }
});

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('saleItemsContainer')) {
            populateProductOptions(); 
        
            if (addSaleItemRowBtn) { 
                addSaleItemRowBtn.addEventListener('click', createSaleItemRow);
            }

            const firstRow = saleItemsContainer.querySelector('.sale-item-row');
            if (firstRow) {
                attachEventListenersToRow(firstRow);
            }
        }
    });

    // Event listener global untuk modal
    window.addEventListener('click', function(event) {
        ['addSaleModal', 'viewSaleDetailModal'].forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal && event.target == modal) {
                if (modalId === 'viewSaleDetailModal') closeModalAndClearUrl(modalId);
                else closeModal(modalId);
            }
        });
    });

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            ['addSaleModal', 'viewSaleDetailModal'].forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && modal.style.display === 'flex') {
                    if (modalId === 'viewSaleDetailModal') closeModalAndClearUrl(modalId);
                    else closeModal(modalId);
                }
            });
        }
    });
</script>
=======

>>>>>>> bb497abaa63a7e8a61c34a8dd9a667eb0851ff74

