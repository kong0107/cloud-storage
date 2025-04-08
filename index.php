<?php
require_once './lib/user_authn.php';

require 'html-header.php';
?>

<h1>Home Page</h1>

<?= $current_user ? $current_user->email : 'QQ' ?>

<!-- <p>It works!</p> -->

<?php require 'html-footer.php'; ?>
