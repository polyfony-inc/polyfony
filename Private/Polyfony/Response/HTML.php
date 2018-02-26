<?php


namespace Polyfony\Response;

class HTML {
	
	// store the list of links (css, favicons...)
	protected static $_links 	= [];

	// store the list of scripts (javascript)
	protected static $_scripts 	= [];

	// store the list of metas tags (title, description, robots...)
	protected static $_metas 	= [];

	// assets externalness/absoluteness detector
	private static function isAssetPathAbsolute(string $asset_path) :bool {

		// check is that asset is external to our server, or absolute (suspicously dynamic)
		return (
			substr($asset_path,0,1) == '/' || 
			substr($asset_path,0,4) == 'http'
		);

	}

	// assets path converter
	private static function getRealAssetPath(string $asset_path, string $asset_type) :string {

		// if the asset path is absolute in any way
		return self::isAssetPathAbsolute($asset_path) ?
			// it is returned as is
			$asset_path : 
			// otherwise it is made relative to the Assets folder
			"/Assets/{$asset_type}/$asset_path";

	}

	// set links for the current HTML page
	public static function setLinks(array $links, bool $replace_existing=false) :void {
		// if we want to purge existing links
		!$replace_existing ?: self::$_links = []; 
		// href is the key for storing links
		foreach($links as $href_or_index => $attributes_or_href) {
			// if arguments are provided
			if(is_array($attributes_or_href)) {
				// push that link and its attributes
				self::$_links[$href_or_index] = $attributes_or_href;
			}
			// else only an href is provided
			else {
				// push that link alone
				self::$_links[$attributes_or_href] = [];
			}
		}
	}

	// set scripts for the current HTML page
	public static function setScripts(array $scripts, bool $replace_existing=false) :void {
		// if we want to purge existing scripts
		!$replace_existing ?: self::$_scripts = [];
		// src is the key for storing scripts
		self::$_scripts = array_merge(self::$_scripts, $scripts);
	}

	// set metas for the current HTML page
	public static function setMetas(array $metas, bool $replace_existing=false) :void {
		// if we want to purge existing metas
		!$replace_existing ?: self::$_metas = [];
		// name is the key for storing metas
		self::$_metas += $metas;
	}

	// shortcuts to quickly set multiple things
	public static function set(array $assets) :void {
		// for each batch
		foreach($assets as $category => $scripts_or_links_or_metas) {
			// if the batch is scripts
			if($category == 'scripts') {
				self::setScripts($scripts_or_links_or_metas);
			}
			// if the batch is links
			elseif($category == 'links') {
				self::setLinks($scripts_or_links_or_metas);
			}
			// if the match is metas
			elseif($category == 'metas') {
				self::setMetas($scripts_or_links_or_metas);
			}
		}
	}

	// build an html page
	public static function buildAndGetPage(string $content) :string {

		// initial content
		$page = '<!doctype html><html lang="'.\Polyfony\Locales::getLanguage().'"><head><meta http-equiv="content-type" content="text/html; charset=' . \Polyfony\Response::getCharset() . '" />';
		// add the meta tags and the links
		$page .= self::buildMetasTags() . self::buildLinksTags();
		// close the head, add the body, and add the scripts
		$page .= '</head><body>' . $content . self::buildScriptsTags();
		// add the profiler (if enabled)
		$page .= \Polyfony\Config::get('profiler', 'enable') ? new \Polyfony\Profiler\Html : '';
		// close the document and return the assembled html page
		return $page . '</body></html>';

	}

	// builds meta code
	private static function buildMetasTags() :string {

		// this is where formatted meta tags go
		$metas = '';
		// for each available meta
		foreach(self::$_metas as $name => $content) {
			// add the formated the meta
			$metas .= '<meta name="'.$name.'" value="'.\Polyfony\Format::htmlSafe($content).'" />';
		}

		return $metas;

	}

