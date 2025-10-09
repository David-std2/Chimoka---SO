<?php
// public/index.php
require_once __DIR__ . '/../app/includes/db_connect.php';
require_once __DIR__ . '/../app/includes/functions.php';

// Obtener tablas
$tablesRes = $conexion->query("SHOW TABLES");
$tables = [];
while ($r = $tablesRes->fetch_array()) $tables[] = $r[0];

$tabla = $_GET['tabla'] ?? '';
if ($tabla && !in_array($tabla, $tables)) $tabla = '';

$q = trim($_GET['q'] ?? '');
$filters = $_GET['f'] ?? [];

$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 10);
if (!in_array($limit, [10,25,50])) $limit = 10;

$columns = [];
$rows = [];
$primaryKey = null;
$total = 0;
$pages = 1;
$filterCandidates = [];
$distinctValues = [];

if ($tabla) {
    $desc = $conexion->query("DESCRIBE `$tabla`");
    while ($d = $desc->fetch_assoc()) {
        $columns[] = $d['Field'];
        if ($d['Key'] === 'PRI') $primaryKey = $d['Field'];
    }
    if (!$primaryKey && !empty($columns)) $primaryKey = $columns[0];

    $startFrom = 2;
    for ($i = $startFrom; $i < count($columns) && count($filterCandidates) < 3; $i++) {
        $col = $columns[$i];
        $res = $conexion->query("SELECT COUNT(DISTINCT `$col`) AS cnt FROM `$tabla`");
        $cnt = $res ? intval($res->fetch_assoc()['cnt']) : 0;
        if ($cnt > 0 && $cnt <= 200) {
            $filterCandidates[] = $col;
            $vals = [];
            $vres = $conexion->query("SELECT DISTINCT `$col` AS v FROM `$tabla` ORDER BY v ASC LIMIT 200");
            while ($vv = $vres->fetch_assoc()) $vals[] = $vv['v'];
            $distinctValues[$col] = $vals;
        }
    }

    $where = [];
    foreach ($filters as $col => $vals) {
        if (!in_array($col, $columns)) continue;
        $valsArr = (array)$vals;
        $escaped = array_map(fn($v) => "'" . $conexion->real_escape_string($v) . "'", $valsArr);
        $where[] = "`$col` IN (" . implode(',', $escaped) . ")";
    }
    if ($q !== '') {
        $likeParts = [];
        $desc2 = $conexion->query("DESCRIBE `$tabla`");
        $textCols = [];
        while ($d = $desc2->fetch_assoc()) {
            $t = strtolower($d['Type']);
            if (str_contains($t, 'char') || str_contains($t, 'text') || str_contains($t, 'varchar')) $textCols[] = $d['Field'];
        }
        if (empty($textCols)) $textCols = $columns;
        foreach ($textCols as $c) {
            $likeParts[] = "`$c` LIKE '%" . $conexion->real_escape_string($q) . "%'";
        }
        if (!empty($likeParts)) $where[] = "(" . implode(' OR ', $likeParts) . ")";
    }
    $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

    $countRow = $conexion->query("SELECT COUNT(*) AS total FROM `$tabla` $whereSql");
    $total = $countRow ? intval($countRow->fetch_assoc()['total']) : 0;
    $pages = $limit > 0 ? max(1, ceil($total / $limit)) : 1;
    if ($page > $pages) $page = $pages;
    $offset = ($page - 1) * $limit;

    $orderBy = $primaryKey ? "ORDER BY `$primaryKey` ASC" : '';
    $sql = "SELECT * FROM `$tabla` $whereSql $orderBy LIMIT $limit OFFSET $offset";
    $res = $conexion->query($sql);
    if ($res) {
        while ($r = $res->fetch_assoc()) $rows[] = $r;
    }
}

// include header
include __DIR__ . '/../templates/header.php';

// print rows
if (empty($columns)) {
} else {
    if (count($rows) > 0) {
        foreach ($rows as $r) {
            echo "<tr>";
            foreach ($columns as $c) {
                echo "<td>" . htmlspecialchars($r[$c]) . "</td>";
            }
            $idVal = htmlspecialchars($r[$primaryKey] ?? '');
            echo '<td class="text-center actions sticky-col">';
            echo '<button class="action-btn text-secondary ver" title="Ver" data-id="'. $idVal .'"><i class="bi bi-eye"></i></button>';
            echo '<button class="action-btn text-primary editar" title="Editar" data-id="'. $idVal .'"><i class="bi bi-pencil"></i></button>';
            echo '<button class="action-btn text-danger eliminar" title="Eliminar" data-id="'. $idVal .'"><i class="bi bi-trash"></i></button>';
            echo '</td>';
            echo "</tr>";
        }
    } else {
        echo '<tr><td colspan="'. (count($columns) + 1) .'" class="text-center text-muted">Sin registros.</td></tr>';
    }
}

// include footer
include __DIR__ . '/../templates/footer.php';
