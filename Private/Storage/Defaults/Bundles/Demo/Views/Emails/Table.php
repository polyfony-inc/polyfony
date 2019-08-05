<table class="table table-striped table-sm table-hover" style="margin:0">
	<thead>
		<tr>
			<th>
				ID
			</th>
			<th>
				Recipients
			</th>
			<th>
				Subject
			</th>
			<th>
				Creation date
			</th>
			<th>
				Sending date
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach($emails as $email): ?>
			<tr>
				<td>
					<?= $email->get('id'); ?>
				</td>
				<td>
					<?= $email->getRecipientsLinks(12); ?>
				</td>
				<td>
					<?= $email->tget('subject', 32); ?>
				</td>
				<td>
					<?= $email->get('creation_date'); ?>
				</td>
				<td>
					<?= $email->getSendingDateBadge(); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>