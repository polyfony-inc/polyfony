<?php use Polyfony\Form as Form; ?>
<div class="jumbotron row justify-content-center" style="padding:45px;">
	<div class="col-4">
		<h1>Emails</h1>
		<p>
			This form asks for your email, and sends you the Framework's README.md as an attachment.
			Try it, twice. Once with "save me to the database" and once without, then look at the controller's method.
		</p>
		<p>
			You will not receive any email because in development, <strong>emails recipients are bypassed</strong> and sent to <code><strong>Config</strong>::get('email','bypass_email');</code> which is currently set to <?= Polyfony\Config::get('email','bypass_email'); ?>
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

					<div class="form-group">
						<label for="exampleField">
							Save email in the DB ?
						</label>
						<?= Form::checkbox('save_me', false, ['class'=>'form-control']); ?>
						<small>
							Saving emails to the database has two main use cases : allowing to send them at a later time and allowing to search in sent emails to help users who didn't receive it and/or manually re-send them.
						</small>
					</div>

					<hr />

				</div>				
				
			</div>
		</form>
	</div>
	<div class="col-6">

		<div class="card card-default">

			<div class="card-header">
				<code><strong>Emails</strong>::search()</code>
			</div>
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
					<?php foreach($this->emails as $email): ?>
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
		</div>
	</div>
</div>
