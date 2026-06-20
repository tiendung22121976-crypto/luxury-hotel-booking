<?php
/**
 * includes/head.php
 * -----------------------------------------------------------
 * Phần <head> dùng chung: nạp Bootstrap 5, Google Fonts (Roboto +
 * Playfair Display) và file CSS tùy biến theo tone Navy/Gold.
 * Biến $pageTitle nên được khai báo TRƯỚC khi include file này.
 * -----------------------------------------------------------
 */
if (!isset($pageTitle)) $pageTitle = 'Luxury Hotel';
?><!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<!-- Bootstrap 5 CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
<!-- Google Fonts: Roboto (mặc định) + Playfair Display (tiêu đề sang trọng) -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Playfair+Display:wght@400;600;700&display=swap">
<!-- CSS tùy biến theo tone Navy/Gold -->
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
