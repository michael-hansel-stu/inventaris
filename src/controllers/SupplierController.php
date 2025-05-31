<?php
// Asumsikan functions.php sudah di-require di file utama (misal: suppliers.php)
// require_once __DIR__ . '/../lib/functions.php'; 

class SupplierController {
    private $supplierModel;

    public function __construct(SupplierModel $model) {
        $this->supplierModel = $model;
    }

    /**
     * Menangani permintaan untuk menambahkan supplier baru.
     * @param array $postData Data dari $_POST.
     * @return array Hasil operasi (success/error message).
     */
    public function handleAddSupplier(array $postData) {
        // Validasi dasar (bisa lebih kompleks sesuai kebutuhan)
        if (empty($postData['name']) || empty($postData['phone'])) {
            return ['error' => true, 'message' => 'Nama supplier dan telepon wajib diisi.'];
        }

        $data = [
            'name'           => sanitizeInput($postData['name']),
            'contact_person' => isset($postData['contact_person']) ? sanitizeInput($postData['contact_person']) : null,
            'phone'          => sanitizeInput($postData['phone']),
            'email'          => isset($postData['email']) ? sanitizeInput($postData['email']) : null,
            'address'        => isset($postData['address']) ? sanitizeInput($postData['address']) : null,
        ];
        
        // Validasi email jika diisi
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error' => true, 'message' => 'Format email tidak valid.'];
        }

        $supplierId = $this->supplierModel->createSupplier($data);

        if ($supplierId) {
            return ['success' => true, 'message' => 'Supplier baru berhasil ditambahkan. ID: ' . $supplierId];
        } else {
            return ['error' => true, 'message' => 'Gagal menambahkan supplier baru. Kesalahan database.'];
        }
    }

    /**
     * Menangani permintaan untuk mengedit supplier.
     * @param array $postData Data dari $_POST.
     * @return array Hasil operasi (success/error message).
     */
    public function handleEditSupplier(array $postData) {
        if (empty($postData['supplier_id_edit']) || !is_numeric($postData['supplier_id_edit'])) {
            return ['error' => true, 'message' => 'ID Supplier tidak valid untuk diedit.'];
        }
        $id = (int) $postData['supplier_id_edit'];

        // Validasi dasar
        if (empty($postData['name_edit']) || empty($postData['phone_edit'])) {
            return ['error' => true, 'message' => 'Nama supplier dan telepon wajib diisi saat mengedit.'];
        }

        $data = [
            'name'           => sanitizeInput($postData['name_edit']),
            'contact_person' => isset($postData['contact_person_edit']) ? sanitizeInput($postData['contact_person_edit']) : null,
            'phone'          => sanitizeInput($postData['phone_edit']),
            'email'          => isset($postData['email_edit']) ? sanitizeInput($postData['email_edit']) : null,
            'address'        => isset($postData['address_edit']) ? sanitizeInput($postData['address_edit']) : null,
        ];

        // Validasi email jika diisi
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['error' => true, 'message' => 'Format email tidak valid.'];
        }
        
        // Cek apakah supplier ada sebelum update
        $existingSupplier = $this->supplierModel->getSupplierById($id);
        if (!$existingSupplier) {
            return ['error' => true, 'message' => 'Supplier dengan ID ' . $id . ' tidak ditemukan.'];
        }

        if ($this->supplierModel->updateSupplier($id, $data)) {
            // Periksa apakah ada perubahan, karena affected_rows bisa 0 jika data sama
            // Untuk kesederhanaan, kita anggap berhasil jika execute() true.
            // Jika ingin lebih akurat, bisa bandingkan data lama dan baru sebelum update.
            return ['success' => true, 'message' => 'Data supplier berhasil diperbarui.'];
        } else {
            // Bisa jadi error, atau tidak ada baris yang terpengaruh karena data sama
            // Untuk menangani kasus "tidak ada perubahan data", logika bisa lebih kompleks
            // Di sini kita anggap jika updateSupplier mengembalikan false, itu adalah error DB
            return ['error' => true, 'message' => 'Gagal memperbarui data supplier atau tidak ada perubahan data.'];
        }
    }

    /**
     * Menangani permintaan untuk menghapus supplier.
     * @param array $postData Data dari $_POST.
     * @return array Hasil operasi (success/error message).
     */
    public function handleDeleteSupplier(array $postData) {
        if (empty($postData['supplier_id_delete']) || !is_numeric($postData['supplier_id_delete'])) {
            return ['error' => true, 'message' => 'ID Supplier tidak valid untuk dihapus.'];
        }
        $id = (int) $postData['supplier_id_delete'];

        // Cek apakah supplier ada sebelum delete
        $existingSupplier = $this->supplierModel->getSupplierById($id);
        if (!$existingSupplier) {
            return ['error' => true, 'message' => 'Supplier dengan ID ' . $id . ' tidak ditemukan untuk dihapus.'];
        }

        if ($this->supplierModel->deleteSupplier($id)) {
            return ['success' => true, 'message' => 'Supplier berhasil dihapus.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menghapus supplier. Kemungkinan supplier sudah tidak ada atau terjadi kesalahan database.'];
        }
    }

    /**
     * Mendapatkan semua supplier untuk ditampilkan di view.
     * Data sudah di-sanitize jika diperlukan (meskipun untuk output ke HTML, htmlspecialchars di view lebih tepat).
     * @return array List supplier.
     */
    public function getAllSuppliersForView() {
        $suppliers = $this->supplierModel->getAllSuppliers();
        if ($suppliers === false) {
            // Penanganan error jika query gagal
            $_SESSION['error_message'] = "Gagal mengambil data supplier dari database.";
            return [];
        }
        
        // Contoh: Jika Anda ingin melakukan sanitasi tambahan sebelum ke view (opsional)
        // return array_map(function($supplier) {
        //     foreach ($supplier as $key => $value) {
        //         $supplier[$key] = sanitizeInput((string)$value); // Konversi ke string untuk antisipasi null
        //     }
        //     return $supplier;
        // }, $suppliers);
        
        return $suppliers; // Data asli dari DB, sanitasi output dihandle di view (misal: dengan htmlspecialchars)
    }
}
?>
