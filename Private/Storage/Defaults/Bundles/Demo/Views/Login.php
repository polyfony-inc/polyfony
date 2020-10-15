<?php 
use Polyfony\Form as Form; 
use Polyfony\Config as Config;
?>
<div class="container">
	<div style="padding:25px;" class="row justify-content-center">
		<div class="col-lg-6">
			<div class="card panel-default">

				<div class="card-header">
					<span class="fa fa-lock"></span> 
					Please log-in
				</div>

				<div class="card-body">

					<?= Bootstrap\Alert::flash(); ?>
					
					<form action="/demo/secure/" method="post">
						
						<?= new Polyfony\Form\Token; ?>
						
						<div class="form-group">
							
							<label for="inputLogin">
								Login
							</label>
							
							<?= Form::input(Config::get('security','login'), null, [
								'class'			=>'form-control',
								'id'			=>'inputLogin',
								'placeholder'	=>'Email',
								'type'			=>'email'
							]); ?>

						</div>
						<div class="form-group">

							<label for="inputPassword">
								Password
							</label>

							<?= Form::input(Config::get('security','password'), null, [
								'class'			=>'form-control',
								'id'			=>'inputPassword',
								'placeholder'	=>'*************',
								'type'			=>'password'
							]); ?>

						</div>
						<div class="form-group">
							<label for="inputPassword">Captcha</label>
							<div class="row">
								<div class="col-6">
									<?= Polyfony\Form\Captcha::input([
										'class'=>'form-control',
										'placeholder'=>'Type captcha here'
									]); ?>
								</div>
								<div class="col-6">
									<?= new Polyfony\Form\Captcha(4); ?>
								</div>
							</div>
						</div>
						<button type="submit" class="btn btn-success">
							Log me in
						</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>