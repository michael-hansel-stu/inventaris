<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/lib/functions.php';
require_once __DIR__ . '/../src/models/ProductModel.php';
require_once __DIR__ . '/../src/controllers/ProductController.php'; 

$page_title = "Manajemen Produk";

// Inisialisasi Model dan Controller
if (!isset($conn) || !$conn instanceof mysqli) {
    die("Koneksi database tidak valid. Periksa file konfigurasi database.");
}
$productModel = new ProductModel($conn);
$productController = new ProductController($productModel);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_result = null;
    $form_data_submitted = $_POST;

    if ($_POST['action'] == 'add_product') {
        $action_result = $productController->handleAddProduct($form_data_submitted);
    } elseif ($_POST['action'] == 'edit_product') {
        $action_result = $productController->handleEditProduct($form_data_submitted);
    } elseif ($_POST['action'] == 'delete_product') {
        $action_result = $productController->handleDeleteProduct($form_data_submitted);
    }

    if ($action_result) {
        if (isset($action_result['success']) && $action_result['success']) {
            $_SESSION['message'] = $action_result['message'];
            unset($_SESSION['form_data_repopulate']); // Nama session umum
            unset($_SESSION['form_errors']);          // Nama session umum
        } elseif (isset($action_result['error']) && $action_result['error']) {
            $_SESSION['error_message'] = $action_result['message'];
            if (isset($action_result['messages']) && is_array($action_result['messages'])) {
                $_SESSION['form_errors'] = $action_result['messages'];
            }
            if ($_POST['action'] == 'add_product' || $_POST['action'] == 'edit_product') {
                $_SESSION['form_data_repopulate'] = $form_data_submitted;
                 // Jika error pada edit, simpan juga ID pemicu untuk JS
                if ($_POST['action'] == 'edit_product' && isset($form_data_submitted['product_id_edit'])) {
                    if(isset($action_result['trigger_id_edit'])){ // Pastikan controller mengirimkan ini
                         $_SESSION['form_data_repopulate']['trigger_id_edit_error'] = $action_result['trigger_id_edit'];
                    }
                }
            }
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan yang tidak diketahui saat memproses aksi produk.";
            if ($_POST['action'] == 'add_product' || $_POST['action'] == 'edit_product') {
                $_SESSION['form_data_repopulate'] = $form_data_submitted;
            }
        }
    }
    
    redirect('products.php'); 
}

$form_errors = $_SESSION['form_errors'] ?? [];
$form_data_repopulate = $_SESSION['form_data_repopulate'] ?? [];

unset($_SESSION['form_errors'], $_SESSION['form_data_repopulate']);

$products_list = $productController->getAllProductsForView();

include_once __DIR__ . '/../src/template/header.php';
?>

<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
    <p class="text-gray-600">Kelola daftar produk inventaris Anda di sini.</p>
</div>

