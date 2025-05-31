// punya ripki gay

<?php
$page_title = "Pesanan Supplier"; // Akan digunakan oleh header.php
include_once __DIR__ . '/../src/template/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
    <p class="text-gray-600">Kelola pesanan supplier Anda di sini.</p>
</div>

<div class="mb-6">
    <button onclick="openModal('addSupplierOrderModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Tambah Pesanan Supplier
    </button>
</div>

<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-slate-700 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">ID Pesanan</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Nama Supplier</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Produk</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Jumlah</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Tanggal Pesanan</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <!-- Contoh data statis untuk UI -->
                <tr class="border-b border-gray-200 hover:bg-slate-50 transition duration-100">
                    <td class="px-5 py-4 whitespace-nowrap text-sm">1</td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900">PT. Supplier A</td>
                    <td class="px-5 py-4 text-sm">Beras Premium</td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm">100</td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm">2025-05-30</td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                        <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                    </td>
                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                        <button onclick="openEditModal('1', 'PT. Supplier A', 'Beras Premium', '100', '2025-05-30', 'Selesai')" class="text-indigo-600 hover:text-indigo-900 mr-3 font-semibold hover:underline">Edit</button>
                        <form method="POST" action="supplier_orders.php" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesanan ini? Tindakan ini tidak dapat dibatalkan.');">
                            <input type="hidden" name="action" value="delete_order">
                            <input type="hidden" name="order_id_delete" value="1">
                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
                <!-- Jika tidak ada data -->
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9 9a.75.75 0 000 1.5h.01a.75.75 0 000-1.5H9zM15 9a.75.75 0 000 1.5h.01a.75.75 0 000-1.5H15z" />
                        </svg>
                        Belum ada pesanan supplier. Silakan tambahkan pesanan baru.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah Pesanan Supplier -->
<div id="addSupplierOrderModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-add">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-add">Tambah Pesanan Supplier</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('addSupplierOrderModal')" aria-label="Tutup modal">
                <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
            </button>
        </div>
        <form method="POST" action="supplier_orders.php" id="addSupplierOrderFormInternal">
            <input type="hidden" name="action" value="add_supplier_order">
            <div class="space-y-4">
                <div>
                    <label for="add_supplier_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="supplier_name" id="add_supplier_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="add_product" class="block text-sm font-medium text-gray-700 mb-1">Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="product" id="add_product" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="add_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" id="add_quantity" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="add_order_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesanan <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date" id="add_order_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="add_status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" id Experts, I need help with the UI for the "Pesanan Supplier" section of my inventory app. Below is the PHP code for the "Manajemen Produk" (Product Management) section, which I’d like to use as a reference for styling and structure. The "Pesanan Supplier" section should have a similar look and feel, including a table to display supplier orders, a button to add new orders, and a modal for adding new orders. For now, I only need the UI (HTML, CSS, and JavaScript for the frontend), not the backend logic. The table should include columns for Order ID, Supplier Name, Product, Quantity, Order Date, Status, and Actions (Edit/Delete). Please provide the code for this section.

Here’s the existing code for "Manajemen Produk":

