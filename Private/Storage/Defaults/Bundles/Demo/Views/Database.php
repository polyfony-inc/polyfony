<div class="jumbotron" style="padding:45px;">
	<h1>Database</h1>
	<p>
		Retrieve and iterate over database records.<br />
		This example uses the <code>Database</code> abstraction and the <code>Record</code> class.
	</p>
	
	<div class="card">
		<!-- Default panel contents -->
		<div class="card-header">Accounts <span class="badge badge-info"><?= count($this->Accounts); ?></span></div>
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
			<?php foreach($this->Accounts as $record): ?>
				<tr>
					<td>
						<code>
							<?= $record->tget('login',8); ?>
						</code>
					</td>
					<td>
						<code>
							<?= $record->get('session_expiration_date',true); ?>
						</code>
					</td>
					<td>
						<code>
							<?= Polyfony\Format::date($record->get('session_expiration_date',true)); ?>
						</code>
					</td>
					<td>
						<span class="label label-warning">
							<?= $record->get('session_expiration_date'); ?>
						</span>
					</td>
					<td>
						<code>
							<?= $record->get('id_level'); ?>
						</code>
					</td>
					<td>
						<span class="label label-success">
							<?= $record->get('last_login_date'); ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	</div>
	<div class="row" style="padding-top:2em">
		<form class="col-4" action="" method="post" class="form form-inline">
			<?= new Polyfony\Form\Token(); ?>
			<div class="card">
				<h5 class="card-header">
					Simple account edition form 
					<button type="submit" class="btn btn-sm btn-success float-right">
						<span class="fa fa-save"></span> 
						Enregistrer
					</button>
				</h5>
				<div class="card-body">
					<?= Bootstrap\Alert::flash(); ?>
					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							Login
						</label>
						<div class="col-9">
							<?= $this->RootAccount->input(
								'login', 
								['class'=>'form-control']
							); ?>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							Level
						</label>
						<div class="col-9">
							<?= $this->RootAccount->select(
								'id_level', 
								Models\Accounts::ID_LEVEL, 
								['class'=>'form-control']
							); ?>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							Is enabled ?
						</label>
						<div class="col-9">
							<?= $this->RootAccount->select(
								'is_enabled', 
								Models\Accounts::IS_ENABLED, 
								['class'=>'form-control']
							); ?>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							Account expires on (optional)
						</label>
						<div class="col-9">
							<?= $this->RootAccount->input(
								'account_expiration_date', [
									'type'=>'date',
									'placeholder'=>'DD/MM/YYYY',
									'class'=>'form-control'
								]
							); ?>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							New password (optional)
						</label>
						<div class="col-9">
							<?= Polyfony\Form::input(
								'password', null, [
									'type'=>'password',
									'class'=>'form-control',
									'minlength'=>$this->MinPasswordLength
								]
							); ?>
						</div>
					</div>
				</div>
			</div>
		</form>
		
	</div>
</div>
