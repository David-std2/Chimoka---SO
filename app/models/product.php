<?php
require_once __DIR__ . '/../includes/db_connect.php';

class Product {
    public static function getAll() {
        global $conexion;
        $sql = "SELECT p.id, p.name, p.description, p.price, 
                       c.name AS category, cat.title AS catalog
                FROM Product p
                JOIN Category c ON p.category_id = c.id
                JOIN Catalog cat ON p.catalog_id = cat.id
                ORDER BY p.id";
        return $conexion->query($sql);
    }

    public static function getById($id) {
        global $conexion;
        $stmt = $conexion->prepare("SELECT * FROM Product WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function create($category_id, $catalog_id, $name, $description, $price) {
        global $conexion;
        $stmt = $conexion->prepare("INSERT INTO Product (category_id, catalog_id, name, description, price) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iissd", $category_id, $catalog_id, $name, $description, $price);
        return $stmt->execute();
    }

    public static function update($id, $category_id, $catalog_id, $name, $description, $price) {
        global $conexion;
        $stmt = $conexion->prepare("UPDATE Product 
                                    SET category_id=?, catalog_id=?, name=?, description=?, price=? 
                                    WHERE id=?");
        $stmt->bind_param("iissdi", $category_id, $catalog_id, $name, $description, $price, $id);
        return $stmt->execute();
    }

    public static function delete($id) {
        global $conexion;
        $stmt = $conexion->prepare("DELETE FROM Product WHERE id=?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>
