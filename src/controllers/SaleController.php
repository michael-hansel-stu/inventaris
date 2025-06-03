<<<<<<< HEAD
<?php
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../lib/functions.php'; 

class SaleController {
    private $saleModel;
    private $productModel;

    public function __construct(SaleModel $saleModel, ProductModel $productModel) {
        $this->saleModel = $saleModel;
        $this->productModel = $productModel;
    }

    public function handleAddSale(array $postData) {
        $productIds = $postData['product_ids'] ?? [];
        $quantities = $postData['quantities'] ?? [];
        $saleDateInput = $postData['sale_date'] ?? date('Y-m-d'); 
        $saleTimeInput = $postData['sale_time'] ?? date('H:i:s'); // Waktu diambil dari form tambah

        if (empty($productIds) || empty($quantities) || count($productIds) !== count($quantities)) {
            return ['error' => true, 'message' => 'Data item penjualan tidak lengkap atau tidak cocok. Pastikan semua produk memiliki kuantitas.'];
        }

        if (!isValidDate($saleDateInput, 'Y-m-d')) {
            return ['error' => true, 'message' => 'Format tanggal penjualan tidak valid. Gunakan YYYY-MM-DD.'];
        }
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)(:([0-5]\d))?$/', $saleTimeInput)) {
             return ['error' => true, 'message' => 'Format waktu penjualan tidak valid. Gunakan HH:MM atau HH:MM:SS.'];
        }
        $saleDateTime = $saleDateInput . ' ' . $saleTimeInput;
        if (strlen($saleTimeInput) == 5) $saleDateTime .= ':00';


        $saleItemsData = [];
        for ($i = 0; $i < count($productIds); $i++) {
            $productId = filter_var($productIds[$i], FILTER_VALIDATE_INT);
            $quantity = filter_var($quantities[$i], FILTER_VALIDATE_INT);

            if (empty($productId) || empty($quantity) || $quantity < 1) { 
                continue; 
            }

            $product = $this->productModel->getProductById($productId);
            if (!$product) {
                return ['error' => true, 'message' => "Produk dengan ID {$productId} tidak ditemukan."];
            }

            if ($product['quantity'] < $quantity) {
                return ['error' => true, 'message' => "Stok tidak mencukupi untuk produk \"{$product['name']}\". Sisa stok: {$product['quantity']}, diminta: {$quantity}."];
            }

            $unitPrice = (float) $product['price'];
            $totalItemPrice = $unitPrice * $quantity;

            $saleItemsData[] = [
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice, 
                'total_price' => $totalItemPrice
            ];
        }

        if (empty($saleItemsData)) {
            return ['error' => true, 'message' => 'Tidak ada item yang valid untuk dijual. Mohon periksa kembali input produk dan kuantitas.'];
        }

        $saleRecordData = [
            'date' => $saleDateTime,
        ];
        
        try {
            $saleId = $this->saleModel->createSale($saleRecordData, $saleItemsData);
            return ['success' => true, 'message' => 'Penjualan berhasil dicatat dengan ID: ' . $saleId . '.'];
        } catch (Exception $e) {
            return ['error' => true, 'message' => 'Gagal mencatat penjualan: ' . $e->getMessage()];
        }
    }

    public function getAllSalesForView() {
        return $this->saleModel->getAllSalesWithDetails();
    }

    public function getSaleDetailsForView($saleId) {
        $id = filter_var($saleId, FILTER_VALIDATE_INT);
        if (!$id) {
            return null;
        }
        return $this->saleModel->getSaleByIdWithItems($id);
    }

    public function handleDeleteSale(int $saleId) {
        try {
            if ($this->saleModel->deleteSale($saleId)) {
                return ['success' => true, 'message' => "Penjualan ID: {$saleId} dan item terkait berhasil dihapus. Stok produk telah dikembalikan."];
            } else {
                return ['error' => true, 'message' => "Gagal menghapus penjualan ID: {$saleId}. Penjualan mungkin sudah dihapus atau tidak ditemukan."];
            }
        } catch (Exception $e) {
            return ['error' => true, 'message' => "Gagal menghapus penjualan ID: {$saleId}. Terjadi kesalahan: " . $e->getMessage()];
        }
    }

    /**
     * Menangani permintaan pembaruan penjualan.
     * @param int $saleId ID penjualan yang akan diupdate.
     * @param array $postData Data dari form edit.
     * @return array Hasil operasi.
     */
    public function handleUpdateSale(int $saleId, array $postData) {
        $saleDateInput = $postData['sale_date'] ?? null;
        // Input Waktu ('sale_time') sudah dihapus dari form edit_sale.php, 
        // jadi kita tidak mengambilnya dari $_POST di sini.
        // Penanganan waktu (mempertahankan waktu asli atau set ke 00:00:00) dilakukan di Model.
        
        if (!$saleDateInput || !isValidDate($saleDateInput, 'Y-m-d')) {
            return ['error' => true, 'message' => 'Format tanggal penjualan tidak valid. Gunakan YYYY-MM-DD.'];
        }
        
        $saleDataForUpdate = ['date' => $saleDateInput]; // Hanya tanggal yang dikirim ke model untuk data utama penjualan

        $newItemsData = [];
        $itemSaleItemIds = $postData['item_sale_item_ids'] ?? []; // ID item lama, 0 jika baru
        $itemProductIds = $postData['item_product_ids'] ?? [];
        $itemQuantities = $postData['item_quantities'] ?? [];

        if (isset($postData['item_product_ids']) && is_array($postData['item_product_ids'])) {
            for ($i = 0; $i < count($postData['item_product_ids']); $i++) {
                $productId = filter_var($itemProductIds[$i], FILTER_VALIDATE_INT);
                $quantity = filter_var($itemQuantities[$i], FILTER_VALIDATE_INT);
                $saleItemId = isset($itemSaleItemIds[$i]) ? filter_var($itemSaleItemIds[$i], FILTER_VALIDATE_INT) : 0;

                if ($productId && $quantity > 0) {
                    // Validasi produk (seperti harga & stok awal) bisa dilakukan di sini sebelum ke model,
                    // tapi model juga akan melakukan validasi lebih lanjut.
                    $newItemsData[] = [
                        'sale_item_id' => $saleItemId, 
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        // unit_price dan total_price akan dihitung ulang di Model berdasarkan harga produk saat ini.
                    ];
                } else if ($saleItemId != 0 && (empty($productId) || $quantity <= 0) ) { 
                    // Ini menandakan item yang sudah ada ingin dikosongkan/dihapus dari form,
                    // Model akan menghapusnya karena tidak ada di $newItemsData yang valid.
                    // Atau bisa ditandai untuk dihapus secara eksplisit jika logic model memerlukannya.
                }
            }
        }
        
        // Jika tidak ada item sama sekali setelah pemrosesan, bisa dianggap error atau penjualan kosong (tergantung business logic)
        // if (empty($newItemsData) && !empty($itemProductIds)) { 
        //      return ['error' => true, 'message' => 'Tidak ada item yang valid untuk diupdate/ditambahkan.'];
        // }

        try {
            $result = $this->saleModel->updateSale($saleId, $saleDataForUpdate, $newItemsData);
            if ($result) {
                return ['success' => true, 'message' => "Penjualan ID: {$saleId} berhasil diperbarui."];
            } else {
                return ['error' => true, 'message' => "Gagal memperbarui penjualan ID: {$saleId} (kemungkinan tidak ada perubahan atau error di model)."];
            }
        } catch (Exception $e) {
            return ['error' => true, 'message' => "Gagal memperbarui penjualan ID: {$saleId}. Error: " . $e->getMessage()];
        }
    }
}
?>
=======

>>>>>>> bb497abaa63a7e8a61c34a8dd9a667eb0851ff74
