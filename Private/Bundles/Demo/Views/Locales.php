<div class="jumbotron" style="padding:45px;">
	<h1>Locales</h1>
	<p>
		The translation file is a CSV, using tabs and double-quotes.<br />
		Use as many as you need.
	</p>
	<code>
		../Private/Bundles/Demo/Locales/example.csv
	</code>
	<p>
		Get the translation for a key, using automatically detected language 
		<span class="label label-info"><?php echo Polyfony\Locales::get('house'); ?></span>
	</p>
	<code>
		echo Polyfony\Locales::get('house');
	</code>
	<p>
		Force another language temporarily 
		<span class="label label-info"><?php echo Polyfony\Locales::get('house','fr'); ?></span>
	</p>
	<code>
		echo Polyfony\Locales::get('house','fr');
	</code>
	<p>
		Switch to another language for this session
	</p>
	<code>
		Polyfony\Locales::setLanguage('fr');
	</code>
</div>
