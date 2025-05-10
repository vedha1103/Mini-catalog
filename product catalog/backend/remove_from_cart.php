<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  echo json_encode(['success' => false, 'message' => 'Not logged in']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$productName = $data['product_name'] ?? null;

if (!$productName) {
  echo json_encode(['success' => false, 'message' => 'Product name missing']);
  exit;
}

$stmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
$stmt->execute([$productName]);
$product = $stmt->fetch();

if (!$product) {
  echo json_encode(['success' => false, 'message' => 'Product not found']);
  exit;
}

$product_id = $product['id'];
$user_id = $_SESSION['user_id'];

$delStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
$success = $delStmt->execute([$user_id, $product_id]);

echo json_encode([
  'success' => $success,
  'message' => $success ? 'Removed from cart' : 'Failed to remove item'
]);
