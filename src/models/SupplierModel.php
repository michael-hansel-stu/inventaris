<?php

class SupplierModel {
    private $conn;
    private $table_name = "suppliers";

    public function __construct(mysqli $db) {
        $this->conn = $db;
    }

    /**
     * Membuat supplier baru.
     * @param array $data Data supplier (name, contact_person, phone, email, address).
     * @return int|false ID supplier yang baru dibuat atau false jika gagal.
     */
    public function createSupplier(array $data) {
        $query = "INSERT INTO " . $this->table_name . " (name, contact_person, phone, email, address, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            // Error saat prepare statement
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        // Tidak perlu sanitizeInput di sini karena prepared statement menangani escaping.
        // Sanitasi sebaiknya dilakukan di Controller sebelum memanggil Model.
        $stmt->bind_param(
            "sssss",
            $data['name'],
            $data['contact_person'],
            $data['phone'],
            $data['email'],
            $data['address']
        );

        if ($stmt->execute()) {
            return $stmt->insert_id;
        } else {
            // Error saat execute statement
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            return false;
        }
    }

    /**
     * Mendapatkan semua data supplier.
     * @return array|false Array data supplier atau false jika gagal.
     */
    public function getAllSuppliers() {
        $query = "SELECT id, name, contact_person, phone, email, address, created_at, updated_at FROM " . $this->table_name . " ORDER BY name ASC";
        
        $result = $this->conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // Error saat query
            error_log("Query failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
    }

    /**
     * Mendapatkan data supplier berdasarkan ID.
     * @param int $id ID supplier.
     * @return array|null|false Data supplier jika ditemukan, null jika tidak, false jika error.
     */
    public function getSupplierById(int $id) {
        $query = "SELECT id, name, contact_person, phone, email, address, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            return $result->fetch_assoc(); // Mengembalikan satu baris data atau null jika tidak ditemukan
        } else {
            error_log("Execute/get_result failed: (" . $stmt->errno . ") " . $stmt->error);
            return false;
        }
    }

    /**
     * Memperbarui data supplier.
     * @param int $id ID supplier yang akan diperbarui.
     * @param array $data Data supplier yang baru (name, contact_person, phone, email, address).
     * @return bool True jika berhasil, false jika gagal.
     */
    public function updateSupplier(int $id, array $data) {
        $query = "UPDATE " . $this->table_name . " SET name = ?, contact_person = ?, phone = ?, email = ?, address = ?, updated_at = NOW() WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param(
            "sssssi",
            $data['name'],
            $data['contact_person'],
            $data['phone'],
            $data['email'],
            $data['address'],
            $id
        );

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0; // Bisa jadi tidak ada row yang terupdate jika data sama
        } else {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            return false;
        }
    }

    /**
     * Menghapus supplier berdasarkan ID.
     * @param int $id ID supplier.
     * @return bool True jika berhasil, false jika gagal.
     */
    public function deleteSupplier(int $id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            error_log("Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            return false;
        }
    }
}
?>
