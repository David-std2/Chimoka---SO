<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Catalog.php';
require_once __DIR__ . '/../includes/functions.php';

class ProductController {

    public function listar() {
        return Product::getAll();
    }
}
?>
