<?php
// public/index.php

require_once __DIR__ . '/../includes/auth.php';
require_login('auth/login.php');

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../public/tables/read_table.php';

$tabla = $_GET['tabla'] ?? '';
$q = trim($_GET['q'] ?? '');
$filters = $_GET['f'] ?? [];
$page = max(1, intval($_GET['page'] ?? 1));
$limit = intval($_GET['limit'] ?? 10);
if (!in_array($limit, [10,25,50], true)) $limit = 10;

$data = read_table($conexion, $tabla, [
    'q' => $q,
    'filters' => $filters,
    'page' => $page,
    'limit' => $limit
]);

$tables = $data['tables'];
$tabla = $data['tabla'];
$columns = $data['columns'];
$rows = $data['rows'];
$primaryKey = $data['primaryKey'];
$total = $data['total'];
$pages = $data['pages'];
$offset = $data['offset'];
$limit = $data['limit'];
$filterCandidates = $data['filterCandidates'];
$distinctValues = $data['distinctValues'];

// include header
include __DIR__ . '/../templates/header.php';

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
