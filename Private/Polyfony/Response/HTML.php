<?php

namespace Polyfony\Response;

use Polyfony\{ 
	Format, 
	Response, 
	Config, 
	Locales, 
	Element 
};

class HTML {
	
	// store the list of links (css, favicons...)
	protected static array $_links 		= [];
	// store the list of scripts (javascript)
	protected static array $_scripts 	= [];
	// store the list of metas tags (title, description, robots...)
	protected static array $_metas 		= [];

	// set links for the current HTML page
	public static function setLinks(
		array $links, 
		bool $replace_existing=false
	) :void {
		// if we want to purge existing links
		!$replace_existing ?: self::$_links = []; 
		// href is the key for storing links
		foreach(
			$links as 
			$href_or_index => $attributes_or_href
		) {
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
					$attributes_or_href['href'] 	= self::getPublicAssetPath(
						$href_or_index, 
						'Css'
					);
					// force the type
					$attributes_or_href['type'] 	= 'text/css';
					// force the rel
					$attributes_or_href['rel'] 		= 'stylesheet';
					// if the media key is implicit
					array_key_exists(
						'media', 
						$attributes_or_href
					) ?: $attributes_or_href['media'] = 'all';
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
			// else only an href is provided, we assume it is a stylesheet
			else {
				// push that link alone
				self::$_links[$attributes_or_href] = [
					// we assume it is a generic all media stylesheet
					'rel'	=>'stylesheet',
					'type'	=>'text/css',
					'media'	=>'all',
					// and we set its href
					'href'	=>self::getPublicAssetPath($attributes_or_href, 'Css')
				];
			}
		}
	}

	// set scripts for the current HTML page
	public static function setScripts(
		array $scripts, 
		bool $replace_existing=false
	) :void {
		// if we want to purge existing scripts
		!$replace_existing ?: self::$_scripts = [];
		// for each script we have to set
		foreach($scripts as $script) {
			// rewrite its path
			self::$_scripts[] = self::getPublicAssetPath($script, 'Js');
		}
	}


	// set metas for the current HTML page
	public static function setMetas(
		array $metas, 
		bool $replace_existing=false
	) :void {
		// if we want to purge existing metas
		!$replace_existing ?: self::$_metas = [];
		// name is the key for storing metas
		self::$_metas += $metas;
	}

	// shortcuts to quickly set multiple things
	public static function set(
		array $assets, 
		bool $replace_existing=false
	) :void {
		// for each batch
		foreach(
			$assets as 
			$category => $scripts_or_links_or_metas
		) {
			// if the batch is scripts
			if($category == 'scripts') {
				self::setScripts(
					$scripts_or_links_or_metas, 
					$replace_existing
				);
			}
			// if the batch is links
			elseif($category == 'links') {
				self::setLinks(
					$scripts_or_links_or_metas, 
					$replace_existing
				);
			}
			// if the match is metas
			elseif($category == 'metas') {
				self::setMetas(
					$scripts_or_links_or_metas, 
					$replace_existing
				);
			}
		}
	}

	// build an html page
	public static function buildAndGetPage(
		string $content
	) :string {
		
		// initial content
		$page = 
			'<!doctype html><html lang="'.
			Locales::getLanguage().
			'"><head><title>'.
			Format::htmlSafe(isset(self::$_metas['title']) ? self::$_metas['title'] : '').
			'</title><meta http-equiv="content-type" content="text/html; charset=' . 
			Response::getCharset() . '" />';

		// add the meta tags and the links
		$page .= self::buildMetasTags() . self::buildLinksTags();
		// close the head, add the body, and add the scripts
		$page .= '</head><body>' . $content . self::buildScriptsTags();
		// add the profiler (if enabled)
		$page .= Config::get('profiler', 'enable') ? new \Polyfony\Profiler\HTML : '';
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
			$metas .= 
				'<meta name="'.$name.'" content="'.
				Format::htmlAttributeSafe($content).'" />';
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
		foreach(
			self::$_links as 
			$href => $attributes
		) {
			// sort the attributes (compulse order needs)
			ksort($attributes);
			// build as base stylesheet link and merge its attributes
			$links[] = new Element('link', $attributes);
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
			// add the formated the script
			$scripts .= '<script type="text/javascript" src="'.$src.'"></script>';
		}
		return $scripts;
	}

