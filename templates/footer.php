<?php
// templates/footer.php
// $tabla, $rows, $columns, $page, $pages, $limit, $total, $offset, $primaryKey
?>
              </tbody>
            </table>
          </div>

          <div class="progress-scroll my-3" id="progressScroll" aria-hidden="true">
            <div class="scroll-inner" id="scrollInner" style="width:0;"></div>
          </div>

          <div class="mt-3 footer-grid d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
              <div class="small text-muted">Registros por página</div>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown"><?= $limit ?></button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="<?= htmlspecialchars(url_set_limit(10)) ?>">10</a></li>
                  <li><a class="dropdown-item" href="<?= htmlspecialchars(url_set_limit(25)) ?>">25</a></li>
                  <li><a class="dropdown-item" href="<?= htmlspecialchars(url_set_limit(50)) ?>">50</a></li>
                </ul>
              </div>
            </div>

            <div class="d-flex justify-content-center">
              <ul class="pagination mb-0">
                <?php if ($tabla):
                  $prevHref = $page > 1 ? htmlspecialchars(url_set_page($page - 1)) : '#';
                  $nextHref = $page < $pages ? htmlspecialchars(url_set_page($page + 1)) : '#';
                ?>
                  <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $prevHref ?>">&lt; Anterior</a></li>
                  <?php
                    $maxVisible = 7;
                    $start = 1; $end = $pages;
                    if ($pages > $maxVisible) {
                      $half = floor($maxVisible / 2);
                      $start = max(1, $page - $half);
                      $end = min($pages, $start + $maxVisible - 1);
                      if ($end - $start + 1 < $maxVisible) $start = max(1, $end - $maxVisible + 1);
                    }
                    if ($start > 1) {
                      echo "<li class='page-item'><a class='page-link' href='".htmlspecialchars(url_set_page(1))."'>1</a></li>";
                      if ($start > 2) echo "<li class='page-item disabled'><a class='page-link'>…</a></li>";
                    }
                    for ($i = $start; $i <= $end; $i++) {
                      echo "<li class='page-item " . ($i == $page ? 'active' : '') . "'><a class='page-link' href='".htmlspecialchars(url_set_page($i))."'>$i</a></li>";
                    }
                    if ($end < $pages) {
                      if ($end < $pages - 1) echo "<li class='page-item disabled'><a class='page-link'>…</a></li>";
                      echo "<li class='page-item'><a class='page-link' href='".htmlspecialchars(url_set_page($pages))."'>$pages</a></li>";
                    }
                  ?>
                  <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>"><a class="page-link" href="<?= $nextHref ?>">Próximo &gt;</a></li>
                <?php else: ?>
                  <li class="page-item active"><a class="page-link">1</a></li>
                <?php endif; ?>
              </ul>
            </div>

            <div class="small text-muted">
              <?php if ($tabla && isset($total) && isset($offset)):
                $startNum = $total ? ($offset + 1) : 0;
                $endNum = min($total, $offset + count($rows));
              ?>
                Mostrando <?= $startNum ?>–<?= $endNum ?> de <?= $total ?> registros
              <?php endif; ?>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  <!-- Modal Ver -->
  <div class="modal fade" id="modalVer" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Detalles del registro</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="modalVerBody">
          <p class="text-muted text-center">Cargando...</p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Crear/editar -->
  <div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="formDynamic">
          <div class="modal-header">
            <h5 class="modal-title" id="modalFormTitle">Nuevo registro</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" id="modalFormBody">
            <p class="text-muted">Cargando formulario...</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    window.APP = {
      tabla: <?= json_encode($tabla) ?>,
      columns: <?= json_encode($columns) ?>,
      pk: <?= json_encode($primaryKey) ?>,
      page: <?= json_encode($page) ?>,
      pages: <?= json_encode($pages) ?>,
      limit: <?= json_encode($limit) ?>
    };
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/main.js"></script>
</body>
</html>
