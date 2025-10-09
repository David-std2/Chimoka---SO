<?php
// public/read_table.php

function read_table(mysqli $conexion, string $tabla = '', array $opts = []): array {
    // por defecto
    $q = trim($opts['q'] ?? '');
    $filters = $opts['filters'] ?? [];
    $page = max(1, intval($opts['page'] ?? 1));
    $limit = intval($opts['limit'] ?? 10);
    if (!in_array($limit, [10,25,50], true)) $limit = 10;

    $result = [
        'tables' => [],
        'tabla' => '',
        'columns' => [],
        'rows' => [],
        'primaryKey' => null,
        'total' => 0,
        'page' => $page,
        'pages' => 1,
        'limit' => $limit,
        'offset' => 0,
        'filterCandidates' => [],
        'distinctValues' => [],
        'q' => $q,
        'filters' => $filters,
    ];

    // lista de tablas
    $tablesRes = $conexion->query("SHOW TABLES");
    if ($tablesRes) {
        while ($r = $tablesRes->fetch_array()) $result['tables'][] = $r[0];
    }

    if ($tabla && in_array($tabla, $result['tables'], true)) {
        $result['tabla'] = $tabla;

        $desc = $conexion->query("DESCRIBE `$tabla`");
        while ($d = $desc->fetch_assoc()) {
            $result['columns'][] = $d['Field'];
            if ($d['Key'] === 'PRI') $result['primaryKey'] = $d['Field'];
        }
        if (!$result['primaryKey'] && !empty($result['columns'])) $result['primaryKey'] = $result['columns'][0];

        $startFrom = 2;
        for ($i = $startFrom; $i < count($result['columns']) && count($result['filterCandidates']) < 3; $i++) {
            $col = $result['columns'][$i];
            $res = $conexion->query("SELECT COUNT(DISTINCT `$col`) AS cnt FROM `$tabla`");
            $cnt = $res ? intval($res->fetch_assoc()['cnt']) : 0;
            if ($cnt > 0 && $cnt <= 200) {
                $result['filterCandidates'][] = $col;
                $vals = [];
                $vres = $conexion->query("SELECT DISTINCT `$col` AS v FROM `$tabla` ORDER BY v ASC LIMIT 200");
                while ($vv = $vres->fetch_assoc()) $vals[] = $vv['v'];
                $result['distinctValues'][$col] = $vals;
            }
        }

        $where = [];
        foreach ($filters as $col => $vals) {
            if (!in_array($col, $result['columns'], true)) continue;
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
            if (empty($textCols)) $textCols = $result['columns'];
            foreach ($textCols as $c) {
                $likeParts[] = "`$c` LIKE '%" . $conexion->real_escape_string($q) . "%'";
            }
            if (!empty($likeParts)) $where[] = "(" . implode(' OR ', $likeParts) . ")";
        }

        $whereSql = empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);

        $countRow = $conexion->query("SELECT COUNT(*) AS total FROM `$tabla` $whereSql");
        $result['total'] = $countRow ? intval($countRow->fetch_assoc()['total']) : 0;
        $result['pages'] = $limit > 0 ? max(1, ceil($result['total'] / $limit)) : 1;
        if ($page > $result['pages']) $page = $result['pages'];
        $result['page'] = $page;
        $result['offset'] = ($page - 1) * $limit;

        $orderBy = $result['primaryKey'] ? "ORDER BY `" . $conexion->real_escape_string($result['primaryKey']) . "` ASC" : '';
        $sql = "SELECT * FROM `$tabla` $whereSql $orderBy LIMIT $limit OFFSET " . intval($result['offset']);
        $res = $conexion->query($sql);
        if ($res) {
            while ($r = $res->fetch_assoc()) $result['rows'][] = $r;
        }
    }

    return $result;
}
