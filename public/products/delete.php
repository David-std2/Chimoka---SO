<?php
require_once __DIR__ . '/../../app/controllers/ProductController.php';
$controller = new ProductController();

$id = $_GET['id'] ?? null;
if ($id) {
    $controller->eliminar($id);
}
?>
