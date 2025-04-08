<?php
require '../lib/init.php';
while (ob_get_level()) ob_end_clean();
$data = get_defined_constants(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PHP Constants</title>
	<style>
		:root { font-family: 'Cascadia Code', monospace; }

		ul { list-style-type: none; }
		li { display: inline-block; }

		section { display: table; }
		h2 { display: table-caption; position: sticky; top: 0; background-color: white; }
		.tbody { display: table-row-group; }
		dl { display: table-row; }
		dt, dd { display: table-cell; padding: 0 .5rem; }
		dd { white-space: pre; }
	</style>
</head>
<body>
	<h1>PHP Constants</h1>
	<nav>
		<ul>
			<?php foreach (array_keys($data) as $cat): ?>
				<li><a href="#<?= $cat ?>"><?= $cat ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php foreach ($data as $cat => $consts): ?>
		<section id="<?= $cat ?>">
			<h2><?= $cat ?></h2>
			<div class="tbody">
				<?php foreach ($consts as $key => $value): ?>
					<dl>
						<dt><?= $key ?></dt>
						<dd><?= htmlspecialchars(var_export($value, true)) ?></dd>
					</dl>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endforeach; ?>
</body>
</html>
