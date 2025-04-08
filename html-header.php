<?php
require_once __DIR__ . '/lib/init.php';
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="<?= CONFIG['language'] ?>" itemtype="WebPage">
<head>
	<meta charset="UTF-8">
	<meta property="og:locale" content="<?= CONFIG['locale'] ?>">
	<meta itemprop="inLanguage" content="<?= CONFIG['language'] ?>">

	<title><?= CONFIG['site.name'] ?></title>
	<base href="<?= CONFIG['site.base'] ?>">

	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="referrer" content="same-origin">
	<meta name="author" content="rich.dog.studio@gmail.com">

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/kong-util@0.8.14/dist/all.js"></script>
	<link rel="stylesheet" href="assets/main.css?<?= filemtime('assets/main.css') ?>">
</head>
<body>
	<script src="assets/afterbegin.js?<?= filemtime('assets/afterbegin.js') ?>"></script>
	<div class="container d-flex flex-column min-vh-100">
		<header>
			<nav class="navbar">
				<a class="navbar-brand" href="./"><?= CONFIG['site.name'] ?></a>
				<div class="d-flex">
					<menu class="nav mt-0">
						<?php if (isset($current_user)): ?>
							<li class="nav-item">
								<a class="nav-link" href="login.php?logout=1">
									登出
									<span title="<?= $current_user->email ?>"><?= $current_user->name ?></span>
								</a>
							</li>
						<?php else: ?>
							<li class="nav-item border rounded">
								<a class="nav-link google-login" href="login.php">
									<img alt aria-hidden="true" src="assets/google.svg" class="align-text-top">
									<span class="d-none d-md-inline">使用 Google</span>
									登入
								</a>
							</li>
						<?php endif; ?>
					</menu>
				</div>
			</nav>
		</header>
		<main itemprop="mainContentOfPage"
			itemtype="WebPageElement"
			class="my-2 pt-2 pb-5 border-top border-bottom flex-grow-1"
		>
