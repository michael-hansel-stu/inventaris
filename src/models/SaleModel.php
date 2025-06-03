<<<<<<< HEAD
<?php
require_once __DIR__ . '/ProductModel.php'; // Pastikan ProductModel tersedia

class SaleModel {
    private $conn;
    private $productModel;

    public function __construct(mysqli $db, ProductModel $productModel) {
        $this->conn = $db;
        $this->productModel = $productModel;
    }

    /**
     * Membuat record penjualan baru beserta item-itemnya dan mengurangi stok produk.
     * @param array $saleData Data penjualan utama (mis: 'date').
     * @param array $itemsData Array dari item penjualan (mis: ['product_id', 'quantity', 'unit_price', 'total_price']).
     * @return int|false ID penjualan baru jika berhasil, false jika gagal.
     * @throws Exception Jika terjadi kesalahan selama transaksi.
     */
    public function createSale(array $saleData, array $itemsData) {
        $this->conn->begin_transaction();

        try {
            // Menggunakan nama tabel 'sales' (lowercase) sesuai database Anda
            $sqlSale = "INSERT INTO sales (date) VALUES (?)"; 
            $stmtSale = $this->conn->prepare($sqlSale);
            if (!$stmtSale) {
                throw new Exception("Gagal menyiapkan statement untuk tabel sales: " . $this->conn->error);
            }
            $saleDate = $saleData['date'] ?? date('Y-m-d H:i:s');
            $stmtSale->bind_param("s", $saleDate);
            if (!$stmtSale->execute()) {
                throw new Exception("Gagal mengeksekusi statement untuk tabel sales: " . $stmtSale->error);
            }
            $saleId = $stmtSale->insert_id; // Ini akan menjadi nilai untuk 'sale_id' di tabel 'sale_item'
            $stmtSale->close();

            // Menggunakan nama tabel 'sale_item' dan kolom 'sale_id' untuk foreign key
            $sqlSaleItem = "INSERT INTO sale_item (sale_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
            $stmtSaleItem = $this->conn->prepare($sqlSaleItem);
            $sqlSaleItem = "INSERT INTO sale_item (sale_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
$stmtSaleItem = $this->conn->prepare($sqlSaleItem);
if (!$stmtSaleItem) {
    throw new Exception("Gagal menyiapkan statement untuk tabel sale_item: " . $this->conn->error);
}

foreach ($itemsData as $item) {
    $stmtSaleItem->bind_param(
        "iiid", 
        $saleId, 
        $item['product_id'],
        $item['quantity'],
        $item['total_price'] 
    );
                if (!$stmtSaleItem->execute()) {
                    throw new Exception("Gagal mengeksekusi statement untuk sale_item (Produk ID: {$item['product_id']}): " . $stmtSaleItem->error);
                }

                // Pastikan ProductModel juga menggunakan nama tabel 'product' yang benar
                if (!$this->productModel->decreaseProductStock($item['product_id'], $item['quantity'])) {
                    $productInfo = $this->productModel->getProductById($item['product_id']);
                    $productIdentifier = $productInfo ? $productInfo['name'] : "ID " . $item['product_id'];
                    throw new Exception("Gagal mengurangi stok untuk produk: {$productIdentifier}. Stok tidak mencukupi atau terjadi kesalahan lain.");
                }
            }
            $stmtSaleItem->close();

            $this->conn->commit();
            return $saleId;

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("SaleModel - createSale transaksi gagal: " . $e->getMessage());
            throw $e; 
        }
    }

    /**
     * Mengambil semua data penjualan dengan ringkasan.
     * @return array List penjualan.
     */
    public function getAllSalesWithDetails() {
        // Menggunakan nama tabel 'sales' dan 'sale_item'.
        // Menggunakan 'si.sale_id' untuk join sesuai dengan struktur tabel Anda.
        $sql = "SELECT
                    s.sales_id,
                    s.date AS sale_date,
                    COUNT(si.sale_item_id) AS total_unique_items,
                    SUM(si.quantity) AS total_quantity_items,
                    SUM(si.total_price) AS grand_total
                FROM sales s 
                LEFT JOIN sale_item si ON s.sales_id = si.sale_id
                GROUP BY s.sales_id, s.date";
        
        $result = $this->conn->query($sql); 
        $sales = [];
        if ($result) {
            while($row = $result->fetch_assoc()) {
                $sales[] = $row;
            }
            $result->free();
        } else {
            error_log("SaleModel - getAllSalesWithDetails Query failed: (" . $this->conn->errno . ") " . $this->conn->error . " SQL: " . $sql);
        }
        return $sales;
    }

