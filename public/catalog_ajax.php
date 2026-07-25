<?php
require "../app/bootstrap.php";

$search   = trim($_GET['q'] ?? '');
$sort     = $_GET['sort'] ?? 'new';
$brand    = $_GET['brand'] ?? '';
$priceMin = $_GET['price_min'] ?? '';
$priceMax = $_GET['price_max'] ?? '';

$sql = "SELECT p.*, b.name AS brand_name FROM products p LEFT JOIN brands b ON p.brand_id = b.id WHERE 1=1";
$params = [];

if ($search !== '') { $sql .= " AND p.name LIKE ?"; $params[] = "%$search%"; }
if ($brand  !== '') { $sql .= " AND p.brand_id = ?"; $params[] = (int)$brand; }
if ($priceMin !== '') { $sql .= " AND p.price >= ?"; $params[] = (int)$priceMin; }
if ($priceMax !== '') { $sql .= " AND p.price <= ?"; $params[] = (int)$priceMax; }

switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    case 'name_asc':   $sql .= " ORDER BY p.name ASC"; break;
    case 'brand_asc':  $sql .= " ORDER BY brand_name ASC, p.name ASC"; break;
    default:           $sql .= " ORDER BY p.id DESC"; break;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($products);
