<?php
require_once __DIR__ . '/../../app/controllers/ProductController.php';
$controller = new ProductController();
$productos = $controller->listar();
include '../../templates/header.php';
?>
<h2>Listado de Productos</h2>
<a href="create.php">➕ Agregar nuevo producto</a>
<table border="1" cellpadding="6">
  <tr><th>ID</th><th>Nombre</th><th>Categoría</th><th>Catálogo</th><th>Precio</th><th>Acciones</th></tr>
  <?php while ($p = $productos->fetch_assoc()): ?>
  <tr>
    <td><?= $p['id'] ?></td>
    <td><?= $p['name'] ?></td>
    <td><?= $p['category'] ?></td>
    <td><?= $p['catalog'] ?></td>
    <td><?= number_format($p['price'], 2) ?></td>
    <td>
      <a href="update.php?id=<?= $p['id'] ?>">Editar</a> |
      <a href="delete.php?id=<?= $p['id'] ?>">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php include '../../templates/footer.php'; ?>
