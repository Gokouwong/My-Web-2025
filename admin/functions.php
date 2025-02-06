<?php
require_once 'admin/config.php';

// Function to get page content
function getPageContent($page) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM website_content WHERE page = ?");
    $stmt->bind_param("s", $page);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to get all active products
function getProducts() {
    global $conn;
    $result = $conn->query("SELECT * FROM products WHERE active = true ORDER BY id");
    $products = [];
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}

// Function to get a single product
function getProduct($id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND active = true");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}
?> 