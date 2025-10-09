<?php
// public/tables/delete_table.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/db_connect.php';

function jsonExit($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }
function is_valid_table_name($s){ return is_string($s) && preg_match('/^[A-Za-z0-9_]+$/', $s); }
function table_exists($conexion, $tabla){ $t = $conexion->real_escape_string($tabla); $r = $conexion->query("SHOW TABLES LIKE '$t'"); return $r && $r->num_rows>0; }

$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
if (!is_array($payload)) $payload = $_POST + [];

$tabla = trim($payload['tabla'] ?? ($payload['table'] ?? ''));
$id = isset($payload['id']) ? intval($payload['id']) : 0;

if (!is_valid_table_name($tabla) || !table_exists($conexion, $tabla)) jsonExit(['success'=>false,'message'=>'tabla_invalida']);
if ($id <= 0) jsonExit(['success'=>false,'message'=>'id_invalido']);

$desc = $conexion->query("DESCRIBE `$tabla`");
if (!$desc) jsonExit(['success'=>false,'message'=>'no_schema']);
$schema = [];
while ($d = $desc->fetch_assoc()) $schema[] = $d;

$pk = null;
foreach ($schema as $c) if (isset($c['Key']) && $c['Key'] === 'PRI') { $pk = $c['Field']; break; }
if (!$pk) $pk = $schema[0]['Field'];

$stmt = $conexion->prepare("DELETE FROM `$tabla` WHERE `$pk` = ?");
if (!$stmt) jsonExit(['success'=>false,'message'=>$conexion->error]);
$stmt->bind_param('i', $id);
$ok = $stmt->execute();

jsonExit(['success'=> (bool)$ok, 'message' => $ok ? 'eliminado' : $stmt->error]);
