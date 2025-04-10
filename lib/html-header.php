<?php
require_once __DIR__ . '/html-prepare.php';
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

	<?php foreach ($meta_name_content as $name => $content): ?>
		<meta name="<?= $name ?>" content="<?= $content ?>">
	<?php endforeach; ?>

	<?php foreach ($link_href as $href): ?>
		<link rel="stylesheet" href="<?= append_mtime($href) ?>">
	<?php endforeach; ?>
</head>
<body>
	<?php foreach ($script_src_afterbegin as $src): ?>
		<script src="<?= append_mtime($src) ?>"></script>
	<?php endforeach; ?>
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
