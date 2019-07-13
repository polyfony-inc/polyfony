<?php use Polyfony\Request as Request; ?>

<ol class="breadcrumb" style="margin:0;padding-left:45px;border-radius:0;">
	
	<li class="breadcrumb-item ">
		<a href="/" >Index</a>
	</li>
	
	<?php if(Polyfony\Router::getCurrentRoute()->name == 'demo'): ?>
		
		<li class="breadcrumb-item">
			<a href="/demo/">Demo</a>
		</li>
		
		<?php if(Request::get('category', null)): ?>
			<li class="breadcrumb-item active">
				<?php /* This is only safe because of the route's category restrictions */ ?>
				<?= ucfirst(Request::get('category', null)); ?>
			</li>
		<?php endif; ?>

	<?php endif; ?>

</ol>

<?= Bootstrap\Alert::flash(); ?>
