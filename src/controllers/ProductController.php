<?php
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../lib/functions.php'; 

class ProductController {
    private $productModel;

    public function __construct(ProductModel $productModel) {
        $this->productModel = $productModel;
    }

    public function handleAddProduct($postData) {
        $name = sanitizeInput($postData['name'] ?? null);
        $description = sanitizeInput($postData['description'] ?? null);
        $price = filter_var($postData['price'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $quantity = filter_var($postData['quantity'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $unit = sanitizeInput($postData['unit'] ?? null);

        if (empty($name) || $price === false || $quantity === false || empty($unit)) {
            return ['error' => true, 'message' => 'Semua field wajib diisi dengan benar. Harga dan kuantitas tidak boleh negatif.'];
        }

        $dataToSave = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'quantity' => $quantity,
            'unit' => $unit
        ];

        $productId = $this->productModel->createProduct($dataToSave);

        if ($productId) {
            return ['success' => true, 'message' => 'Produk "' . htmlspecialchars($name) . '" berhasil ditambahkan dengan ID: ' . $productId . '.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menambahkan produk. Terjadi kesalahan database.'];
        }
    }

    public function handleEditProduct($postData) {
        $product_id = filter_var($postData['product_id_edit'] ?? null, FILTER_VALIDATE_INT);
        $name = sanitizeInput($postData['name_edit'] ?? null);
        $description = sanitizeInput($postData['description_edit'] ?? null);
        $price = filter_var($postData['price_edit'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $quantity = filter_var($postData['quantity_edit'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $unit = sanitizeInput($postData['unit_edit'] ?? null);

        if (!$product_id || empty($name) || $price === false || $quantity === false || empty($unit)) {
            return ['error' => true, 'message' => 'Data produk tidak valid untuk diedit. Semua field wajib diisi dengan benar.'];
        }

        $dataToUpdate = [
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'quantity' => $quantity,
            'unit' => $unit
        ];

        if ($this->productModel->updateProduct($product_id, $dataToUpdate)) {
            return ['success' => true, 'message' => 'Produk "' . htmlspecialchars($name) . '" (ID: ' . $product_id . ') berhasil diperbarui.'];
        } else {
            return ['error' => true, 'message' => 'Gagal memperbarui produk atau tidak ada data yang berubah.'];
        }
    }

    public function handleDeleteProduct($postData) {
        $product_id = filter_var($postData['product_id_delete'] ?? null, FILTER_VALIDATE_INT);

        if (!$product_id) {
            return ['error' => true, 'message' => 'ID produk tidak valid untuk dihapus.'];
        }

        if ($this->productModel->deleteProduct($product_id)) {
            return ['success' => true, 'message' => 'Produk dengan ID: ' . $product_id . ' berhasil dihapus.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menghapus produk. Produk mungkin tidak ditemukan atau terkait dengan data lain (jika ada foreign key constraint).'];
        }
    }

    public function getAllProductsForView() {
        return $this->productModel->getAllProducts();
    }

    public function getProductByIdForView($product_id) {
        $id = filter_var($product_id, FILTER_VALIDATE_INT);
        if (!$id) {
            return null;
        }
        return $this->productModel->getProductById($id);
    }
}
?>