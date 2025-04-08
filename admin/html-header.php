<?php
require_once 'authentication.php';
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="<?= CONFIG['language'] ?>">
<head>
	<meta charset="UTF-8">
	<meta name="referrer" content="strict-origin-when-cross-origin">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= CONFIG['site.name'] ?>管理後臺</title>
	<base href="<?= CONFIG['site.root'] ?>"><!-- 為了圖片的相對路徑。注意也因此： HTML 和 JS 用的路徑基準是這個，但在 PHP 則不是。 -->
	<link rel="icon" href="https://fakeimg.pl/256x256/?font=noto&text=<?= urlencode(CONFIG['site.name']) ?>">
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/kong-util@0.8.14/dist/all.js"></script>
	<link rel="stylesheet" href="assets/main.css?mtime=<?= filemtime('../assets/main.css') ?>">
</head>
<body>
	<script src="assets/afterbegin.js?mtime=<?= filemtime('../assets/afterbegin.js') ?>"></script>
	<div class="container">
	    <header>
	        <nav class="navbar">
	            <a class="navbar-brand" href="admin/"><?= CONFIG['site.name'] ?>管理後臺</a>
	            <menu class="nav mt-0">
	                <li class="nav-item"><a class="nav-link" href="admin/markdown.php">文檔</a></li>
	                <li class="nav-item"><a class="nav-link" href="admin/product.php">產品</a></li>
	                <!-- <li class="nav-item"><a class="nav-link" href="admin/image.php">圖片</a></li> -->
	                <li class="nav-item"><a class="nav-link" href="." target="_blank">前臺</a></li>
	                <li class="nav-item"><a class="nav-link" href="login.php?logout=1">登出</a></li>
	            </menu>
	        </nav>
	        <?php if (! empty($breadcrumb_list)): ?>
	            <nav aria-label="導覽標記">
	                <ol class="breadcrumb">
	                    <?php foreach ($breadcrumb_list as $index => $bc): ?>
	                        <li class="breadcrumb-item">
	                            <a href="<?= $bc['url'] ?>"><?= $bc['name'] ?></a>
	                        </li>
	                    <?php endforeach; ?>
	                </ol>
	            </nav>
	        <?php endif; ?>
	    </header>
	    <main class="my-2 pt-2 pb-5 border-top border-bottom">
