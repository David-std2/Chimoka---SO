<?php
// templates/header.php
// $tabla, $tables, $filterCandidates, $distinctValues, $filters, $q, $columns, $limit existan
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars(human($tabla) ?: 'None') ?> — Chimoka</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<link rel="stylesheet" href="css/main.css">
</head>
<body>
  <!-- Sidebar -->
  <aside class="sidebar d-flex flex-column align-items-center py-4" role="navigation" aria-label="Barra lateral">
    <a href="index.php" class="sidebar-logo mb-3" title="Inicio">
      <img src="assets/LogoChimoka.png" alt="Logo" class="sidebar-logo-img">
    </a>

    <nav class="nav-icons d-flex flex-column gap-3 align-items-center w-100" role="menu">
      <a href="index.php" class="icon-btn <?= empty($tabla) ? 'active' : '' ?>" title="Tablas"><i class="bi bi-table"></i></a>
      <a href="dashboard.php" class="icon-btn" title="Dashboard"><i class="bi bi-speedometer2"></i></a>
      <a href="profile.php" class="icon-btn" title="Perfil"><i class="bi bi-person-circle"></i></a>
    </nav>

    <div class="mt-auto mb-3 small-handle">
      <a href="#" id="sidebarToggle" class="icon-btn-handle" title="Menú"><i class="bi bi-list"></i></a>
    </div>
  </aside>

  <main class="main-content">
    <div class="container-fluid p-4">
      <div class="card page-card">
        <div class="card-body p-4 p-md-5">

          <!-- header -->
          <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-3">
            <h2 class="page-title mb-0"><?= htmlspecialchars(human($tabla) ?: 'None') ?></h2>

            <div class="d-flex gap-2 w-100 w-md-auto align-items-center">
              <form method="GET" class="d-flex me-2 flex-grow-1" style="gap:10px; align-items:center;">
                <input type="hidden" name="tabla" value="<?= htmlspecialchars($tabla) ?>">
                <?php
                  if (!empty($filters)) {
                    foreach ($filters as $col => $vals) {
                      foreach ((array)$vals as $v) {
                        echo '<input type="hidden" name="f['.htmlspecialchars($col).'][]" value="'.htmlspecialchars($v).'">';
                      }
                    }
                  }
                ?>
                <div class="input-group search-wrap">
                  <span class="input-group-text"><i class="bi bi-search"></i></span>
                  <input id="search" name="q" value="<?= htmlspecialchars($q) ?>" type="text" class="form-control search-input" placeholder="Buscar en la tabla...">
                </div>
              </form>

              <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php foreach ($filterCandidates as $field): ?>
                  <div class="dropdown">
                    <button class="btn filter-btn dropdown-toggle" data-bs-toggle="dropdown"><?= htmlspecialchars(ucwords(str_replace('_',' ',$field))) ?></button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="<?= htmlspecialchars(url_remove_filter($field)) ?>">Todos</a></li>
                      <?php foreach ($distinctValues[$field] as $val): ?>
                        <li><a class="dropdown-item" href="<?= htmlspecialchars(url_add_filter($field, $val)) ?>"><?= $val === '' ? '(vacío)' : htmlspecialchars($val) ?></a></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                <?php endforeach; ?>
              </div>

              <form method="GET" class="d-flex align-items-center ms-2" style="gap:8px;">
                <select name="tabla" class="form-select form-select-sm">
                  <option value="">- Tabla -</option>
                  <?php foreach ($tables as $t): ?>
                    <option value="<?= htmlspecialchars($t) ?>" <?= $t === $tabla ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm">Seleccionar</button>
              </form>

              <button id="btn-new" class="btn btn-new d-flex align-items-center ms-2"><i class="bi bi-plus-lg me-2"></i> Nuevo registro</button>
            </div>
          </div>

          <hr style="opacity:.12;">

          <!-- chips -->
          <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
            <div class="filters-left text-muted small">Filtrar por:</div>
            <div class="filters-chips d-flex gap-2 align-items-center" id="active-chips">
              <?php if (!empty($filters)): ?>
                <?php foreach ($filters as $col => $vals): foreach ((array)$vals as $v): ?>
                  <div class="chip"><?= htmlspecialchars(ucwords($col)) ?>: <?= htmlspecialchars($v) ?> <a class="chip-x" href="<?= htmlspecialchars(url_remove_filter($col, $v)) ?>">&times;</a></div>
                <?php endforeach; endforeach; ?>
                <a id="clear-chips" class="ms-2 small text-decoration-none text-danger" href="<?= htmlspecialchars(url_clear_filters()) ?>">Limpiar</a>
              <?php else: ?>
                <div class="chip">None <a class="chip-x" href="#" onclick="event.preventDefault(); this.closest('.chip').remove();">&times;</a></div>
                <a id="clear-chips" class="ms-2 small text-decoration-none" href="#" onclick="event.preventDefault(); document.querySelectorAll('.chip').forEach(c=>c.remove());">Limpiar</a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Tabla: inicio -->
          <div class="table-wrap">
            <table id="data-table" class="table table-striped align-middle mb-0">
              <thead class="thead-custom">
                <tr>
                  <?php if (empty($columns)): ?>
                    <th>None <i class="bi bi-chevron-down ms-1 small"></i></th>
                    <th>None <i class="bi bi-chevron-down ms-1 small"></i></th>
                    <th>None</th>
                    <th>None <i class="bi bi-chevron-down ms-1 small"></i></th>
                    <th>None <i class="bi bi-chevron-down ms-1 small"></i></th>
                    <th>None <i class="bi bi-chevron-down ms-1 small"></i></th>
                    <th class="text-center sticky-col">Acciones</th>
                  <?php else: ?>
                    <?php foreach ($columns as $col): ?>
                      <th><?= htmlspecialchars(ucwords(str_replace('_',' ', $col))) ?> <?php if ($col !== end($columns)) echo '<i class="bi bi-chevron-down ms-1 small"></i>'; ?></th>
                    <?php endforeach; ?>
                    <th class="text-center sticky-col">Acciones</th>
                  <?php endif; ?>
                </tr>
              </thead>
              <tbody>
<!-- index.php -->
