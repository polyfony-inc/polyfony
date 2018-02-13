<div style="padding:25px;">
	<div class="panel panel-default">
		<div class="panel-heading">Login</div>
		<div class="panel-body">
			<?php echo $this->Notice; ?>
			<form action="/demo/secure/" method="post">
				<?= new Polyfony\Form\Token(); ?>
				<div class="form-group">
					<label for="inputLogin">Login</label>
					<?php echo $this->LoginInput; ?>
				</div>
				<div class="form-group">
					<label for="inputPassword">Password</label>
					<?php echo $this->PasswordInput; ?>
				</div>
				<button type="submit" class="btn btn-success">
					Log me in
				</button>
			</form>
		</div>
   	 </div>
</div>
