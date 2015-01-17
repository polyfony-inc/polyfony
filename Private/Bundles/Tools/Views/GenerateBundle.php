<div class="jumbotron" style="padding:45px;">
	<h1>CRUD Generator</h1>
	<p>
		This tool will generate a model, controller, view and routes<br />
		with Create, Read, Update, Delete capabilities for a table of your database.
	</p>
</div>

<form action="" method="post">
	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				CRUD Generator
			</div>
			<div class="panel-body">
				<?php echo isset($this->Notice) ? $this->Notice : ''; ?>
				<div class="form-group">
					<label>Destination bundle</label>
					<?php echo $this->bundleInput; ?>
				</div>
				<div class="form-group">
					<label>Table name</label>
					<?php echo $this->tableInput; ?>
				</div>
				<button class="btn btn-primary pull-right">
					Generate
				</button>
			</div>
		</div>
	</div>
</form>
