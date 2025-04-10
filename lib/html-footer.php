		</main>
		<footer><?= json_encode_fake($current_user ?? '') ?></fotter>
	</div>
	<?php foreach ($script_src_beforeend as $src): ?>
		<script src="<?= append_mtime($src) ?>"></script>
	<?php endforeach; ?>
</body>
</html>
