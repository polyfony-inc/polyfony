<?php use Polyfony\Locales as Loc; ?>
<div class="jumbotron" style="padding:45px;">
	<h1>Locales</h1>
	<p>
		The translation file is a CSV (use as many as you like), using tabs and double-quotes.<br />
		This example uses the <code>Locales</code> class.
	</p>
	<code>
		../Private/Bundles/Demo/Locales/example.csv
	</code>

	<hr />
	<p>
		Get the translation for a key, using automatically detected language 
	</p>
	<code class="label label-info">
		echo Locales::get('house');<br />
		<span class="text-primary"><?= Loc::get('house'); ?></span>
	</code>

	<hr />

	<p>
		Get the translation for a key, using automatically detected language and with variables in the translation
	</p>
	<code class="label label-info">
		echo Locales::get('unsupported_format', null, ['_format_'=>'text/css']);<br />
		<span class="text-primary"><?= Loc::get('unsupported_format', null, ['_format_'=>'text/css']); ?></span>
	</code>
	
	<hr />
	<p>
		Force another language temporarily 
	</p>
	<code class="label label-info">
		echo Locales::get('house','fr');<br />
		<span class="text-primary"><?= Loc::get('house','fr'); ?></span>
	</code>
	
	<hr />

	<p>
		Switch to another language for this session
	</p>
	<code>
		Locales::setLanguage('fr');
	</code>
</div>