```php
<?php
require_once __DIR__ . '/../src/config/database.php';       // Untuk $conn
require_once __DIR__ . '/../src/lib/functions.php';        // Untuk sanitizeInput, formatRupiah, redirect, dll.
require_once __DIR__ . '/../src/models/ProductModel.php';  // Model
require_once __DIR__ . '/../src/controllers/ProductController.php'; // Controller

$page_title = "Manajemen Produk"; // Akan digunakan oleh header.php

// 2. Inisialisasi Model dan Controller
// $conn didapat dari require_once database.php
if (!isset($conn) || !$conn instanceof mysqli) {
    // Penanganan error jika koneksi database tidak tersedia
    // Ini seharusnya tidak terjadi jika database.php sudah benar
    die("Koneksi database tidak valid. Periksa file konfigurasi database.");
}
$productModel = new ProductModel($conn);
$productController = new ProductController($productModel);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_result = null; 

    if ($_POST['action'] == 'add_product') {
        $action_result = $productController->handleAddProduct($_POST);
    } elseif ($_POST['action'] == 'edit_product') {
        $action_result = $productController->handleEditProduct($_POST);
    } elseif ($_POST['action'] == 'delete_product') {
        $action_result = $productController->handleDeleteProduct($_POST);
    }

    // Simpan pesan ke session untuk ditampilkan setelah redirect (PRG Pattern)
    if ($action_result) {
        if (isset($action_result['success']) && $action_result['success']) {
            $_SESSION['message'] = $action_result['message'];
        } elseif (isset($action_result['error']) && $action_result['error']) {
            // Jika ada error spesifik dari controller, gunakan itu
            $_SESSION['error_message'] = $action_result['message'];
        } else {
            // Fallback jika struktur action_result tidak sesuai
            $_SESSION['error_message'] = "Terjadi kesalahan yang tidak diketahui.";
        }
    }
    
    redirect('products.php'); // Redirect kembali ke halaman products.php untuk menghindari resubmission
}

// 4. Ambil data produk untuk ditampilkan (setelah potensi redirect)
$products_list = $productController->getAllProductsForView();

// 5. Render Tampilan
include_once __DIR__ . '/../src/template/header.php';
// header.php akan memanggil displayFlashMessages() yang mengambil dari $_SESSION
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
    <p class="text-gray-600">Kelola daftar produk Anda di sini.</p>
</div>

<div class="mb-6">
    <button onclick="openModal('addProductModal')" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Tambah Produk Baru
    </button>
</div>

<div class="bg-white shadow-xl rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full leading-normal">
            <thead class="bg-slate-700 text-white">
                <tr>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Nama Produk</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Deskripsi</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Harga</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Stok</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Satuan</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (!empty($products_list)): ?>
                    <?php foreach ($products_list as $product): ?>
                        <tr class="border-b border-gray-200 hover:bg-slate-50 transition duration-100">
                            <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo $product['product_id']; ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo sanitizeInput($product['name']); ?></td>
                            <td class="px-5 py-4 text-sm">
                                <p class="truncate w-48 md:w-64" title="<?php echo sanitizeInput($product['description']); ?>">
                                    <?php echo sanitizeInput(substrastava name="status" id="add_status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="Menunggu">Menunggu</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('addSupplierOrderModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-blue-600 py-2 rounded-lg text-white hover:bg-blue-700 font-medium transition duration-150">Simpan Pesanan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pesanan Supplier -->
<div id="editSupplierOrderModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-edit">
         <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-edit">Edit Pesanan Supplier</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('editSupplierOrderModal')" aria-label="Tutup modal">
                 <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
            </button>
        </div>
        <form method="POST" action="supplier_orders.php" id="editSupplierOrderFormInternal">
            <input type="hidden" name="action" value="edit_supplier_order">
            <input type="hidden" name="order_id_edit" id="edit_order_id">
            <div class="space-y-4">
                <div>
                    <label for="edit_supplier_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="supplier_name_edit" id="edit_supplier_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_product" class="block text-sm font-medium text-gray-700 mb-1">Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="product_edit" id="edit_product" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity_edit" id="edit_quantity" min="1" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_order_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesanan <span class="text-red-500">*</span></label>
                    <input type="date" name="order_date_edit" id="edit_order_date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status_edit" id="edit_status" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        <option value="Menunggu">Menunggu</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('editSupplierOrderModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-blue-600 py-2 rounded-lg text-white hover:bg-blue-700 font-medium transition duration-150">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
include_once __DIR__ . '/../src/template/footer.php';
?>

<script>
// JavaScript untuk modal Pesanan Supplier
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
             modal.querySelector('.relative').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
             modal.querySelector('.relative').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        });

        if (modalId === 'addSupplierOrderModal') {
            const form = modal.querySelector('#addSupplierOrderFormInternal');
            if(form) form.reset();
            const firstInput = modal.querySelector('input[name="supplier_name"]');
            if (firstInput) {
                firstInput.focus();
            }
        } else if (modalId === 'editSupplierOrderModal') {
             const firstEditInput = modal.querySelector('input[name="supplier_name_edit"]');
            if (firstEditInput) {
                firstEditInput.focus();
            }
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.querySelector('.relative').classList Leading-normal">
                        <option value="Menunggu">Menunggu</option>
                        <option value="Dalam Proses">Dalam Proses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('editSupplierOrderModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-blue-600 py-2 rounded-lg text-white hover:bg-blue-700 font-medium transition duration-150">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php
include_once __DIR__ . '/../src/template/footer.php';
?>

<script>
// JavaScript untuk modal Pesanan Supplier
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
             modal.querySelector('.relative').classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
             modal.querySelector('.relative').classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        });

        if (modalId === 'addSupplierOrderModal') {
            const form = modal.querySelector('#addSupplierOrderFormInternal');
            if(form) form.reset();
            const firstInput = modal.querySelector('input[name="supplier_name"]');
            if (firstInput) {
                firstInput.focus();
            }
        } else if (modalId === 'editSupplierOrderModal') {
             const firstEditInput = modal.querySelector('input[name="supplier_name_edit"]');
            if (firstEditInput) {
                firstEditInput.focus();
            }
        }
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.querySelector('.relative').classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        modal.querySelector('.relative').classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function openEditModal(id, supplier_name, product, quantity, order_date, status) {
    const modal = document.getElementById('editSupplierOrderModal');
    if(modal) {
        modal.querySelector('input[name="order_id_edit"]').value = id;
        modal.querySelector('input[name="supplier_name_edit"]').value = supplier_name;
        modal.querySelector('input[name="product_edit"]').value = product;
        modal.querySelector('input[name="quantity_edit"]').value = quantity;
        modal.querySelector('input[name="order_date_edit"]').value = order_date;
        modal.querySelector('select[name="status_edit"]').value = status;
        openModal('editSupplierOrderModal');
    }
}

window.addEventListener('click', function(event) {
    const addModal = document.getElementById('addSupplierOrderModal');
    const editModal = document.getElementById('editSupplierOrderModal');
    if (addModal && event.target == addModal) {
        closeModal('addSupplierOrderModal');
    }
    if (editModal && event.target == editModal) {
        closeModal('editSupplierOrderModal');
    }
});

window.addEventListener('keydown', function(event) {
    if (event.key === 'Escape' || event.keyCode === 27) {
        const addModal = document.getElementById('addSupplierOrderModal');
        const editModal = document.getElementById('editSupplierOrderModal');
        if (addModal && addModal.style.display === 'flex') {
            closeModal('addSupplierOrderModal');
        }
        if (editModal && editModal.style.display === 'flex') {
            closeModal('editSupplierOrderModal');
        }
    }
});
</script>
