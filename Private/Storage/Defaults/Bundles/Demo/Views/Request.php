<?php 
use Polyfony\Form as Form; 
use Polyfony\Request as Request;
?>
<div class="container">
	<div class="jumbotron row justify-content-center" style="padding:45px;">
		<div class="col-12">
			<h1>Request</h1>
			<p>
				Posting some things and getting them back<br />
				This example uses the <code>Request</code> abstraction, the <code>Form</code> helper (with XSS prevention), the <code>Bootstrap\Alert</code> object and the <code>Token</code> (CSRF prevention) feature.
			</p>
			
			<form action="" method="post">
				<div class="card card-default">
					
					<?= new Polyfony\Form\Token(); ?>
					
					<div class="card-header">A basic form</div>
					<div class="card-content" style="padding: 25px;">
					
						<?= Bootstrap\Alert::flash(); ?>

						<div class="form-group">

							<label for="exampleField">Example field</label>
							
							<?= Form::input(
								'test',
								Request::post('test', null),
								[
									'placeholder'	=>'Type something in there',
									'class'			=>'form-control',
									'id'			=>'exampleField'
								]
							); ?>

						</div>

						<button type="submit" class="btn btn-success">
							Post me !
						</button>

					</div>
					
					
				</div>
			</form>
		</div>
	</div>
</div>
