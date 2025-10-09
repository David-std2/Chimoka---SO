<?php
// app/includes/functions.php
// Helpers reutilizables

if (!function_exists('url_with')) {
    function url_with($overrides = []) {
        $qs = $_GET;
        foreach ($overrides as $k => $v) $qs[$k] = $v;
        return '?' . http_build_query($qs);
    }
}

if (!function_exists('url_set_page')) {
    function url_set_page($p) { return url_with(['page' => $p]); }
}

if (!function_exists('url_set_limit')) {
    function url_set_limit($l) { return url_with(['limit' => $l, 'page' => 1]); }
}

if (!function_exists('url_clear_filters')) {
    function url_clear_filters() { $qs = $_GET; unset($qs['f']); $qs['page'] = 1; return '?' . http_build_query($qs); }
}

if (!function_exists('url_add_filter')) {
    function url_add_filter($col, $val) {
        $qs = $_GET; $f = $qs['f'] ?? [];
        if (!isset($f[$col])) $f[$col] = [];
        if (!is_array($f[$col])) $f[$col] = [$f[$col]];
        if (!in_array($val, $f[$col])) $f[$col][] = $val;
        $qs['f'] = $f; $qs['page'] = 1;
        return '?' . http_build_query($qs);
    }
}

if (!function_exists('url_remove_filter')) {
    function url_remove_filter($col, $val = null) {
        $qs = $_GET; $f = $qs['f'] ?? [];
        if (!isset($f[$col])) return '?' . http_build_query($qs);
        if ($val === null) { unset($f[$col]); }
        else {
            $f[$col] = array_values(array_diff((array)$f[$col], [$val]));
            if (empty($f[$col])) unset($f[$col]);
        }
        $qs['f'] = $f; $qs['page'] = 1;
        if (empty($qs['f'])) unset($qs['f']);
        return '?' . http_build_query($qs);
    }
}

if (!function_exists('human')) {
    function human($s){ return $s ? ucwords(str_replace('_',' ',$s)) : ''; }
}
