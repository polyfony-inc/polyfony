<?php


namespace Polyfony\Response;

class HTML {
	
	// store the list of links (css, favicons...)
	protected static $_links 	= [];

	// store the list of scripts (javascript)
	protected static $_scripts 	= [];

	// store the list of metas tags (title, description, robots...)
	protected static $_metas 	= [];

	// set links for the current HTML page
	public static function setLinks(array $links, bool $replace_existing=false) :void {
		// if we want to purge existing links
		!$replace_existing ?: self::$_links = []; 
		// href is the key for storing links
		foreach($links as $href_or_index => $attributes_or_href) {
			// if arguments are provided
			if(is_array($attributes_or_href)) {
				// if we have a stylesheet
				if(
					// if a rel is set, and it's not a stylesheet
					!array_key_exists('rel',$attributes_or_href) || 
					// and it's not a stylesheet
					$attributes_or_href['rel'] == 'stylesheet'
				) {
					// also rewrite the path of assets that used relative shortcuts
					$attributes_or_href['href'] = self::getRealAssetPath($href_or_index,'Css');
					// force the type
					$attributes_or_href['type'] = 'text/css';
					// force the rel
					$attributes_or_href['rel'] = 'stylesheet';
					// if the media key is implicit
					array_key_exists('media', $attributes_or_href) ?: $attributes_or_href['media'] = 'all';
					// push the slightly alternated stylesheet
					self::$_links[$href_or_index] = $attributes_or_href;
				}
				// otherwize we don't know what we're dealing with
				else {
					// just set its href
					$attributes_or_href['href'] = $href_or_index;
					// push that link and its attributes
					self::$_links[$href_or_index] = $attributes_or_href;
				}
			}
			// else only an href is provided
			else {
				// push that link alone
				self::$_links[$attributes_or_href] = [
					// we assume it is a stylesheet
					'rel'	=>'stylesheet',
					'type'	=>'text/css',
					// we assume it is a generic one
					'media'	=>'all',
					// and we set its href
					'href'	=>self::getRealAssetPath($attributes_or_href, 'Css')
				];
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
		$page = '<!doctype html><html lang="'.\Polyfony\Locales::getLanguage().'"><head><title>'.(isset(self::$_metas['title']) ? self::$_metas['title'] : '').'</title><meta http-equiv="content-type" content="text/html; charset=' . \Polyfony\Response::getCharset() . '" />';
		// add the meta tags and the links
		$page .= self::buildMetasTags() . self::buildLinksTags();
		// close the head, add the body, and add the scripts
		$page .= '</head><body>' . $content . self::buildScriptsTags();
		// add the profiler (if enabled)
		$page .= \Polyfony\Config::get('profiler', 'enable') ? new \Polyfony\Profiler\Html : '';
		// close the document and return the assembled html page
		return $page . '</body></html>';

	}

	// assets absoluteness detector
	private static function isAssetPathAbsolute(string $asset_path) :bool {

		// check is that asset is external to our server, or absolute (suspicously dynamic)
		return (
			substr($asset_path,0,1) == '/' || 
			substr($asset_path,0,4) == 'http'
		);

	}

	// assets externalness detector
	private static function isAssetPathRemote(string $asset_path) :bool {

		// check is that asset is external to our server, or absolute (suspicously dynamic)
		return (
			substr($asset_path,0,2) == '//' || 
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

	// allow to convert an asset path to a path that file_get_contents can access
	private static function getAssetContents(string $file_path, string $asset_type) :string {

		// modify the filename to allow getting external files
		if(substr($file_path, 0,2) == '//') {
			$file_path = "https:{$file_path}"; 
		}
		// and absolute path files too
		elseif(substr($file_path,0,1) == '/') {
			$file_path = ".{$file_path}";
		}
		// the file is local, but still has a partial path
		else {
			$file_path = "./Assets/{$asset_type}/".$file_path;
		}
		// return the proper path
		return file_get_contents($file_path);

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

		// this is where formatted links tags go
		$links = [];
		// pack and minify
		self::packAndMinifyLinks();
		// for each available link
		foreach(self::$_links as $href => $attributes) {
			// sort the attributes (compulse order needs)
			ksort($attributes);
			// build as base stylesheet link and merge its attributes
			$links[] = new \Polyfony\Element('link', $attributes);
		}
		return implode('', $links);

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
			// generate a unique name for a pack that contains that list of scripts only
			list($pack_name, $path_path) = 
				self::createPackingNameAndPathFor(self::$_scripts,'Js');
			// if the pack file doesn't exist yet or if we're not allowed to use cache in response
			if(
				// the packed file doesn't exist yet
				!file_exists($path_path) || 
				// or it may exist, but we are not allowed to use cached element in our response
				!\Polyfony\Config::get('response','cache') || 
				// or if the request explicitely disallowed cached elements
				!\Polyfony\Request::isCacheAllowed()
			) {
				// create the packing directories if they don't already exist
				self::createPackingDirectoriesFor('Js');
				// the contents of the pack
				$pack_contents = '';
				// for each asset
				foreach(self::$_scripts as $file) {
					// append he contents of that file to the pack
					$pack_contents .= " \n".self::getAssetContents($file, 'Js');
				}
				// if minifying is allowed
				if(\Polyfony\Config::get('response','minify')) {
					// instanciate a new minifier, add contents, and minify it
					$pack_contents = (new \MatthiasMullie\Minify\JS)
						->add($pack_contents)
						->minify();
				}
				// populate the cache file
				file_put_contents($path_path, $pack_contents);
			}
			// replace the script list
			self::$_scripts = ["/Assets/Js/Cache/{$pack_name}"];
		}
	}

	private static function getStylesheetsLinksSortedByMedia() :array {
		// we'll split the stylesheets links by media attribute
		$stylesheets_by_media = [];
		// iterate over links
		foreach(self::$_links as $index => $attributes) {
			// if the link is a stylesheet and it is local
			if(
				// is it a stylesheet ?
				$attributes['rel'] == 'stylesheet' && 
				// is it a local one ?
				!self::isAssetPathRemote($attributes['href'])
			) {
				// spool it by its media
				$stylesheets_by_media[$attributes['media']][] = $attributes;
				// and remove it from the list of links to build
				unset(self::$_links[$index]);
			}
		}
		// return both
		return $stylesheets_by_media;
	}

	private static function packAndMinifyLinks() :void {

		// if we are allowed to pack js files
		if(
			\Polyfony\Config::isProd() && 
			\Polyfony\Config::get('response','pack_css') == '1'
		) {
			// get the list of stylesheets, sorted by media type, to pack them together
			$stylesheets_by_media = self::getStylesheetsLinksSortedByMedia();
			// now that we have sorted links by type and medias, we can pack them by medias
			foreach($stylesheets_by_media as $media => $list_of_stylesheets) {
				// define a name and path for that pack
				list($pack_name, $pack_path) = 
					self::createPackingNameAndPathFor($list_of_stylesheets, 'Css');
				// if the file does not exist, or if we're not allowed to use cached items
				if(
					// if the file does not exist
					!file_exists($pack_path) || 
					// or if we're not allowed to use cached items
					!\Polyfony\Config::get('response','cache') ||
					// of if the request explicitely disallows that
					!\Polyfony\Request::isCacheAllowed()
				) {
					// create the packing directories if they don't already exist
					self::createPackingDirectoriesFor('Css');
					// the contents of the pack
					$pack_contents = '';
					// foreach stylesheet for this media
					foreach($list_of_stylesheets as $attributes) {
						// get the contents of that stylesheet
						$pack_contents .= self::getAssetContents($attributes['href'], 'Css');
					}
					// if minifying is allowed
					if(\Polyfony\Config::get('response','minify')) {
						// instanciate a new minifier, add contents, and minify it
						$pack_contents = (new \MatthiasMullie\Minify\CSS)
							->add($pack_contents)
							->minify();
					}
					// then the pack
					file_put_contents($pack_path, $pack_contents);
				}
				// add our pack to the list of links
				self::$_links[] = [
					'href'	=>"/Assets/Css/Cache/{$pack_name}",
					'type'	=>'text/css',
					'media'	=>$media,
					'rel'	=>'stylesheet'
				];
				// free up memory
				unset($stylesheets_by_media[$media], $pack_contents);
			}

		}

	}

	public static function createPackingNameAndPathFor(array $assets, string $asset_type) :array {
		// generate a unique name for that list of assets, and the right extension
		$name = \Polyfony\Keys::generate($assets).'.'.strtolower($asset_type);
		// generate a private path to store that file
		$path = "../Private/Storage/Cache/Assets/{$asset_type}/{$name}";
		// return both
		return [$name, $path];
	}

	// initialize the packing of assets (directory structure)
	public static function createPackingDirectoriesFor(string $asset_type) :void {
		// if it doesn't exist create a folder to store packed files
		is_dir("../Private/Storage/Cache/Assets/{$asset_type}/") ?: 	
			mkdir("../Private/Storage/Cache/Assets/{$asset_type}/", 0777, true);
		// if the general public assets folder doesn't exist we create it
		is_dir("./Assets/{$asset_type}/") ?: 	
			mkdir("./Assets/{$asset_type}/", 0777, true);
		// create a link from the public to the private place where caches are generated
		is_link("./Assets/{$asset_type}/Cache") ?: 	
			symlink("../../../Private/Storage/Cache/Assets/{$asset_type}/", "./Assets/{$asset_type}/Cache");
	}

}

?>
