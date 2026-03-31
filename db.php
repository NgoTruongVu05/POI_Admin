<?php
$host = "localhost";
$dbname = "POI_Database";
$username = "root"; // Mặc định của XAMPP
$password = "";     // Mặc định của XAMPP thường để trống

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Thiết lập chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối MySQL: " . $e->getMessage());
}
?>