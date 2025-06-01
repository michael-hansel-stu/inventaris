<?php
// src/models/SaleItemModel.php

class SaleItemModel {
    private $conn;
    private $table_name = "saleitem";

    public function __construct($db_connection) {
        $this->conn = $db_connection;
    }

    /**
     * Creates a new sale item.
     * Also adjusts product quantity if inventory management is active.
     * @param array $data Associative array containing sale item data
     * (sales_id, product_id, quantity, total_price)
     * @return int|false The ID of the newly created sale item or false on failure.
     */
    public function createSaleItem($data) {
        $sql = "INSERT INTO " . $this->table_name . " (sales_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - Create Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        // Assuming total_price is pre-calculated and passed in $data
        $stmt->bind_param(
            "iiid", // i(sales_id), i(product_id), i(quantity), d(total_price - using double for price)
            $data['sales_id'],
            $data['product_id'],
            $data['quantity'],
            $data['total_price']
        );

        if ($stmt->execute()) {
            $new_id = $this->conn->insert_id;
            $stmt->close();
            // Optionally, update product stock here
            // $this->updateProductStock($data['product_id'], -$data['quantity']); // Decrease stock
            return $new_id;
        } else {
            error_log("SaleItemModel - Create Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Retrieves all sale items for a specific sales_id, including product details.
     * @param int $sales_id The ID of the sale.
     * @return array An array of sale items with product details.
     */
    public function getSaleItemsBySalesId($sales_id) {
        $items = [];
        $sql = "SELECT 
                    si.sale_item_id, 
                    si.sales_id, 
                    si.product_id, 
                    si.quantity, 
                    si.total_price,
                    p.name AS product_name,
                    p.unit AS product_unit,
                    (si.total_price / si.quantity) AS unit_price_at_sale 
                FROM " . $this->table_name . " si
                JOIN product p ON si.product_id = p.product_id
                WHERE si.sales_id = ?
                ORDER BY p.name ASC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - getBySalesId Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return $items;
        }

        $stmt->bind_param("i", $sales_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
            $result->free();
        } else {
            error_log("SaleItemModel - getBySalesId Execute failed: (" . $stmt->errno . ") " . $stmt->error);
        }
        $stmt->close();
        return $items;
    }

    /**
     * Retrieves a single sale item by its ID.
     * @param int $sale_item_id The ID of the sale item.
     * @return array|null The sale item data or null if not found.
     */
    public function getSaleItemById($sale_item_id) {
        $sql = "SELECT 
                    si.sale_item_id, 
                    si.sales_id, 
                    si.product_id, 
                    si.quantity, 
                    si.total_price,
                    p.name AS product_name,
                    p.unit AS product_unit
                FROM " . $this->table_name . " si
                JOIN product p ON si.product_id = p.product_id
                WHERE si.sale_item_id = ? LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - getById Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return null;
        }
        $stmt->bind_param("i", $sale_item_id);

        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $item = $result->fetch_assoc();
            $stmt->close();
            return $item;
        } else {
            error_log("SaleItemModel - getById Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return null;
        }
    }

    /**
     * Updates an existing sale item.
     * @param int $sale_item_id The ID of the sale item to update.
     * @param array $data Associative array with new data (e.g., product_id, quantity, total_price).
     * @return bool True on success, false on failure.
     */
    public function updateSaleItem($sale_item_id, $data) {
        // Note: When updating, you might need to manage product stock adjustments:
        // 1. Get old quantity.
        // 2. Calculate difference with new quantity.
        // 3. Update product stock.
        // This example keeps it simpler and focuses on the sale item itself.

        $sql = "UPDATE " . $this->table_name . " 
                SET product_id = ?, quantity = ?, total_price = ?
                WHERE sale_item_id = ?";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - Update Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param(
            "idii", // i(product_id), i(quantity), d(total_price), i(sale_item_id) - using double for price
            $data['product_id'],
            $data['quantity'],
            $data['total_price'],
            $sale_item_id
        );

        if ($stmt->execute()) {
            $success = $stmt->affected_rows >= 0; // True if query ran, even if no rows changed
            $stmt->close();
            return $success;
        } else {
            error_log("SaleItemModel - Update Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Deletes a sale item.
     * @param int $sale_item_id The ID of the sale item to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteSaleItem($sale_item_id) {
        // Optionally, revert product stock here by getting the item's quantity and product_id first.
        // $item = $this->getSaleItemById($sale_item_id);
        // if ($item) { $this->updateProductStock($item['product_id'], $item['quantity']); // Increase stock }

        $sql = "DELETE FROM " . $this->table_name . " WHERE sale_item_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - Delete Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $sale_item_id);

        if ($stmt->execute()) {
            $success = $stmt->affected_rows > 0;
            $stmt->close();
            return $success;
        } else {
            error_log("SaleItemModel - Delete Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    /**
     * Deletes all sale items associated with a specific sales_id.
     * Useful when deleting a parent Sale record.
     * @param int $sales_id The ID of the sale.
     * @return bool True if deletion was successful or no items needed deletion, false on error.
     */
    public function deleteSaleItemsBySalesId($sales_id) {
        // Similar to deleteSaleItem, you might want to revert stock for all items deleted.
        // This would involve fetching all items first, then looping through them to update stock.

        $sql = "DELETE FROM " . $this->table_name . " WHERE sales_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - DeleteBySalesId Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("i", $sales_id);

        if ($stmt->execute()) {
            // $stmt->affected_rows can be 0 if no items existed, which is still a "success"
            $stmt->close();
            return true;
        } else {
            error_log("SaleItemModel - DeleteBySalesId Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
    }

    // --- Optional: Product Stock Management ---
    // This should ideally be in ProductModel or a dedicated InventoryService.
    // For simplicity, a basic version is included here if you want to manage it directly.
    /*
    private function updateProductStock($product_id, $quantity_change) {
        // $quantity_change is positive to add stock, negative to remove stock
        $sql = "UPDATE product SET quantity = quantity + ? WHERE product_id = ?";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            error_log("SaleItemModel - UpdateProductStock Prepare failed: (" . $this->conn->errno . ") " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("ii", $quantity_change, $product_id);
        if (!$stmt->execute()) {
            error_log("SaleItemModel - UpdateProductStock Execute failed: (" . $stmt->errno . ") " . $stmt->error);
            $stmt->close();
            return false;
        }
        $stmt->close();
        return true;
    }
    */
}
?>
