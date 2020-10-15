<table class="table">
	<tr>
		<th>
			Login (truncated)
		</th>
		<th>
			Account expiration (raw)
		</th>
		<th>
			Account expiration
		</th>
		<th>
			
		</th>
		<th>
			
		</th>
		<th>
			
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
					<?= $account->get('is_expiring_on',true); ?>
				</code>
			</td>
			<td>
				<code>
					<?= $account->get('is_expiring_on'); ?>
				</code>
			</td>
			<td>
				
			</td>
			<td>
				
			</td>
			<td>
				
			</td>
		</tr>
	<?php endforeach; ?>
</table>