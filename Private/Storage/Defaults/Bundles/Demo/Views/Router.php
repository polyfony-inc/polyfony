<?php

use \Polyfony\Router as r;

?>
<div class="container">
	<div class="row">

		<div class="col-6">

			<h2>PHP</h2>

			<code>
				Router::reverse('demo', ['category'=>'locales'], true, true);
			</code>
			<br />

			<code>
				Router::reverse('demo', ['category'=>'response'], false, true);
			</code>
			<br />

			<code>
				Router::reverse('demo');
			</code>


		</div>

		<div class="col-6">

			<h2>
				Output
			</h2>

			<code>
				<?= r::reverse(
					'demo', 
					['category'=>'locales'], 
					true, 
					true
				); ?>
			</code>
			<br />

			<code>
				<?= r::reverse(
					'demo', 
					['category'=>'response'], 
					false, 
					true
				); ?>
			</code>
			<br />

			<code>
				<?= r::reverse(
					'demo', 
					[]
				); ?>
			</code>

		</div>


	</div>	

</div>