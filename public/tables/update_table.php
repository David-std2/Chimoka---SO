<?php
// public/tables/update_table.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';

function jsonExit($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }
function is_valid_table_name($s){ return is_string($s) && preg_match('/^[A-Za-z0-9_]+$/', $s); }
function table_exists($conexion, $tabla){ $t = $conexion->real_escape_string($tabla); $r = $conexion->query("SHOW TABLES LIKE '$t'"); return $r && $r->num_rows>0; }

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) $payload = $_POST + [];

$tabla = trim($payload['tabla'] ?? ($payload['table'] ?? ''));
$id = isset($payload['id']) ? intval($payload['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
$data = $payload['data'] ?? $payload;

if (!is_valid_table_name($tabla) || !table_exists($conexion, $tabla)) jsonExit(['success'=>false,'message'=>'tabla_invalida']);
if ($id <= 0) jsonExit(['success'=>false,'message'=>'id_invalido']);

$desc = $conexion->query("DESCRIBE `$tabla`");
if (!$desc) jsonExit(['success'=>false,'message'=>'no_schema']);
$schema = [];
while ($d = $desc->fetch_assoc()) $schema[$d['Field']] = $d;

$pk = null;
foreach ($schema as $col => $meta) if (isset($meta['Key']) && $meta['Key'] === 'PRI') { $pk = $col; break; }
if (!$pk) $pk = array_key_first($schema);

$setParts = []; $values = []; $types = '';
foreach ($schema as $col => $meta) {
    if ($col === $pk) continue;
    if (!array_key_exists($col, $data)) continue;
    $setParts[] = "`$col` = ?";
    $val = $data[$col];
    $t = strtolower($meta['Type']);
    if (preg_match('/\b(int|tinyint|smallint|mediumint|bigint)\b/',$t)) { $types .= 'i'; $val = ($val === '' ? 0 : intval($val)); }
    elseif (preg_match('/\b(decimal|float|double|real)\b/',$t)) { $types .= 'd'; $val = ($val === '' ? 0.0 : floatval($val)); }
    else { $types .= 's'; $val = (string)$val; }
    $values[] = $val;
}

if (empty($setParts)) jsonExit(['success'=>false,'message'=>'no_campos_para_actualizar']);

$pkType = 'i';
$types .= $pkType;
$values[] = $id;

$sql = "UPDATE `$tabla` SET " . implode(', ', $setParts) . " WHERE `$pk` = ?";
$stmt = $conexion->prepare($sql);
if (!$stmt) jsonExit(['success'=>false,'message'=>$conexion->error,'sql'=>$sql]);

$refs = [];
foreach ($values as $i => $v) $refs[$i] = &$values[$i];
array_unshift($refs, $types);
call_user_func_array([$stmt, 'bind_param'], $refs);

$ok = $stmt->execute();
jsonExit(['success'=> (bool)$ok, 'message' => $ok ? 'actualizado' : $stmt->error]);