	// build links code
	private static function buildLinksTags() :string {

		// PACKER AND MINIFIER MISSING

		// this is where formatted links tags go
		$links = '';
		// for each available script
		foreach(self::$_links as $href => $attributes) {
			// if attribute exist and say that the rel is not stylesheet, we are not dealing with css
			if(array_key_exists('rel', $attributes) && $attributes['rel'] != 'stylesheet') {
				// build a base link tag and appen attributes
				$links .= (string) new \Polyfony\Element('link', array_merge([
					'href'	=>$href
				], $attributes));
			}
			// we are dealing with a css file
			else {
				// build as base stylesheet link and merge its attributes (is any)
				$links .= (string) new \Polyfony\Element('link', array_merge([
					'rel'	=>'stylesheet',
					'media'	=>'all',
					'href'	=>self::getRealAssetPath($href,'Css')
				], $attributes));
			}
		}

		return $links;

	}

	private static function buildScriptsTags() :string {
		// this is where formatted scripts tags go
		$scripts = '';
		// deduplicate scripts
		self::$_scripts = array_unique(self::$_scripts);
		// pack and minify
		self::packAndMinifyScripts();
		// for each available script
		foreach(self::$_scripts as $src) {
			// add the formated the meta
			$scripts .= '<script type="text/javascript" src="'.self::getRealAssetPath($src,'Js').'"></script>';
		}

		return $scripts;

	}

	private static function packAndMinifyScripts() :void {

		// if we are allowed to pack js files
		if(
			\Polyfony\Config::isProd() && 
			\Polyfony\Config::get('response','pack_js') == '1'
		) {
			// generate a unique name for the packed css files
			$js_pack_name = \Polyfony\Keys::generate(self::$_scripts) . '.js';
			$js_pack_file = "../Private/Storage/Cache/Assets/Js/{$js_pack_name}";
			// if the pack file doesn't exist yet or if we're not allowed to use cache in response
			if(!file_exists($js_pack_file) || !\Polyfony\Config::get('response','cache')) {
				// the content of the pack
				$js_pack_contents = '';
				// for each asset
				foreach(self::$_scripts as $file) {
					// modify the filename to allow getting external files
					if(substr($file, 0,2) == '//') {
						$file = "https:{$file}"; 
					}
					// and absolute path files too
					elseif(substr($file,0,1) == '/') {
						$file = ".{$file}";
					}
					// the file is local, but has a partial path
					else {
						$file = "./Assets/Js/".$file;
					}
					// append he contents of that file to the pack
					$js_pack_contents .= " \n".file_get_contents($file);
				}
				// if minifying is allowed
				if(\Polyfony\Config::get('response','minify')) {
					// instanciate a new minifier
					$minifier = new \MatthiasMullie\Minify\JS();
					// add our css contents
					$minifier->add($js_pack_contents);
					// minify 
					$js_pack_contents = $minifier->minify();
				}
				// populate the cache file
				file_put_contents($js_pack_file, $js_pack_contents);
			}
			// replace the script list
			self::$_scripts = ["/Assets/Js/Cache/{$js_pack_name}"];
		}
	}

	// initialize the packing of assets (directory structure)
	public static function createAssetsPackingDirectories() :void {

		// if we are allowed to use the assets packing feature
		if(
			\Polyfony\Config::isProd() && 
			(
				\Polyfony\Config::get('response','pack_css') == 1 || 
				\Polyfony\Config::get('response','pack_js') == 1 
			)
		) {
			// create css and js packing cache directories if they don't exist yet
			is_dir('../Private/Storage/Cache/Assets/Css/') ?: 	
				mkdir('../Private/Storage/Cache/Assets/Css/', 0777, true);
			is_dir('../Private/Storage/Cache/Assets/Js/') ?: 	
				mkdir('../Private/Storage/Cache/Assets/Js/', 0777, true);
			// if the general assets file do not exist
			is_dir('./Assets/Css/') ?: 	
				mkdir('./Assets/Css/', 0777, true);
			is_dir('./Assets/Js/') ?: 	
				mkdir('./Assets/Js/', 0777, true);
			// create css and js public symlinks if it doesn't exist already
			is_link('./Assets/Css/Cache') ?: 	
				symlink('../../../Private/Storage/Cache/Assets/Css/', './Assets/Css/Cache');
			is_link('./Assets/Js/Cache') ?: 	
				symlink('../../../Private/Storage/Cache/Assets/Js/', './Assets/Js/Cache');
		}

	}

}

?>
