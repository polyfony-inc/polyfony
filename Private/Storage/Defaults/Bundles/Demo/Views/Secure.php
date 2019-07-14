<div class="jumbotron" style="padding:45px;">
	<h1>Security</h1>
	<p>
		If you see this, <strong>you are authenticated !</strong><br />
		You are logged is as <code>root</code>, bellow are your credentials
	</p>
	<p>
		id <code><?php echo $this->Id; ?></code>, level <code><?php echo $this->Level; ?></code>, 
		login <code><?php echo $this->Login; ?></code></code>
	</p>
	<p>
		To close your session, <a href="/demo/disconnect/">just click here</a>
	</p>
</div>
