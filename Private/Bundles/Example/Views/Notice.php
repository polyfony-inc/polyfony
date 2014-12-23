<div style="padding: 25px;">

	<!-- Hard import CSS since Polyofny\Response has not reimplemented metas yet -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap-theme.min.css">

	<!-- Test our notice object -->
	<?php echo $this->Notice; ?>
	
	<!-- Test with a different storage/padding method -->
	<?php echo Polyfony\Store\Request::get('notice'); ?>

</div>