<?php
$host     = 'localhost';
$dbname   = 'student_management_system'; // phpMyAdmin'deki veritabanı adınız
$kullanici = 'root';
$sifre     = '';
$charset   = 'utf8mb4';       

$sql = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       
    PDO::ATTR_EMULATE_PREPARES   => false,                 
];

try {
    $baglanti = new PDO($sql, $kullanici, $sifre, $options);
} catch(PDOException $e) {
    die("Veritabanına bağlanılamadı: " . $e->getMessage());
}
?>