<div class="mb-6">
    <button onclick="handleOpenAddProductModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
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
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-right text-xs font-semibold uppercase tracking-wider">Harga</th>
                    <th class="px-5 py-3 border-b-2 border-slate-200 text-right text-xs font-semibold uppercase tracking-wider">Stok</th>
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
                                    <?php echo sanitizeInput(substr($product['description'], 0, 70)) . (strlen($product['description']) > 70 ? '...' : ''); ?>
                                </p>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right"><?php echo formatRupiah($product['price']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm text-right"><?php echo sanitizeInput($product['quantity']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($product['unit']); ?></td>
                            <td class="px-5 py-4 whitespace-nowrap text-sm">
                                <button onclick="handleOpenEditProductModal(
                                    '<?php echo $product['product_id']; ?>',
                                    '<?php echo htmlspecialchars(addslashes($product['name']), ENT_QUOTES); ?>',
                                    '<?php echo htmlspecialchars(addslashes($product['description']), ENT_QUOTES); ?>',
                                    '<?php echo $product['price']; ?>',
                                    '<?php echo $product['quantity']; ?>',
                                    '<?php echo htmlspecialchars(addslashes($product['unit']), ENT_QUOTES); ?>'
                                )" class="text-indigo-600 hover:text-indigo-900 mr-3 font-semibold hover:underline">Edit</button>
                                <form method="POST" action="products.php" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk \'<?php echo htmlspecialchars(addslashes($product['name']), ENT_QUOTES); ?>\'? Produk yang sudah terkait dengan penjualan atau pesanan mungkin tidak dapat dihapus.');">
                                    <input type="hidden" name="action" value="delete_product">
                                    <input type="hidden" name="product_id_delete" value="<?php echo $product['product_id']; ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            Belum ada produk. Silakan tambahkan produk baru.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<div id="addProductModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-add-product">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-add-product">Tambah Produk Baru</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('addProductModal')" aria-label="Tutup modal">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 50 50">
                    <path d="M 9.15625 6.3125 L 6.3125 9.15625 L 22.15625 25 L 6.21875 40.96875 L 9.03125 43.78125 L 25 27.84375 L 40.9375 43.78125 L 43.78125 40.9375 L 27.84375 25 L 43.6875 9.15625 L 40.84375 6.3125 L 25 22.15625 Z"></path>
                </svg>
            </button>
        </div>
        <form method="POST" action="products.php" id="addProductFormInternal">
            <input type="hidden" name="action" value="add_product">
            <div class="space-y-4">
                <div>
                    <label for="add_product_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="add_product_name" value="<?php echo sanitizeInput($form_data_repopulate['name'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['name']) || isset($form_errors['name_exists']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                    </div>
                <div>
                    <label for="add_product_description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description" id="add_product_description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['description']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm"><?php echo sanitizeInput($form_data_repopulate['description'] ?? ''); ?></textarea>
                    </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="add_product_price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price" id="add_product_price" min="0" step="1" value="<?php echo sanitizeInput($form_data_repopulate['price'] ?? '0'); ?>"
                               class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['price']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                        </div>
                    <div>
                        <label for="add_product_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" id="add_product_quantity" min="0" value="<?php echo sanitizeInput($form_data_repopulate['quantity'] ?? '0'); ?>"
                               class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['quantity']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                        </div>
                </div>
                <div>
                    <label for="add_product_unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan (mis: pcs, kg) <span class="text-red-500">*</span></label>
                    <input type="text" name="unit" id="add_product_unit" value="<?php echo sanitizeInput($form_data_repopulate['unit'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['unit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                    </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('addProductModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-indigo-600 py-2 rounded-lg text-white hover:bg-indigo-700 font-medium transition duration-150">Simpan Produk</button>
            </div>
        </form>
    </div>
</div>

<div id="editProductModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
    <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         role="dialog" aria-modal="true" aria-labelledby="modal-headline-edit-product">
        <div class="flex justify-between items-center pb-3 border-b mb-4">
            <p class="text-2xl font-bold text-gray-800" id="modal-headline-edit-product">Edit Produk</p>
            <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('editProductModal')" aria-label="Tutup modal">
                <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" height="20" viewBox="0 0 50 50">
                    <path d="M 9.15625 6.3125 L 6.3125 9.15625 L 22.15625 25 L 6.21875 40.96875 L 9.03125 43.78125 L 25 27.84375 L 40.9375 43.78125 L 43.78125 40.9375 L 27.84375 25 L 43.6875 9.15625 L 40.84375 6.3125 L 25 22.15625 Z"></path>
                </svg>
            </button>
        </div>
        <form method="POST" action="products.php" id="editProductFormInternal">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="product_id_edit" id="edit_product_id" value="<?php echo sanitizeInput($form_data_repopulate['product_id_edit'] ?? ''); ?>">
            <div class="space-y-4">
                <div>
                    <label for="edit_product_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" name="name_edit" id="edit_product_name" value="<?php echo sanitizeInput($form_data_repopulate['name_edit'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['name_edit']) || isset($form_errors['name_exists_edit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                    </div>
                <div>
                    <label for="edit_product_description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea name="description_edit" id="edit_product_description" rows="3"
                              class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['description_edit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm"><?php echo sanitizeInput($form_data_repopulate['description_edit'] ?? ''); ?></textarea>
                    </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="edit_product_price" class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="price_edit" id="edit_product_price" min="0" step="1" value="<?php echo sanitizeInput($form_data_repopulate['price_edit'] ?? '0'); ?>"
                               class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['price_edit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                        </div>
                    <div>
                        <label for="edit_product_quantity" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity_edit" id="edit_product_quantity" min="0" value="<?php echo sanitizeInput($form_data_repopulate['quantity_edit'] ?? '0'); ?>"
                               class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['quantity_edit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                        </div>
                </div>
                <div>
                    <label for="edit_product_unit" class="block text-sm font-medium text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                    <input type="text" name="unit_edit" id="edit_product_unit" value="<?php echo sanitizeInput($form_data_repopulate['unit_edit'] ?? ''); ?>"
                           class="mt-1 block w-full px-3 py-2 border <?php echo isset($form_errors['unit_edit']) ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-indigo-500 focus:border-indigo-500'; ?> rounded-md shadow-sm sm:text-sm" required>
                    </div>
            </div>
            <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                <button type="button" onclick="closeModal('editProductModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                <button type="submit" class="px-4 bg-indigo-600 py-2 rounded-lg text-white hover:bg-indigo-700 font-medium transition duration-150">Simpan Perubahan</button>
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
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                const modalContent = modal.querySelector('.relative'); 
                if (modalContent) {
                    modalContent.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                    modalContent.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
                }
            });

            const form = modal.querySelector('form'); 
            if (form) {
                const firstInput = form.querySelector('input[type="text"]:not([readonly]):not([disabled]), textarea:not([readonly]):not([disabled])');
                if (firstInput) {
                    firstInput.focus();
                }
            }
        } else {
            console.error("Modal with ID '" + modalId + "' not found.");
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const form = modal.querySelector('form');
            if(form) {
                clearFormErrors(form);
                if (modalId === 'addProductModal' && Object.keys(<?php echo json_encode($form_data_repopulate); ?>).length === 0) {
                    form.reset();
                }
            }
            const modalContent = modal.querySelector('.relative');
            if (modalContent) {
                modalContent.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                modalContent.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            }
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); 
        }
    }

    function clearFormErrors(formElement) {
        if (!formElement) return;
        const errorInputs = formElement.querySelectorAll('.border-red-500');
        errorInputs.forEach(input => {
            input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            input.classList.add('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');
        });
        const errorMessages = formElement.querySelectorAll('p.error-message-field');
        errorMessages.forEach(msg => msg.remove());
    }

    function displayFormErrors(formElement, errors, isEditForm = false) {
        if (!formElement || !errors || Object.keys(errors).length === 0) return; 

        for (const fieldKeyWithController in errors) {
            let inputFieldName = fieldKeyWithController;
            
            const inputField = formElement.elements[inputFieldName];
            const errorMessage = errors[fieldKeyWithController];

            if (inputField) {
                inputField.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
                inputField.classList.remove('border-gray-300', 'focus:ring-indigo-500', 'focus:border-indigo-500');
                
                const errorP = document.createElement('p');
                errorP.className = 'mt-1 text-xs text-red-600 error-message-field';
                errorP.textContent = errorMessage;
                
                inputField.parentNode.insertBefore(errorP, inputField.nextSibling);
            } else {
                console.warn("Field HTML tidak ditemukan di form '" + formElement.id + "' untuk error key:", fieldKeyWithController, ". Mencoba mencari dengan nama:", inputFieldName);
            }
        }
    }

    function handleOpenAddProductModal(dataToRepopulate = {}, errorsToDisplay = {}) {
        const modalId = 'addProductModal';
        const modal = document.getElementById(modalId);
        if (modal) {
            const form = modal.querySelector('#addProductFormInternal'); 
            if (form) {
                clearFormErrors(form);

                if (dataToRepopulate && dataToRepopulate.action === 'add_product' && errorsToDisplay && Object.keys(errorsToDisplay).length > 0) {
                    form.elements['name'].value = dataToRepopulate.name || '';
                    form.elements['description'].value = dataToRepopulate.description || '';
                    form.elements['price'].value = dataToRepopulate.price || '0';
                    form.elements['quantity'].value = dataToRepopulate.quantity || '0';
                    form.elements['unit'].value = dataToRepopulate.unit || '';
                    displayFormErrors(form, errorsToDisplay, false);
                } else {
                    form.reset();
                }
            }
            openModal(modalId); 
        }
    }

    function handleOpenEditProductModal(id, currentName, currentDescription, currentPrice, currentQuantity, currentUnit, errorsForThisEdit = {}) {
        const modalId = 'editProductModal';
        const modal = document.getElementById(modalId);
        if(modal) {
            const form = modal.querySelector('#editProductFormInternal');
            clearFormErrors(form); 
            
            form.elements['product_id_edit'].value = id;
            form.elements['name_edit'].value = currentName;
            form.elements['description_edit'].value = currentDescription;
            form.elements['price_edit'].value = currentPrice;
            form.elements['quantity_edit'].value = currentQuantity;
            form.elements['unit_edit'].value = currentUnit;
            
            if (errorsForThisEdit && Object.keys(errorsForThisEdit).length > 0) {
                displayFormErrors(form, errorsForThisEdit, true);
            }
            openModal(modalId);
        }
    }

    window.addEventListener('click', function(event) { 
        const addModal = document.getElementById('addProductModal');
        const editModal = document.getElementById('editProductModal');
        if (addModal && event.target == addModal) {
            closeModal('addProductModal');
        }
        if (editModal && event.target == editModal) {
            closeModal('editProductModal');
        } 
    });

    window.addEventListener('keydown', function(event) { 
        if (event.key === 'Escape' || event.keyCode === 27) {
            const addModal = document.getElementById('addProductModal');
            const editModal = document.getElementById('editProductModal');
            if (addModal && addModal.style.display === 'flex') {
                closeModal('addProductModal');
            }
            if (editModal && editModal.style.display === 'flex') {
                closeModal('editProductModal');
            }
        }
    });

    function closeFlashMessage(elementId) { 
        const flashMessage = document.getElementById(elementId);
        if (flashMessage) {
            flashMessage.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out'; 
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'scale(0.95)'; 
            setTimeout(() => {
                flashMessage.style.display = 'none';
                flashMessage.remove();
            }, 300);
        }
     }

    document.addEventListener('DOMContentLoaded', function() {
        const successFlash = document.getElementById('flashMessageSuccess');
        const errorGeneralFlash = document.getElementById('flashMessageErrorGeneral');

        if (successFlash) {
            setTimeout(() => { closeFlashMessage('flashMessageSuccess'); }, 3000);
        }
        if (errorGeneralFlash) {
            setTimeout(() => { closeFlashMessage('flashMessageErrorGeneral'); }, 4000);
        }

        const phpFormDataRepopulate = <?php echo json_encode($form_data_repopulate); ?>;
        const phpFormErrors = <?php echo json_encode($form_errors); ?>;

        if (phpFormDataRepopulate && phpFormDataRepopulate.action) {
            if (phpFormDataRepopulate.action === 'add_product' && phpFormErrors && Object.keys(phpFormErrors).length > 0) {
                handleOpenAddProductModal(phpFormDataRepopulate, phpFormErrors);
            } else if (phpFormDataRepopulate.action === 'edit_product' && phpFormErrors && Object.keys(phpFormErrors).length > 0 && phpFormDataRepopulate.product_id_edit) {
                handleOpenEditProductModal(
                    phpFormDataRepopulate.product_id_edit,
                    phpFormDataRepopulate.name_edit || '', 
                    phpFormDataRepopulate.description_edit || '',
                    phpFormDataRepopulate.price_edit || '0',
                    phpFormDataRepopulate.quantity_edit || '0',
                    phpFormDataRepopulate.unit_edit || '',
                    phpFormErrors
                );
            }
        }
    });
</script>
