<?php

require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../lib/functions.php';

class ProductController {
    private $productModel;

    public function __construct(ProductModel $productModel) {
        $this->productModel = $productModel;
    }

    public function handleAddProduct($postData) {
        $errors = [];
        $name = sanitizeInput($postData['name'] ?? null);
        $description = sanitizeInput($postData['description'] ?? '');
        $price = filter_var($postData['price'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $quantity = filter_var($postData['quantity'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $unit = sanitizeInput($postData['unit'] ?? null);

        if (empty($name)) {
            $errors['name'] = 'Nama produk wajib diisi.';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Nama produk maksimal 100 karakter.';
        }

        if ($price === false) {
            $errors['price'] = 'Harga produk harus angka positif atau nol.';
        }

        if ($quantity === false) {
            $errors['quantity'] = 'Kuantitas produk harus angka positif atau nol.';
        }

        if (empty($unit)) {
            $errors['unit'] = 'Satuan produk wajib diisi.';
        } elseif (strlen($unit) > 20) {
            $errors['unit'] = 'Satuan produk maksimal 20 karakter.';
        }
        
        if (!empty($errors)) {
            return ['error' => true, 'messages' => $errors, 'message' => 'Terdapat kesalahan pada input Anda. Silakan periksa kembali.'];
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
        $errors = [];
        $product_id = filter_var($postData['product_id_edit'] ?? null, FILTER_VALIDATE_INT);
        
        if (!$product_id) {
            return ['error' => true, 'message' => 'ID produk tidak valid atau tidak ditemukan untuk diedit.'];
        }

        $existingProduct = $this->productModel->getProductById($product_id);
        if (!$existingProduct) {
            return ['error' => true, 'message' => 'Produk dengan ID ' . $product_id . ' tidak ditemukan.'];
        }

        $name = sanitizeInput($postData['name_edit'] ?? null);
        $description = sanitizeInput($postData['description_edit'] ?? '');
        $price = filter_var($postData['price_edit'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $quantity = filter_var($postData['quantity_edit'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $unit = sanitizeInput($postData['unit_edit'] ?? null);

        if (empty($name)) {
            $errors['name_edit'] = 'Nama produk wajib diisi.';
        } elseif (strlen($name) > 100) {
            $errors['name_edit'] = 'Nama produk maksimal 100 karakter.';
        }

        if ($price === false) {
            $errors['price_edit'] = 'Harga produk harus angka positif atau nol.';
        }

        if ($quantity === false) {
            $errors['quantity_edit'] = 'Kuantitas produk harus angka positif atau nol.';
        }

        if (empty($unit)) {
            $errors['unit_edit'] = 'Satuan produk wajib diisi.';
        } elseif (strlen($unit) > 20) {
            $errors['unit_edit'] = 'Satuan produk maksimal 20 karakter.';
        }
        
        if(!empty($errors)) {
            return ['error' => true, 'messages' => $errors, 'message' => 'Terdapat kesalahan pada input Anda. Silakan periksa kembali.', 'trigger_id_edit' => $product_id];
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

        // (PENTING) Pengecekan keterkaitan produk sebelum menghapus.
        // Contoh:
        // if (class_exists('SaleModel') && method_exists('SaleModel', 'isProductInSales')) {
        //     // Anda perlu cara untuk mendapatkan koneksi DB atau instance SaleModel
        //     // $saleModel = new SaleModel($this->productModel->getConnection());
        //     // if ($saleModel->isProductInSales($product_id)) {
        //     //     return ['error' => true, 'message' => 'Produk tidak dapat dihapus karena terkait dengan data penjualan.'];
        //     // }
        // }
        // if (class_exists('OrderModel') && method_exists('OrderModel', 'isProductInSupplyOrders')) {
        //     // $orderModel = new OrderModel($this->productModel->getConnection());
        //     // if ($orderModel->isProductInSupplyOrders($product_id)) {
        //     //     return ['error' => true, 'message' => 'Produk tidak dapat dihapus karena terkait dengan data pesanan supplier.'];
        //     // }
        // }


        $productData = $this->productModel->getProductById($product_id);
        $productNameForMessage = $productData ? sanitizeInput($productData['name']) : "ID: ".$product_id;

        if ($this->productModel->deleteProduct($product_id)) {
            return ['success' => true, 'message' => 'Produk "' . $productNameForMessage . '" berhasil dihapus.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menghapus produk "' . $productNameForMessage . '". Produk mungkin masih terkait dengan data lain atau tidak ditemukan.'];
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
