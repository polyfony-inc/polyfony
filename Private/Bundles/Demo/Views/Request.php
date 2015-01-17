<div class="jumbotron" style="padding:45px;">
	<h1>Request</h1>
	<p>
		Posting some things and getting them back<br />
		This example uses the <code>pf\Request</code> abstraction, the <code>pf\Form</code> helper and the <code>pf\Notice</code> object.
	</p>
	
	<form action="" method="post">
	<div class="panel panel-default">
		
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
