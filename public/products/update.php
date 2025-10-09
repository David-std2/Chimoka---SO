<?php
require_once __DIR__ . '/../../app/controllers/ProductController.php';
$controller = new ProductController();
$id = $_GET['id'];
$producto = $controller->obtenerPorId($id);
$categorias = $controller->obtenerCategorias();
$catalogos = $controller->obtenerCatalogos();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->actualizar($id, $_POST['category_id'], $_POST['catalog_id'], $_POST['name'], $_POST['description'], $_POST['price']);
}

include '../../templates/header.php';
?>
<h2>Editar Producto</h2>
<form method="POST">
  <label>Nombre:</label><br>
  <input type="text" name="name" value="<?= $producto['name'] ?>"><br><br>

  <label>Descripción:</label><br>
  <textarea name="description"><?= $producto['description'] ?></textarea><br><br>

  <label>Categoría:</label><br>
  <select name="category_id">
    <?php while ($c = $categorias->fetch_assoc()): ?>
      <option value="<?= $c['id'] ?>" <?= $c['id'] == $producto['category_id'] ? 'selected' : '' ?>>
        <?= $c['name'] ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Catálogo:</label><br>
  <select name="catalog_id">
    <?php while ($cat = $catalogos->fetch_assoc()): ?>
      <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $producto['catalog_id'] ? 'selected' : '' ?>>
        <?= $cat['title'] ?>
      </option>
    <?php endwhile; ?>
  </select><br><br>

  <label>Precio (S/):</label><br>
  <input type="number" step="0.01" name="price" value="<?= $producto['price'] ?>"><br><br>

  <button type="submit">Actualizar</button>
</form>
<?php include '../../templates/footer.php'; ?>
