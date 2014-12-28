<ol class="breadcrumb" style="margin:0;padding-left:45px;border-radius:0;">
	<li>
		<a href="/">Index</a>
	</li>
	<?php if(Polyfony\Router::getCurrentRoute()->name == 'demo'): ?>
		<li>
			<a href="/demo/">Demo</a>
		</li>
		<?php if($this->CurrentTab): ?>
			<li class="active">
				<?php echo ucfirst($this->CurrentTab); ?>
			</li>
		<?php endif; ?>
	<?php endif; ?>
</ol>
