<div class="jumbotron" style="padding:45px;">
	<h1>Tools</h1>
	<p>This bundle provides some usefull functionnalities.<br />
	You should probably keep it <small>(it provides the exception route)</small>.</p>
	<p>
		<a class="btn btn-warning btn-lg" href="/tools/generateSymlinks/" role="button">Generate assets symlinks</a> 
		<a class="btn btn-warning btn-lg" href="/tools/generateBundle/" role="button">Generate CRUD bundle</a> 
		<a class="btn btn-warning btn-lg" href="/tools/purgeCache/" role="button">Purge cache</a> 
		<a class="btn btn-warning btn-lg" href="/tools/checkConfiguration/" role="button">Check configuration</a>
	</p>
	<?php echo isset($this->notice) ? $this->notice : ''; ?>
	<?php echo isset($this->errors) ? $this->errors : ''; ?>
</div>
