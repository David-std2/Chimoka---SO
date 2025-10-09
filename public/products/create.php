<?php
require_once __DIR__ . '/../../app/controllers/ProductController.php';
$controller = new ProductController();
$categorias = $controller->obtenerCategorias();
$catalogos = $controller->obtenerCatalogos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->crear($_POST['category_id'], $_POST['catalog_id'], $_POST['name'], $_POST['description'], $_POST['price']);
}

include '../../templates/header.php';
?>
<h2>Agregar Producto</h2>
<form method="POST">
  <label>Nombre:</label><br>
  <input type="text" name="name" required><br><br>

  <label>Descripción:</label><br>
  <textarea name="description" rows="3" cols="40"></textarea><br><br>

  <label>Categoría:</label><br>
  <select name="category_id">
    <?php while ($c = $categorias->fetch_assoc()): ?>
      <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Catálogo:</label><br>
  <select name="catalog_id">
    <?php while ($cat = $catalogos->fetch_assoc()): ?>
      <option value="<?= $cat['id'] ?>"><?= $cat['title'] ?></option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Precio (S/):</label><br>
  <input type="number" step="0.01" name="price" required><br><br>

  <button type="submit">Guardar</button>
</form>
<?php include '../../templates/footer.php'; ?>