	private static function packAndMinifyScripts() :void {
		// if we are allowed to pack js files
		if(self::isPackingOfTheseAssetsAllowed('js')) {
			// generate a unique name for a pack that contains that list of scripts only
			list(
				$pack_name, 
				$path_path
			) = self::getPackingNameAndPathFor(self::$_scripts,'Js');
			// if the pack file doesn't exist yet or if we're not allowed to use cache in response
			if(self::doesThisPackNeedRegeneration($path_path)) {
				// create the packing directories if they don't already exist
				self::createPackingDirectoriesFor('Js');
				// the contents of the pack
				$pack_contents = '';
				// for each asset
				foreach(self::$_scripts as $index => $file) {
					// if that file is not remote we can include it in the pack
					if(!self::isAssetPathRemote($file)) {
						// append he contents of that file to the pack
						$pack_contents .= " \n".file_get_contents('.'.$file);
					}
				}
				// populate the cache file
				file_put_contents(
					$path_path, 
					self::getMinifiedPackIfAllowed(
						$pack_contents,  
						new \MatthiasMullie\Minify\JS
					)
				);
			}
			// for each asset
			foreach(self::$_scripts as $index => $file) {
				// if that file is not remote it has already been included in the pack
				if(!self::isAssetPathRemote($file)) {
					// and we remove the origin from the list of script to include
					unset(self::$_scripts[$index]);
				}
			}
			// add the pack in addition to already existing scripts
			self::$_scripts[] = "/Assets/Js/Cache/{$pack_name}";
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
		if(self::isPackingOfTheseAssetsAllowed('css')) {
			// now that we have sorted links by type and medias, we can pack them by medias
			foreach(self::getStylesheetsLinksSortedByMedia() as $media => $list_of_stylesheets) {
				// define a name and path for that pack
				list($pack_name, $pack_path) = self::getPackingNameAndPathFor($list_of_stylesheets, 'Css');
				// if the file does not exist, or if we're not allowed to use cached items
				if(self::doesThisPackNeedRegeneration($pack_path)) {
					// create the packing directories if they don't already exist
					self::createPackingDirectoriesFor('Css');
					// the contents of the pack
					$pack_contents = '';
					// foreach stylesheet for this media
					foreach($list_of_stylesheets as $attributes) {
						// get the contents of that stylesheet
						$pack_contents .= file_get_contents('.'.$attributes['href']);
					}
					// then add to the pack
					file_put_contents($pack_path, self::getMinifiedPackIfAllowed($pack_contents, new \MatthiasMullie\Minify\CSS));
				}
				// add our pack to the list of links
				self::$_links[] = [
					'href'	=>"/Assets/Css/Cache/{$pack_name}",
					'type'	=>'text/css',
					'media'	=>$media,
					'rel'	=>'stylesheet'
				];
				// free up memory
				unset($pack_contents);
			}
		}
	}

	// assets externalness detector
	private static function isAssetPathRemote(string $asset_path) :bool {
		// check is that asset is external to our server, or absolute (suspicously dynamic)
		return 
			substr($asset_path,0,2) == '//' || 
			substr($asset_path,0,4) == 'http';
	}

	private static function isPackingOfTheseAssetsAllowed(
		string $asset_type
	) :bool {
		// we have to be in prod, and the packing of this type of assets has to be allowed
		return 
			Config::get('response','pack_'.$asset_type) == '1';
	}

	// if this pack needs to be generated again
	private static function doesThisPackNeedRegeneration(
		string $pack_path
	) :bool {
		return 
			// if the file does not exist
			!file_exists($pack_path) || 
			// or if we're not allowed to use cached items
			!Config::get('response','cache');
	}

	// assets path converter
	private static function getPublicAssetPath(
		string $asset_path, 
		string $asset_type
	) :string {
		// if the asset path is absolute in any way
		return (
			substr($asset_path,0,1) == '/' || 
			substr($asset_path,0,4) == 'http'
		) ?
			// it is returned as is, // otherwise it is made relative to the Assets folder
			$asset_path : "/Assets/{$asset_type}/$asset_path";
	}

	public static function getPackingNameAndPathFor(
		array $assets, 
		string $asset_type
	) :array {
		// generate a unique name for that list of assets, and the right extension
		$name = \Polyfony\Hashs::get($assets).'.'.strtolower($asset_type);
		// generate a private path to store that file
		$path = "../Private/Storage/Cache/Assets/{$asset_type}/{$name}";
		// return both
		return [$name, $path];
	}

	// minify the packed content if it's allowed, otherwise return is at is
	private static function getMinifiedPackIfAllowed(
		string $pack_contents, 
		object $packer_object=null
	) :string {
		// if we are allowed to minify
		return Config::get('response','minify') ? 
			($packer_object)->add($pack_contents)->minify() : 
			$pack_contents;

	}

	// initialize the packing of assets (directory structure)
	public static function createPackingDirectoriesFor(
		string $asset_type
	) :void {
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

	// push the assets using HTTP/2 
	public static function pushAssets() :void {
		// a list of assets, in a format suited for the Response::push() method
		$assets = [];
		// for each link type asset of the current webpage
		foreach(self::$_links as $link) {
			// if it's CSS, declare it as such
			if($link['type'] == 'text/css') {
				$assets[$link['href']] = 'style';
			}
			// if it's an image, declare it as such
			elseif(stripos($link['type'], 'image') !== false) {
				$assets[$link['href']] = 'image';
			}
		}
		// for each script type asset of the current webpage
		foreach(self::$_scripts as $script) {
			// declare it as a script file
			$assets[$script] = 'script';
		}
		// push all those using HTTP/2
		Response::push($assets);

	}

}

?>
