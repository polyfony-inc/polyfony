<div class="jumbotron" style="padding:45px;">
	<h1>Request</h1>
	<p>
		Posting some things and getting them back<br />
		This example uses the <code>Request</code> abstraction, the <code>Form</code> helper, the <code>Alert</code> object and the <code>Token (CSRF prevention)</code> feature.
	</p>
	
	<form action="" method="post">
	<div class="panel panel-default">
		<?= new Polyfony\Form\Token(); ?>
		<div class="panel-heading">A basic form</div>
		<div class="panel-content" style="padding: 25px;">
			<div class="form-group">
				<label for="exampleField">Example field</label>
				<?php echo $this->InputExample; ?>
			</div>
			<button type="submit" class="btn btn-success">
				Post me !
			</button>
		</div>
		
		
	</div>
	</form>
	<?php if($this->Feedback): ?>
		<?php echo $this->Feedback; ?>
	<?php endif; ?>
</div>
