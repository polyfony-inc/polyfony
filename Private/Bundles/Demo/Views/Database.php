<div class="jumbotron" style="padding:45px;">
	<h1>Database</h1>
	<p>
		Retrieve and iterate over records from the database
	</p>
	<code>
		<?php foreach($this->Results as $record): ?>
			<?php echo $record; ?><br />
			creation_date = <?php echo $record->get('creation_date'); ?><br />
			creation_date (raw) = <?php echo $record->get('creation_date',true); ?><br />
			login = <?php echo $record->get('login'); ?>
		<?php endforeach; ?>
	</code>
	<p>
		Retrieve a specific record by its id, alter and save it
	</p>
	<code>
		$root_account = new pf\Record('Accounts','1');
	</code>
	<p>
		Create a new record and insert it
	</p>
	<code>
		$new_account = new pf\Record('Accounts');<br />
		$new_account->set('login','test');<br />
		$new_account->set('password',pf\Security::getPassword('test'));<br />
		$new_account->save();
	</code>
</div>
