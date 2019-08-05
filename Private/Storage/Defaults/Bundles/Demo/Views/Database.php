<?php use Polyfony\Locales as Locales; ?>
<div class="jumbotron" style="padding:45px;">

	<h1>
		Database
	</h1>
	
	<p>
		Retrieve and iterate over database records.<br />
		This example uses the <code>Database</code> abstraction and the <code>Record</code> class.
	</p>
	

	<div class="row">

		<div class="col-8">
			<div class="card">
				<!-- Default panel contents -->
				<div class="card-header">

					Accounts 
					<span class="badge badge-info">
						<?= count($accounts); ?>	
					</span>
				</div>
			
				<?php $this->view('Database/Table', ['accounts'=>$accounts]); ?>

			</div>
		</div>

		<form class="col-4" action="" method="post" class="form form-inline">

			<?= new Polyfony\Form\Token(); ?>

			<div class="card">

				<h5 class="card-header">
					Simple account edition form 
					
					<button type="submit" class="btn btn-sm btn-success float-right">
						<span class="fa fa-save"></span> 
						<?= Locales::get('Save'); ?>
					</button>

				</h5>
				<div class="card-body">

					<?= Bootstrap\Alert::flash(); ?>

					<div class="form-group row">
						<label class="col-3 col-form-label text-right">
							Login
						</label>
						<div class="col-9">
							<?= $rootAccount->input(
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
							<?= $rootAccount->select(
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
							<?= $rootAccount->select(
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
							<?= $rootAccount->input(
								'account_expiration_date', [
									'type'			=>'date',
									'placeholder'	=>'DD/MM/YYYY',
									'class'			=>'form-control'
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
									'type'		=>'password',
									'class'		=>'form-control',
									'minlength'	=>$minimumPasswordLength
								]
							); ?>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
