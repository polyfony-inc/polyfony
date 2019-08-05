<table class="table">
	<tr>
		<th>
			Login
		</th>
		<th>
			Session expiration (raw)
		</th>
		<th>
			Session expiration (relative)
		</th>
		<th>
			Session expiration
		</th>
		<th>
			Level
		</th>
		<th>
			Last login date
		</th>
	</tr>
	<?php foreach($accounts as $account): ?>
		<tr>
			<td>
				<code>
					<?= $account->tget('login',16); ?>
				</code>
			</td>
			<td>
				<code>
					<?= $account->get('session_expiration_date',true); ?>
				</code>
			</td>
			<td>
				<code>
					<?= Polyfony\Format::date(
						$account->get('session_expiration_date',true)
					); ?>
				</code>
			</td>
			<td>
				<span class="label label-warning">
					<?= $account->get('session_expiration_date'); ?>
				</span>
			</td>
			<td>
				<?= $account->getLevel(); ?>
				(<code>
					<?= $account->get('id_level'); ?>
				</code>) 
			</td>
			<td>
				<span class="label label-success">
					<?= $account->get('last_login_date'); ?>
				</span>
			</td>
		</tr>
	<?php endforeach; ?>
</table>