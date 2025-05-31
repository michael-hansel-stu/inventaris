<?php
// Simulate required files for structure, but no actual backend logic will run
// require_once __DIR__ . '/../src/config/database.php';      // Untuk $conn (Simulated)
// require_once __DIR__ . '/../src/lib/functions.php';       // Untuk sanitizeInput, formatRupiah, redirect, dll. (Simulated)
// require_once __DIR__ . '/../src/models/SupplierModel.php'; // Model (Simulated)
// require_once __DIR__ . '/../src/controllers/SupplierController.php'; // Controller (Simulated)

// --- SIMULATE functions.php for UI ---
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }
}
if (!function_exists('redirect')) {
    function redirect($url) {
        // In a real app, this would redirect. For UI, we'll do nothing.
        // header("Location: " . $url);
        // exit();
    }
}
// --- END SIMULATE functions.php ---

$page_title = "Manajemen Supplier"; // Akan digunakan oleh header.php

// 2. Inisialisasi Model dan Controller (Simulated - not functional for UI only)
// $conn = null; // Simulate no DB connection for UI only
// if (!isset($conn) || !$conn instanceof mysqli) {
//     // die("Koneksi database tidak valid. Periksa file konfigurasi database.");
// }
// $supplierModel = new SupplierModel($conn); // Placeholder
// $supplierController = new SupplierController($supplierModel); // Placeholder

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Simulate POST request handling for flash messages
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action_result = null;

    // --- SIMULATED ACTIONS ---
    if ($_POST['action'] == 'add_supplier') {
        // Simulate success for UI testing of flash message
        $_SESSION['message'] = "Supplier baru berhasil ditambahkan (Simulasi).";
        // To test error:
        // $_SESSION['error_message'] = "Gagal menambahkan supplier (Simulasi Error).";
    } elseif ($_POST['action'] == 'edit_supplier') {
        $_SESSION['message'] = "Data supplier berhasil diubah (Simulasi).";
    } elseif ($_POST['action'] == 'delete_supplier') {
        $_SESSION['message'] = "Supplier berhasil dihapus (Simulasi).";
    }
    // --- END SIMULATED ACTIONS ---

    redirect('suppliers.php'); // Redirect kembali ke halaman suppliers.php
}

// 4. Ambil data supplier untuk ditampilkan (Simulated sample data)
$suppliers_list = [
    [
        'supplier_id' => 'SUP001',
        'name' => 'PT Sejahtera Abadi',
        'contact_person' => 'Bapak Subagio',
        'phone' => '081234567890',
        'email' => 'subagio@sejahtera.co.id',
        'address' => 'Jl. Merdeka No. 123, Jakarta Pusat, DKI Jakarta',
    ],
    [
        'supplier_id' => 'SUP002',
        'name' => 'CV Maju Jaya Bersama',
        'contact_person' => 'Ibu Rina Amelia',
        'phone' => '021-555-0102',
        'email' => 'rina.amelia@majujaya.com',
        'address' => 'Komp. Pergudangan Sentosa Blok C5 No. 8, Surabaya Timur, Jawa Timur',
    ],
    [
        'supplier_id' => 'SUP003',
        'name' => 'UD Sumber Rejeki',
        'contact_person' => 'Andi Wijaya',
        'phone' => '085511223344',
        'email' => 'andi@sumberrejeki.net',
        'address' => 'Jl. Gatot Subroto Kav. 18, Medan, Sumatera Utara',
    ]
];
// Jika ingin menguji tampilan tabel kosong:
// $suppliers_list = [];

