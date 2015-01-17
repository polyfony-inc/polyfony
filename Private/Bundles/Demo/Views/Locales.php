<div class="jumbotron" style="padding:45px;">
	<h1>Locales</h1>
	<p>
		The translation file is a CSV (use as many as you like), using tabs and double-quotes.<br />
		This example uses the <code>Locales</code> class.
	</p>
	<code>
		../Private/Bundles/Demo/Locales/example.csv
	</code>
	<p>
		Get the translation for a key, using automatically detected language 
		<span class="label label-info"><?php echo Polyfony\Locales::get('house'); ?></span>
	</p>
	<code>
		echo Locales::get('house');
	</code>
	<p>
		Force another language temporarily 
		<span class="label label-info"><?php echo Polyfony\Locales::get('house','fr'); ?></span>
	</p>
	<code>
		echo Locales::get('house','fr');
	</code>
	<p>
		Switch to another language for this session
	</p>
	<code>
		Locales::setLanguage('fr');
	</code>
</div>
