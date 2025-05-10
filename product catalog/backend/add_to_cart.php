<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'You must be logged in to add to cart.'
        ]);
        exit;
    }

    $product_id = $_POST['product_id'] ?? null;

    if (!$product_id) {
        $input = json_decode(file_get_contents('php://input'), true);
        $product_id = $input['product_id'] ?? null;
    }

    if (!$product_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing product ID.'
        ]);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->rowCount() > 0) {
        $updateStmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND product_id = ?");
        $updateStmt->execute([$user_id, $product_id]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->execute([$user_id, $product_id, 1]);
    }
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to add to cart: ' . $e->getMessage()
    ]);
}