// 5. Render Tampilan
// include_once __DIR__ . '/../src/template/header.php'; // Assuming header.php exists and uses $page_title
// For this standalone UI example, we'll mock a simple header part.
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo sanitizeInput($page_title); ?> - Aplikasi POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom styles for modal animation if needed, or use Tailwind's */
        .modal { display: none; } /* Initially hidden */
        .modal.flex { display: flex; } /* Shown by JS */

        /* Simple Flash Message Styling */
        .flash-message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
        }
        .flash-message-success {
            background-color: #d1fae5; /* green-100 */
            color: #065f46; /* green-800 */
            border: 1px solid #6ee7b7; /* green-300 */
        }
        .flash-message-error {
            background-color: #fee2e2; /* red-100 */
            color: #991b1b; /* red-800 */
            border: 1px solid #fca5a5; /* red-300 */
        }
        .flash-close-button {
            margin-left: auto;
            background: transparent;
            border: none;
            font-size: 1.25rem;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-slate-100 text-gray-800 font-sans">
    <nav class="bg-slate-800 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <a href="#" class="text-xl font-bold">Aplikasi POS</a>
            <div>
                <a href="#" class="px-3 py-2 hover:bg-slate-700 rounded">Dashboard</a>
                <a href="#" class="px-3 py-2 hover:bg-slate-700 rounded bg-slate-700">Supplier</a>
                <a href="#" class="px-3 py-2 hover:bg-slate-700 rounded">Produk</a>
                 </div>
        </div>
    </nav>

    <main class="container mx-auto p-6">
        <?php
        // Simulate displayFlashMessages() from header.php
        if (isset($_SESSION['message'])) {
            echo '<div id="flashMessageSuccess" class="flash-message flash-message-success flex items-center">';
            echo '<span>' . sanitizeInput($_SESSION['message']) . '</span>';
            echo '<button type="button" class="flash-close-button" onclick="closeFlashMessage(\'flashMessageSuccess\')">&times;</button>';
            echo '</div>';
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<div id="flashMessageError" class="flash-message flash-message-error flex items-center">';
            echo '<span>' . sanitizeInput($_SESSION['error_message']) . '</span>';
            echo '<button type="button" class="flash-close-button" onclick="closeFlashMessage(\'flashMessageError\')">&times;</button>';
            echo '</div>';
            unset($_SESSION['error_message']);
        }
        ?>

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2"><?php echo sanitizeInput($page_title); ?></h1>
            <p class="text-gray-600">Kelola daftar supplier Anda di sini.</p>
        </div>

        <div class="mb-6">
            <button onclick="openModal('addSupplierModal')" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2 -mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                </svg>
                Tambah Supplier Baru
            </button>
        </div>

        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead class="bg-slate-700 text-white">
                        <tr>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">ID</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Nama Supplier</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Kontak Person</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Telepon</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Alamat</th>
                            <th class="px-5 py-3 border-b-2 border-slate-200 text-left text-xs font-semibold uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (!empty($suppliers_list)): ?>
                            <?php foreach ($suppliers_list as $supplier): ?>
                                <tr class="border-b border-gray-200 hover:bg-slate-50 transition duration-100">
                                    <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($supplier['supplier_id']); ?></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo sanitizeInput($supplier['name']); ?></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($supplier['contact_person']); ?></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($supplier['phone']); ?></td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm"><?php echo sanitizeInput($supplier['email']); ?></td>
                                    <td class="px-5 py-4 text-sm">
                                        <p class="truncate w-48 md:w-64" title="<?php echo sanitizeInput($supplier['address']); ?>">
                                            <?php echo sanitizeInput(substr($supplier['address'], 0, 50)) . (strlen($supplier['address']) > 50 ? '...' : ''); ?>
                                        </p>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-sm">
                                        <button onclick="openEditSupplierModal(
                                            '<?php echo $supplier['supplier_id']; ?>',
                                            '<?php echo htmlspecialchars(addslashes($supplier['name']), ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars(addslashes($supplier['contact_person']), ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars(addslashes($supplier['phone']), ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars(addslashes($supplier['email']), ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars(addslashes($supplier['address']), ENT_QUOTES); ?>'
                                        )" class="text-indigo-600 hover:text-indigo-900 mr-3 font-semibold hover:underline">Edit</button>
                                        <form method="POST" action="suppliers.php" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier \'<?php echo htmlspecialchars(addslashes($supplier['name']), ENT_QUOTES); ?>\'? Tindakan ini tidak dapat dibatalkan.');">
                                            <input type="hidden" name="action" value="delete_supplier">
                                            <input type="hidden" name="supplier_id_delete" value="<?php echo $supplier['supplier_id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-semibold hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    Belum ada supplier. Silakan tambahkan supplier baru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <div id="addSupplierModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             role="dialog" aria-modal="true" aria-labelledby="modal-headline-add-supplier">
            <div class="flex justify-between items-center pb-3 border-b mb-4">
                <p class="text-2xl font-bold text-gray-800" id="modal-headline-add-supplier">Tambah Supplier Baru</p>
                <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('addSupplierModal')" aria-label="Tutup modal">
                    <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
                </button>
            </div>
            <form method="POST" action="suppliers.php" id="addSupplierFormInternal">
                <input type="hidden" name="action" value="add_supplier">
                <div class="space-y-4">
                    <div>
                        <label for="add_supplier_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="add_supplier_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="add_contact_person" class="block text-sm font-medium text-gray-700 mb-1">Kontak Person</label>
                        <input type="text" name="contact_person" id="add_contact_person" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="add_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepon <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone" id="add_phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="add_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" id="add_email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="add_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="address" id="add_address" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                    <button type="button" onclick="closeModal('addSupplierModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                    <button type="submit" class="px-4 bg-green-600 py-2 rounded-lg text-white hover:bg-green-700 font-medium transition duration-150">Simpan Supplier</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editSupplierModal" class="modal fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full items-center justify-center z-50">
        <div class="relative mx-auto p-6 border w-full max-w-lg shadow-lg rounded-md bg-white transform transition-all sm:my-8 opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             role="dialog" aria-modal="true" aria-labelledby="modal-headline-edit-supplier">
            <div class="flex justify-between items-center pb-3 border-b mb-4">
                <p class="text-2xl font-bold text-gray-800" id="modal-headline-edit-supplier">Edit Supplier</p>
                <button type="button" class="cursor-pointer z-50 p-2 -mr-2 rounded-full hover:bg-gray-200 transition" onclick="closeModal('editSupplierModal')" aria-label="Tutup modal">
                    <svg class="fill-current text-gray-700 hover:text-gray-900" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M10 8.586L15.929.657l1.414 1.414L11.414 10l5.929 5.929-1.414 1.414L10 11.414l-5.929 5.929-1.414-1.414L8.586 10 .657 4.071 2.071 2.657z"/></svg>
                </button>
            </div>
            <form method="POST" action="suppliers.php" id="editSupplierFormInternal">
                <input type="hidden" name="action" value="edit_supplier">
                <input type="hidden" name="supplier_id_edit" id="edit_supplier_id">
                <div class="space-y-4">
                    <div>
                        <label for="edit_supplier_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="name_edit" id="edit_supplier_name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                    </div>
                    <div>
                        <label for="edit_contact_person" class="block text-sm font-medium text-gray-700 mb-1">Kontak Person</label>
                        <input type="text" name="contact_person_edit" id="edit_contact_person" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="edit_phone" class="block text-sm font-medium text-gray-700 mb-1">Telepon <span class="text-red-500">*</span></label>
                            <input type="tel" name="phone_edit" id="edit_phone" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                        </div>
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" name="email_edit" id="edit_email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="edit_address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="address_edit" id="edit_address" rows="3" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
                <div class="flex justify-end pt-6 space-x-3 border-t mt-6">
                    <button type="button" onclick="closeModal('editSupplierModal')" class="px-4 bg-gray-200 py-2 rounded-lg text-gray-800 hover:bg-gray-300 font-medium transition duration-150">Batal</button>
                    <button type="submit" class="px-4 bg-blue-600 py-2 rounded-lg text-white hover:bg-blue-700 font-medium transition duration-150">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="text-center text-sm text-gray-500 py-10">
        &copy; <?php echo date("Y"); ?> Aplikasi POS Sederhana. Hak Cipta Dilindungi.
    </footer>
<?php
// include_once __DIR__ . '/../src/template/footer.php'; // Assuming footer.php exists
// Simulate closing connection if it were opened
// if (isset($conn) && $conn instanceof mysqli) {
//     $conn->close();
// }
?>
<script>
    // JavaScript untuk modal
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            requestAnimationFrame(() => {
                const modalDialog = modal.querySelector('.relative');
                modalDialog.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
                modalDialog.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
            });

            if (modalId === 'addSupplierModal') {
                const form = modal.querySelector('#addSupplierFormInternal');
                if(form) form.reset();
                const firstInput = modal.querySelector('input[name="name"]');
                if (firstInput) firstInput.focus();
            } else if (modalId === 'editSupplierModal') {
                const firstEditInput = modal.querySelector('input[name="name_edit"]');
                if (firstEditInput) firstEditInput.focus();
            }
        }
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const modalDialog = modal.querySelector('.relative');
            modalDialog.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            modalDialog.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Corresponds to Tailwind's default transition duration
        }
    }

    function openEditSupplierModal(id, name, contact_person, phone, email, address) {
        const modal = document.getElementById('editSupplierModal');
        if(modal) {
            modal.querySelector('input[name="supplier_id_edit"]').value = id;
            modal.querySelector('input[name="name_edit"]').value = name;
            modal.querySelector('input[name="contact_person_edit"]').value = contact_person;
            modal.querySelector('input[name="phone_edit"]').value = phone;
            modal.querySelector('input[name="email_edit"]').value = email;
            modal.querySelector('textarea[name="address_edit"]').value = address;
            openModal('editSupplierModal');
        }
    }

    // Event listener untuk klik di luar modal dan tombol Escape
    window.addEventListener('click', function(event) {
        const addModal = document.getElementById('addSupplierModal');
        const editModal = document.getElementById('editSupplierModal');
        if (addModal && event.target == addModal) {
            closeModal('addSupplierModal');
        }
        if (editModal && event.target == editModal) {
            closeModal('editSupplierModal');
        }
    });

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            const addModal = document.getElementById('addSupplierModal');
            const editModal = document.getElementById('editSupplierModal');
            if (addModal && addModal.style.display === 'flex') {
                closeModal('addSupplierModal');
            }
            if (editModal && editModal.style.display === 'flex') {
                closeModal('editSupplierModal');
            }
        }
    });

    function closeFlashMessage(elementId) {
        const flashMessage = document.getElementById(elementId);
        if (flashMessage) {
            flashMessage.style.transition = 'opacity 0.3s ease-out';
            flashMessage.style.opacity = '0';
            setTimeout(() => {
                flashMessage.style.display = 'none';
            }, 300);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const successFlash = document.getElementById('flashMessageSuccess');
        const errorFlash = document.getElementById('flashMessageError');

        if (successFlash) {
            setTimeout(() => {
                closeFlashMessage('flashMessageSuccess');
            }, 5000); // Disappears after 5 seconds
        }
        if (errorFlash) {
            setTimeout(() => {
                closeFlashMessage('flashMessageError');
            }, 7000); // Error messages might need more time to be read
        }
    });
</script>
</body>
</html>
