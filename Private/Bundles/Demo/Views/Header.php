<ol class="breadcrumb" style="margin:0;padding-left:45px;border-radius:0;">
	<li class="breadcrumb-item ">
		<a href="/" >Index</a>
	</li>
	<?php if(Polyfony\Router::getCurrentRoute()->name == 'demo'): ?>
		<li class="breadcrumb-item">
			<a href="/demo/">Demo</a>
		</li>
		<?php if($this->CurrentTab): ?>
			<li class="breadcrumb-item active">
				<?php echo ucfirst($this->CurrentTab); ?>
			</li>
		<?php endif; ?>
	<?php endif; ?>
</ol>
