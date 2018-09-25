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
