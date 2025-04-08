<?php
require_once 'authentication.php';

require 'html-header.php';
?>

<div class="markdown"><?= file_get_contents('index.md') ?></div>

<?php require 'html-footer.php'; ?>
