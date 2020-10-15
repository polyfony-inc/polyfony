<h1>
	Email template number <?= $number; ?>
</h1>
<p>
	This is merely a demo template, bellow we iterate on stuff
</p>

<?php foreach(
	$names 
	as $name
): ?>

	<div class="name">
		<?= $name ?>
	</div>

<?php endforeach; ?> 