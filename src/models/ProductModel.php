<?php
class ProductModel {
    private $conn; 

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    public function createProduct($data) {
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
        $sql = "SELECT product_id, name, description, price, quantity, unit FROM product";
        
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
        $sql = "SELECT product_id, name, description, price, quantity, unit FROM product WHERE product_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - getById Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();
            $stmt->close();
            return $product; // Akan null jika tidak ditemukan
        } else {
            error_log("ProductModel - getById Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    public function updateProduct($product_id, $data) {
        $sql = "
            UPDATE product
            SET name = ?, description = ?, price = ?, quantity = ?, unit = ?
            WHERE product_id = ?;
        ";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("ProductModel - Update Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        
        $stmt->bind_param(
            "ssiisi", // s(name), s(description), i(price), i(quantity), s(unit), i(product_id)
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
            // Error bisa terjadi karena foreign key constraint jika produk masih digunakan
            $stmt->close();
            return false;
        }
    }
}
?>