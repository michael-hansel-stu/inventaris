<?php
class ProductModel {
    private $conn; 

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function createProduct($data) {
        // Pastikan nama tabel 'product' sudah benar
        $sql = "INSERT INTO product (name, description, price, quantity, unit) VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - Create Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param(
            "ssiis", // s(name), s(description), i(price), i(quantity), s(unit)
            $data['name'],
            $data['description'],
            $data['price'],
            $data['quantity'],
            $data['unit']
        );

        if ($stmt->execute()) {
            $new_id = $this->conn->insert_id;
            $stmt->close();
            return $new_id;
        } else {
            error_log("ProductModel - Create Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function getAllProducts() {
        $products = [];
        // Pastikan nama tabel 'product' sudah benar
        $sql = "SELECT product_id, name, description, price, quantity, unit FROM product ORDER BY name ASC";
        
        $result = $this->conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $products[] = $row;
            }
            $result->free();
        } else {
            error_log("ProductModel - getAllProducts Query failed: (" . $this->conn->errno . ") " . $this->conn->error);
        }
        return $products;
    }

    public function getProductById($product_id) {
        // Pastikan nama tabel 'product' sudah benar
        $sql = "SELECT product_id, name, description, price, quantity, unit FROM product WHERE product_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - getById Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $product = $result->fetch_assoc(); // Akan null jika tidak ditemukan
            $stmt->close();
            return $product; 
        } else {
            error_log("ProductModel - getById Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function updateProduct($product_id, $data) {
        // Pastikan nama tabel 'product' sudah benar
        $sql = "UPDATE product
                SET name = ?, description = ?, price = ?, quantity = ?, unit = ?
                WHERE product_id = ?;";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - Update Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param(
            "ssiisi", 
            $data['name'],
            $data['description'],
            $data['price'],
            $data['quantity'],
            $data['unit'],
            $product_id
        );

        if ($stmt->execute()) {
            $success = $stmt->affected_rows >= 0; 
            $stmt->close();
            return $success;
        } else {
            error_log("ProductModel - Update Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    public function deleteProduct($product_id) {
        // Pastikan nama tabel 'product' sudah benar
        $sql = "DELETE FROM product WHERE product_id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - Delete Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $success = $stmt->affected_rows > 0; // True jika ada baris yang terhapus
            $stmt->close();
            return $success;
        } else {
            error_log("ProductModel - Delete Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Mengurangi stok produk.
     * @param int $product_id ID produk.
     * @param int $quantity_to_decrease Jumlah yang akan dikurangi.
     * @return bool True jika berhasil, false jika gagal (mis: stok tidak cukup, produk tidak ada).
     */
    public function decreaseProductStock($product_id, $quantity_to_decrease) {
        $product = $this->getProductById($product_id); 
        if (!$product) {
            error_log("ProductModel - decreaseProductStock: Produk ID {$product_id} tidak ditemukan.");
            return false; 
        }

        if (!isset($product['quantity'])) {
             error_log("ProductModel - decreaseProductStock: Kuantitas tidak ditemukan untuk Produk ID {$product_id}.");
             return false;
        }

        if ($product['quantity'] < $quantity_to_decrease) {
            error_log("ProductModel - decreaseProductStock: Stok tidak mencukupi untuk Produk ID {$product_id}. Tersedia: {$product['quantity']}, Diminta: {$quantity_to_decrease}");
            return false; 
        }

        // Pastikan nama tabel 'product' sudah benar
        $sql = "UPDATE product SET quantity = quantity - ? WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - decreaseProductStock Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param("ii", $quantity_to_decrease, $product_id);

        if ($stmt->execute()) {
            $success = $stmt->affected_rows >= 0; 
            if ($quantity_to_decrease > 0) { 
                $success = $stmt->affected_rows > 0;
            }
            $stmt->close();
            return $success;
        } else {
            error_log("ProductModel - decreaseProductStock Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }
}
?>
