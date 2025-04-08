<?php
require '../lib/init.php';
while (ob_get_level()) ob_end_clean();

$data = ini_get_all();
$nav_items = array();
foreach ($data as $fullname => &$info) {
	$info['changed'] = ($info['local_value'] !== $info['global_value']);
	$info['name_split'] = explode('.', $fullname, 2);

	if (count($info['name_split']) > 1
		&& ! array_key_exists($info['name_split'][0], $nav_items)
	) $nav_items[$info['name_split'][0]] = $fullname;
}

function convert(
	?string $value
) : string {
	if ($value === null) return '<small>NULL</small>';
	if (! strlen($value)) return '<small>empty</small>';
	return htmlspecialchars($value);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>PHP Configuration Options</title>
	<style>
		:root { font-family: 'Cascadia Code', monospace; }

		ul { list-style-type: none; }
		li { display: inline-block; }

		thead th { background-color: #ccc; }

		small { color: gray; }
		small::before { content: '('; }
		small::after { content: ')'; }
	</style>
</head>
<body>
	<h1>PHP Configuration Options</h1>
	<nav>
		<ul>
			<?php foreach ($nav_items as $ext => $full): ?>
				<li><a href="#<?= $full ?>"><?= $ext ?></a></li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<table>
		<thead>
			<tr>
				<th scope="colgroup" colspan="2">name</th>
				<th scope="col">value</th>
				<th scope="col">access</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($data as $fullname => $info): ?>
				<tr id="<?= $fullname ?>"
					<?php if ($info['changed']): ?>
						style="background-color: pink"
					<?php endif; ?>
				>
					<th scope="row" style="text-align: end;"><?= $info['name_split'][0] ?></th>
					<td><?= count($info['name_split']) > 1 ? ".{$info['name_split'][1]}" : '' ?></td>
					<td>
						<?= convert($info['local_value']) ?>
						<?php if ($info['changed']): ?>
							<div style="font-size: small;">global: <?= convert($info['global_value']) ?></div>
						<?php endif; ?>
					</td>
					<td><?= sprintf('b%03b', $info['access']) ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</body>
</html>
