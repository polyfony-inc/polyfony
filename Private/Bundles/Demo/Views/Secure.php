<div class="jumbotron" style="padding:45px;">
	<h1>Security</h1>
	<p>
		If you see this, <strong>you are authenticated !</strong><br />
		You are logged is as <code>root</code>, your session will expire on <span class="label label-success"><?php Polyfony\Security::get('login'); ?></span>
	</p>
	<code>
		login :
		level :
		session expiration date :
	</code>
</div>
