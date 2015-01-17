<div class="jumbotron" style="padding:45px;">
	<h1>Database</h1>
	<p>
		Retrieve and iterate over database records.<br />
		This example uses the <code>Database</code> abstraction and the <code>Record</code> class.
	</p>
	
	<div class="panel panel-default">
		<!-- Default panel contents -->
		<div class="panel-heading">Accounts <span class="badge badge-info"><?php echo count($this->Accounts); ?></span></div>
		<table class="table">
			<tr>
				<th>
					Login
				</th>
				<th>
					Session expiration (raw)
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
			<?php foreach($this->Accounts as $record): ?>
				<tr>
					<td>
						<code>
							<?php echo $record->get('login'); ?>
						</code>
					</td>
					<td>
						<code>
							<?php echo $record->get('session_expiration_date',true); ?>
						</code>
					</td>
					<td>
						<span class="label label-warning">
							<?php echo $record->get('session_expiration_date'); ?>
						</span>
					</td>
					<td>
						<code>
							<?php echo $record->get('id_level'); ?>
						</code>
					</td>
					<td>
						<span class="label label-success">
							<?php echo $record->get('last_login_date'); ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<p>
		Update
	</p>
	<?php var_dump($this->UpdateStatus); ?>
	<p>
		Create
	</p>
	<?php var_dump($this->CreateStatus); ?>
	<p>
		Delete
	</p>
	<?php var_dump($this->DeleteStatus); ?>
</div>
