<?php use Polyfony\Form as Form; ?>
<div class="container-fluid">
	<div class="jumbotron row justify-content-center" style="padding:45px;">
		<div class="col-4">
			<h1>Emails</h1>
			<p>
				This form asks for your email, and sends you the Framework's README.md as an attachment.
				It will store the email in the database, and send it after the page has rendered. As to not negatively impact the user experience.
			</p>
			<p>
				You will not receive any email because in development, <strong>emails recipients are bypassed</strong> and sent to <code><strong>Config</strong>::get('email','bypass_email');</code> which is currently set to <code><?= Polyfony\Config::get('email','bypass_email'); ?></code>
			</p>
			
			<form action="" method="post">

				<div class="card card-default">
					
					<?= new Polyfony\Form\Token(); ?>
					
					<div class="card-header">
						<span class="fa fa-envelope"></span> 
						A simple <strong>mail form</strong>

						<button type="submit" class="btn btn-sm btn-link text-success float-right">
							Send it 
							<span class="fa fa-chevron-right"></span> 
						</button>

					</div>
					
					<div class="card-content" style="padding: 25px;">

						<?= Bootstrap\Alert::flash(); ?>

						<div class="form-group">
							<label for="exampleField">
								Type your email here
							</label>
							<?= Form::input('to_email', null, [
								'class'			=>'form-control',
								'placeholder'	=>'name@domain.com',
								'type'			=>'email'
							]); ?>
						</div>

					</div>				
					
				</div>
			</form>
		</div>
		<div class="col-6">

			<div class="card card-default">

				<div class="card-header">
					<code><strong>Emails</strong>::search()</code>
				</div>
				
				<?php $this->view('Emails/Table', ['emails'=>$emails]); ?>

			</div>
		</div>
	</div>
</div>