    /**
     * Mengambil detail penjualan berdasarkan ID beserta item-itemnya.
     * @param int $saleId ID Penjualan.
     * @return array|null Data penjualan dan itemnya, atau null jika tidak ditemukan.
     */
    public function getSaleByIdWithItems(int $saleId) {
        $sale = null;
        $items = [];

        // Menggunakan nama tabel 'sales'
        $sqlSale = "SELECT sales_id, date FROM sales WHERE sales_id = ? LIMIT 1"; 
        $stmtSale = $this->conn->prepare($sqlSale);
        if (!$stmtSale) {
             error_log("SaleModel - getSaleById (Sale) Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
             return null;
        }
        $stmtSale->bind_param("i", $saleId);
        if ($stmtSale->execute()) {
            $resultSale = $stmtSale->get_result();
            $sale = $resultSale->fetch_assoc();
        } else {
            error_log("SaleModel - getSaleById (Sale) Execute failed: (" . $stmtSale->errno . ") " . $stmtSale->error);
        }
        $stmtSale->close();

        if (!$sale) {
            return null; 
        }

        // Menggunakan nama tabel 'sale_item' dan 'product'.
        // Menggunakan 'si.sale_id' di WHERE clause sesuai struktur tabel Anda.
        $sqlItems = "SELECT
                        si.sale_item_id,
                        si.product_id,
                        p.name AS product_name,
                        p.unit AS product_unit,
                        si.quantity,
                        (si.total_price / CASE WHEN si.quantity = 0 THEN 1 ELSE si.quantity END) AS unit_price, # Hindari division by zero
                        si.total_price
                    FROM sale_item si 
                    JOIN product p ON si.product_id = p.product_id 
                    WHERE si.sale_id = ? 
                    ORDER BY p.name ASC";
        
        $stmtItems = $this->conn->prepare($sqlItems);
        if (!$stmtItems) {
            error_log("SaleModel - getSaleById (Items) Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
        } else {
            $stmtItems->bind_param("i", $saleId);
            if ($stmtItems->execute()) {
                $resultItems = $stmtItems->get_result();
                while($row = $resultItems->fetch_assoc()) {
                    $items[] = $row;
                }
            } else {
                error_log("SaleModel - getSaleById (Items) Execute failed: (" . $stmtItems->errno . ") " . $stmtItems->error);
            }
            $stmtItems->close();
        }
        
        $sale['total_quantity_items'] = 0;
        $sale['grand_total'] = 0;
        if (!empty($items)) { 
            foreach($items as $item) {
                if (isset($item['quantity'])) $sale['total_quantity_items'] += $item['quantity'];
                if (isset($item['total_price'])) $sale['grand_total'] += $item['total_price'];
            }
        }

        return ['sale' => $sale, 'items' => $items];
    }

    /**
     * Menghapus data penjualan dan item-item terkait.
     * Juga mengembalikan stok produk.
     * @param int $saleId ID penjualan yang akan dihapus.
     * @return bool True jika berhasil, false jika gagal.
     * @throws Exception Jika terjadi kesalahan.
     */
    public function deleteSale(int $saleId) {
        $saleDetails = $this->getSaleByIdWithItems($saleId);

        $this->conn->begin_transaction();
        try {
            // Kembalikan stok produk jika ada item terjual
            if ($saleDetails && !empty($saleDetails['items'])) {
                foreach ($saleDetails['items'] as $item) {
                    $currentProduct = $this->productModel->getProductById($item['product_id']);
                    if ($currentProduct) {
                        $dataToUpdate = [
                            'name' => $currentProduct['name'],
                            'description' => $currentProduct['description'],
                            'price' => $currentProduct['price'],
                            'quantity' => $currentProduct['quantity'] + $item['quantity'], 
                            'unit' => $currentProduct['unit']
                        ];
                        if (!$this->productModel->updateProduct($item['product_id'], $dataToUpdate)) {
                             throw new Exception("Gagal mengembalikan stok untuk produk ID: " . $item['product_id']);
                        }
                    } else {
                        error_log("Produk ID {$item['product_id']} tidak ditemukan saat mencoba mengembalikan stok untuk penjualan ID: {$saleId}.");
                    }
                }
            }

            // Hapus item dari sale_item
            $sqlDeleteItems = "DELETE FROM sale_item WHERE sale_id = ?";
            $stmtDeleteItems = $this->conn->prepare($sqlDeleteItems);
            if (!$stmtDeleteItems) {
                throw new Exception("Gagal menyiapkan statement untuk menghapus sale_item: " . $this->conn->error);
            }
            $stmtDeleteItems->bind_param("i", $saleId);
            $stmtDeleteItems->execute(); 
            $stmtDeleteItems->close();

            // Hapus dari sales
            $sqlDeleteSale = "DELETE FROM sales WHERE sales_id = ?";
            $stmtDeleteSale = $this->conn->prepare($sqlDeleteSale);
            if (!$stmtDeleteSale) {
                throw new Exception("Gagal menyiapkan statement untuk menghapus sales: " . $this->conn->error);
            }
            $stmtDeleteSale->bind_param("i", $saleId);
            if (!$stmtDeleteSale->execute()) {
                throw new Exception("Gagal menghapus dari tabel sales: " . $stmtDeleteSale->error);
            }
            $affectedRows = $stmtDeleteSale->affected_rows;
            $stmtDeleteSale->close();

            $this->conn->commit();
            return $affectedRows > 0; 

        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("SaleModel - deleteSale transaction failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Memperbarui data penjualan yang ada. (IMPLEMENTASI DASAR - PERLU DIKEMBANGKAN)
     * @param int $saleId ID penjualan yang akan diupdate.
     * @param array $saleData Data utama penjualan yang baru (mis: 'date').
     * @param array $newItemsData Array dari item penjualan yang baru. Setiap item:
     * ['sale_item_id' => ID lama atau 0, 'product_id' => X, 'quantity' => Y]
     * @return bool True jika berhasil, false jika gagal.
     * @throws Exception Jika terjadi kesalahan.
     */
    public function updateSale(int $saleId, array $saleData, array $newItemsData) {
        $this->conn->begin_transaction();
        try {
            // 1. Update tabel 'sales' (misalnya tanggal)
            $saleDate = $saleData['date'] ?? null; 
            if (!$saleDate) { 
                throw new Exception("Tanggal penjualan tidak boleh kosong saat update.");
            }
            
            $currentSaleDetails = $this->getSaleByIdWithItems($saleId); 
            $originalTime = '00:00:00'; 
            if ($currentSaleDetails && isset($currentSaleDetails['sale']['date'])) {
                $originalTime = date('H:i:s', strtotime($currentSaleDetails['sale']['date']));
            }
            $saleDateTimeToUpdate = $saleDate . ' ' . $originalTime;


            $sqlUpdateSale = "UPDATE sales SET date = ? WHERE sales_id = ?";
            $stmtUpdateSale = $this->conn->prepare($sqlUpdateSale);
            if (!$stmtUpdateSale) throw new Exception("Gagal menyiapkan statement update sales: " . $this->conn->error);
            $stmtUpdateSale->bind_param("si", $saleDateTimeToUpdate, $saleId);
            if (!$stmtUpdateSale->execute()) throw new Exception("Gagal update tabel sales: " . $stmtUpdateSale->error);
            $stmtUpdateSale->close();

            // 2. Dapatkan item-item penjualan yang lama
            $oldItemsQuery = "SELECT sale_item_id, product_id, quantity FROM sale_item WHERE sale_id = ?";
            $stmtOldItems = $this->conn->prepare($oldItemsQuery);
            if (!$stmtOldItems) throw new Exception("Gagal prepare old items: " . $this->conn->error);
            $stmtOldItems->bind_param("i", $saleId);
            $stmtOldItems->execute();
            $resultOldItems = $stmtOldItems->get_result();
            $oldItemsMap = []; 
            while ($row = $resultOldItems->fetch_assoc()) {
                $oldItemsMap[$row['sale_item_id']] = $row;
            }
            $stmtOldItems->close();

            $processedNewItemSaleItemIds = []; 

            // 3. Proses item-item dari form ($newItemsData)
            foreach ($newItemsData as $newItem) {
                $saleItemId = $newItem['sale_item_id'] ?? 0; 
                $productId = $newItem['product_id'];
                $newQuantity = $newItem['quantity'];

                $productDetails = $this->productModel->getProductById($productId);
                if (!$productDetails) throw new Exception("Produk ID {$productId} tidak ditemukan.");
                $unitPrice = $productDetails['price'];
                $totalPrice = $unitPrice * $newQuantity;
                
                $originalProductStock = $productDetails['quantity']; // Stok produk saat ini di DB sebelum penyesuaian apapun di transaksi ini

                if ($saleItemId != 0 && isset($oldItemsMap[$saleItemId])) { // Item ini ada sebelumnya, berarti UPDATE
                    $oldItem = $oldItemsMap[$saleItemId];
                    $currentProductStock = $this->productModel->getProductById($productId)['quantity']; // Stok produk terkini
                    $stockToAdjust = 0;

                    if ($oldItem['product_id'] == $productId) { // Produk sama, kuantitas mungkin berubah
                        $stockToAdjust = $oldItem['quantity'] - $newQuantity; // Positif jika stok harus ditambah, negatif jika stok harus dikurang
                    } else { // Produk diganti
                        // Kembalikan stok produk lama
                         $oldProductData = $this->productModel->getProductById($oldItem['product_id']);
                         if ($oldProductData) {
                            $this->productModel->updateProduct($oldItem['product_id'], ['quantity' => $oldProductData['quantity'] + $oldItem['quantity']]); // Kembalikan penuh stok lama
                         }
                        // Stok untuk produk baru akan dikurangi sejumlah $newQuantity
                        $stockToAdjust = -$newQuantity; 
                    }
                    
                    // Sesuaikan stok untuk produk saat ini (yang baru atau yang sama)
                    $finalStockForCurrentProduct = $this->productModel->getProductById($productId)['quantity'] + $stockToAdjust; // Hitung berdasarkan stok terkini
                    if ($finalStockForCurrentProduct < 0) {
                         throw new Exception("Stok tidak mencukupi untuk produk ID {$productId} setelah penyesuaian.");
                    }
                    $this->productModel->updateProduct($productId, ['quantity' => $finalStockForCurrentProduct]);
                    
                    // Update item di sale_item
                    $sqlUpdateItem = "UPDATE sale_item SET product_id = ?, quantity = ?, total_price = ? WHERE sale_item_id = ?";
                    $stmtUpdateItem = $this->conn->prepare($sqlUpdateItem);
                    if (!$stmtUpdateItem) throw new Exception("Gagal prepare update sale_item: " . $this->conn->error);
                    $stmtUpdateItem->bind_param("iidi", $productId, $newQuantity, $totalPrice, $saleItemId);
                    if (!$stmtUpdateItem->execute()) throw new Exception("Gagal update sale_item ID {$saleItemId}: " . $stmtUpdateItem->error);
                    $stmtUpdateItem->close();
                    $processedNewItemSaleItemIds[] = $saleItemId;
                    unset($oldItemsMap[$saleItemId]); 

                } else { // Item ini BARU, berarti INSERT
                    $currentProductStock = $this->productModel->getProductById($productId)['quantity'];
                    if ($currentProductStock < $newQuantity) {
                        throw new Exception("Stok tidak cukup untuk produk baru ID {$productId}");
                    }
                    $this->productModel->decreaseProductStock($productId, $newQuantity); 

                     $sqlInsertItem = "INSERT INTO sale_item (sale_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
    $stmtInsertItem = $this->conn->prepare($sqlInsertItem);
    if (!$stmtInsertItem) throw new Exception("Gagal prepare insert sale_item: " . $this->conn->error);
    $stmtInsertItem->bind_param("iiid", $saleId, $productId, $newQuantity, $totalPrice);
                    if (!$stmtInsertItem->execute()) throw new Exception("Gagal insert sale_item baru: " . $stmtInsertItem->error);
                    $stmtInsertItem->close();
                }
            }

            // 4. Hapus item-item lama yang tidak ada di $newItemsData (yang tersisa di $oldItemsMap)
            foreach ($oldItemsMap as $oldSaleItemId => $oldItemData) {
                 $oldProductData = $this->productModel->getProductById($oldItemData['product_id']);
                 if ($oldProductData) {
                    $dataToUpdateOldProduct = [
                        'name' => $oldProductData['name'],
                        'description' => $oldProductData['description'],
                        'price' => $oldProductData['price'],
                        'quantity' => $oldProductData['quantity'] + $oldItemData['quantity'],
                        'unit' => $oldProductData['unit']
                    ];
                    $this->productModel->updateProduct($oldItemData['product_id'], $dataToUpdateOldProduct);
                 }

                $sqlDeleteItem = "DELETE FROM sale_item WHERE sale_item_id = ?";
                $stmtDeleteItem = $this->conn->prepare($sqlDeleteItem);
                if (!$stmtDeleteItem) throw new Exception("Gagal prepare delete old sale_item: " . $this->conn->error);
                $stmtDeleteItem->bind_param("i", $oldSaleItemId);
                if (!$stmtDeleteItem->execute()) throw new Exception("Gagal delete old sale_item ID {$oldSaleItemId}: " . $stmtDeleteItem->error);
                $stmtDeleteItem->close();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollback();
            error_log("SaleModel - updateSale transaction failed for sale ID {$saleId}: " . $e->getMessage());
            throw $e;
        }
    }

}
?>
=======

>>>>>>> bb497abaa63a7e8a61c34a8dd9a667eb0851ff74
