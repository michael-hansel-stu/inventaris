<?php
// src/controllers/SaleItemController.php

require_once __DIR__ . '/../models/SaleItemModel.php';
require_once __DIR__ . '/../models/ProductModel.php'; // Needed to get product prices and list
require_once __DIR__ . '/../lib/functions.php';

class SaleItemController {
    private $saleItemModel;
    private $productModel;

    public function __construct(SaleItemModel $saleItemModel, ProductModel $productModel) {
        $this->saleItemModel = $saleItemModel;
        $this->productModel = $productModel;
    }

    /**
     * Handles adding a new sale item to a specific sale.
     * @param array $postData Data from the POST request. Expected keys:
     * 'sales_id', 'product_id', 'quantity'.
     * @return array Result array with 'success' or 'error' status and a 'message'.
     */
    public function handleAddSaleItem($postData) {
        $sales_id = filter_var($postData['sales_id'] ?? null, FILTER_VALIDATE_INT);
        $product_id = filter_var($postData['product_id'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($postData['quantity'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if (!$sales_id || !$product_id || $quantity === false) {
            return ['error' => true, 'message' => 'Data item penjualan tidak valid. Pastikan produk dan kuantitas diisi dengan benar (minimal 1).'];
        }

        // Get product details to calculate total_price and check stock
        $product = $this->productModel->getProductById($product_id);
        if (!$product) {
            return ['error' => true, 'message' => 'Produk tidak ditemukan.'];
        }

        // Optional: Check stock availability
        if ($product['quantity'] < $quantity) {
            return ['error' => true, 'message' => 'Stok produk tidak mencukupi. Sisa stok: ' . $product['quantity'] . '.'];
        }

        $total_price = $product['price'] * $quantity; // Calculate total price for this item

        $dataToSave = [
            'sales_id' => $sales_id,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'total_price' => $total_price
        ];

        $saleItemId = $this->saleItemModel->createSaleItem($dataToSave);

        if ($saleItemId) {
            // IMPORTANT: After adding item, update product stock in ProductModel
            // This makes sure the operation is atomic with product stock update
            // For example: $this->productModel->updateStock($product_id, -$quantity);
            // The SaleItemModel example has a private updateProductStock method too, choose one place.
            // Let's assume for now the SaleItemModel's createSaleItem handles stock reduction if enabled.

            return ['success' => true, 'message' => 'Item "' . htmlspecialchars($product['name']) . '" berhasil ditambahkan ke penjualan.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menambahkan item ke penjualan. Terjadi kesalahan database.'];
        }
    }

    /**
     * Handles editing an existing sale item.
     * @param array $postData Data from POST. Expected: 'sale_item_id_edit', 'product_id_edit', 'quantity_edit'.
     * @return array Result array.
     */
    public function handleEditSaleItem($postData) {
        $sale_item_id = filter_var($postData['sale_item_id_edit'] ?? null, FILTER_VALIDATE_INT);
        // In a real scenario, you might not allow changing the product_id of an existing sale item.
        // You might delete and add a new one. For this example, we allow it.
        $product_id = filter_var($postData['product_id_edit'] ?? null, FILTER_VALIDATE_INT);
        $quantity = filter_var($postData['quantity_edit'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);

        if (!$sale_item_id || !$product_id || $quantity === false) {
            return ['error' => true, 'message' => 'Data item penjualan tidak valid untuk diedit.'];
        }

        // Get existing sale item to compare quantities for stock adjustment (if implemented)
        // $oldSaleItem = $this->saleItemModel->getSaleItemById($sale_item_id);
        // if (!$oldSaleItem) {
        //     return ['error' => true, 'message' => 'Item penjualan yang akan diedit tidak ditemukan.'];
        // }

        $product = $this->productModel->getProductById($product_id);
        if (!$product) {
            return ['error' => true, 'message' => 'Produk yang dipilih tidak ditemukan.'];
        }
        
        // Optional: Stock check logic for editing
        // $quantityDifference = $quantity - $oldSaleItem['quantity']; // if product is the same
        // Or more complex logic if product_id can change.
        // if ($product['quantity'] < $quantityDifference_if_product_unchanged_or_just_quantity_if_product_changed) {
        //     return ['error' => true, 'message' => 'Stok produk tidak mencukupi untuk perubahan ini.'];
        // }

        $total_price = $product['price'] * $quantity;

        $dataToUpdate = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'total_price' => $total_price
        ];

        if ($this->saleItemModel->updateSaleItem($sale_item_id, $dataToUpdate)) {
            // Adjust stock based on quantity change if implemented
            // Example: $this->productModel->updateStock($oldSaleItem['product_id'], $oldSaleItem['quantity']); // Revert old stock
            // $this->productModel->updateStock($product_id, -$quantity); // Apply new stock
            return ['success' => true, 'message' => 'Item penjualan (ID: ' . $sale_item_id . ') berhasil diperbarui.'];
        } else {
            return ['error' => true, 'message' => 'Gagal memperbarui item penjualan atau tidak ada data yang berubah.'];
        }
    }

    /**
     * Handles deleting a sale item.
     * @param array $postData Data from POST. Expected: 'sale_item_id_delete'.
     * @return array Result array.
     */
    public function handleDeleteSaleItem($postData) {
        $sale_item_id = filter_var($postData['sale_item_id_delete'] ?? null, FILTER_VALIDATE_INT);

        if (!$sale_item_id) {
            return ['error' => true, 'message' => 'ID item penjualan tidak valid untuk dihapus.'];
        }

        // Before deleting, get item details if you need to revert stock
        // $itemToDelete = $this->saleItemModel->getSaleItemById($sale_item_id);

        if ($this->saleItemModel->deleteSaleItem($sale_item_id)) {
            // If stock management is active, revert the stock for the deleted item
            // Example: $this->productModel->updateStock($itemToDelete['product_id'], $itemToDelete['quantity']);
            return ['success' => true, 'message' => 'Item penjualan dengan ID: ' . $sale_item_id . ' berhasil dihapus.'];
        } else {
            return ['error' => true, 'message' => 'Gagal menghapus item penjualan. Item mungkin tidak ditemukan.'];
        }
    }

    /**
     * Gets all sale items for a specific sale, formatted for view.
     * @param int $sales_id The ID of the sale.
     * @return array List of sale items.
     */
    public function getSaleItemsForView($sales_id) {
        $id = filter_var($sales_id, FILTER_VALIDATE_INT);
        if (!$id) {
            return [];
        }
        return $this->saleItemModel->getSaleItemsBySalesId($id);
    }

    /**
     * Gets a single sale item for editing purposes.
     * @param int $sale_item_id The ID of the sale item.
     * @return array|null Sale item data or null.
     */
    public function getSaleItemByIdForEdit($sale_item_id) {
        $id = filter_var($sale_item_id, FILTER_VALIDATE_INT);
        if (!$id) {
            return null;
        }
        return $this->saleItemModel->getSaleItemById($id); // This model method already joins with product
    }

    /**
     * Gets all products for populating dropdowns in forms.
     * @return array List of all products.
     */
    public function getAllProductsForDropdown() {
        return $this->productModel->getAllProducts(); // Assuming ProductModel has this method
    }
}
?>
