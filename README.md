[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/mini.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0) [![Maintainability](https://api.codeclimate.com/v1/badges/dcb85f03d218814504ac/maintainability)](https://codeclimate.com/github/polyfony-inc/polyfony/maintainability)

## Polyfony is an intuitive, light and powerful PHP micro-framework.

#### Philosophy
Inspired by Symfony and Laravel but tailored to favour an inclination towards extreme simplicity and efficiency.  
Compared to major PHP frameworks, Polyfony covers 95%+ of what we need most of the time, and does so using a fragment of the ressources, space, configuration files and dependencies required by major frameworks.  
It features routing, bundles, controllers, profiler, views, ORM, tests, caches, locales, events, authentication, environments, form helper, CLI helper... and extensibility via composer.

#### Footprint [of an Hello World](https://github.com/polyfony-inc/polyfony/wiki/Benchmark)
* ≤ 300 Ko of disk space *(35% of comment lines)*
* ≤ 400 Ko of RAM
* ≤ 2.5 ms (cold)

## Requirements
You need a POSIX compatible system (Linux/MacOS/xBSD), PHP >= 7.1 with ext-pdo, ext-sqlite3, ext-mbstring, ext-msgpack and a rewrite module for your webserver. 

## Installation

###### To download & preconfigure the framework in *your-project-folder*

```
composer create-project --stability=dev polyfony-inc/polyfony your-project-folder
```
*--stability=dev is mandatory since we don't publish releases*

###### NginX configuration
```
root /var/www/your-project-folder/Public
location / {
	try_files $uri $uri/ /index.php?$query_string;
}
```

###### LigHTTPd configuration
```
server.document-root 	= "/var/www/your-project-folder/Public/"
url.rewrite-once 		= ("^(?!/Assets/).*" => "/?")
```

###### Apache configuration
```
DocumentRoot "/var/www/your-project-folder/Public/"
```

## Almost no learning required

This *readme.md* file should be enough to get you started, you can also browse the `Private/Bundles/Demo/` bundle and browse the Framework's source code.
As the framework classes are static, everything is **always available, everywhere** thru simple and natural naming.

*The code bellow assumes you are prefixing the `Polyfony` namespace before each call.*

### [Request](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyrequest)

```php
// retrieve an url parameter
Request::get('blog_post_id');

// retrieve a posted field named `search_expression`
Request::post('search_expression');

// retrieve a posted file
Request::files('attachment_document');

// retrieve a request header
Request::header('Accept-Encoding');

// retrieve the user agent
Request::server('HTTP_USER_AGENT');

// check if the method is post (returns a boolean)
Request::isPost();

// check if the request is done using ajax (returns a boolean)
Request::isAjax();

// check if the request is done thru TLS/SSL (returns a boolean)
Request::isSecure();

// check if the request is from the command line (returns a boolean)
Request::isCli();
```

### [Database](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyquery)

Polyfony provides self-aware entities, in a similar way to an *ActiveRecord* (RubyOnRails) or an *Eloquent* object.

Examples bellow assume a table named `Pages` exists in the database.
The `Models\Pages` file has the following minimum amount of code.

```php 
namespace Models;
class Pages extends \Polyfony\Record {}
```

```php
// Retrieve a database entity by its ID, here, the id 67
$webpage = new Pages(67);

// Retrieve another database entity by its `url` column
$webpage = new Pages(['url'=>'/my-awesome-vegan-burger-recipe/']);

// Retrieve a single record by its ID and generate an input to change it's title property, with a custom css class
// note that any html in the title field will be escaped in the <input> to prevent XSS
// returns <input type="etextmail" name="Pages[title]" value="My awesome Vegan burger recipe is so yummy" />
(new Pages(67))
	->input('title', ['class'=>'form-control']);

// Create an new page, populate and save it
(new Pages)
	->set([
		'url'				=> '/veganaise-c-est-comme-de-la-mayonnaise-mais-vegan/',
		'title'				=> 'I\'m running out of ideas...',
		'description'		=> 'Meta descriptions get less and less important with Google\'s newest algorithms',
		'creation_date'		=> '18/04/1995', // this gets converted to a unixepoch automagically
		'modification_date'	=> time(), 
		'contents'			=> 'Meh...',
		'categories_array'	=> ['Cooking', 'Organic'], // this get's saved as json automagically
		'id_creator'		=> Security::getAccount()->get('id') // assuming you are logged in
	])
	->save();

// Alternatively, you can also create an entity this way
Pages::create([
	'url'		=>'...',
	'title'		=>'...',
	// more columns and values...
]);

// Retrieve the `title` and `id` of 5 pages that have the `Organic` category, 
// that have been modified in the last week, 
// and that have been created by user's id 7.
$pages = Pages::_select(['title','id'])
	->whereContains(['categories_array'=>'Organic'])
	->whereGreaterThan('modification_date', time() - 7*24*3600)
	->where(['id_creator'=>7])
	->limitTo(0,5)
	->execute();
```

#### Parameters

```php
->where()			// == $value
->whereNot()			// <> $value
->whereBetween()		// BETWEEN $min_value AND $max_value
->whereMatch()			// MATCH column AGAINST $value
->whereContains()		// % $value %
->whereEndsWith()		// % $value
->whereStartsWith() 		// $value %
->whereNotEmpty() 		// <> '' and NOT NULL
->whereEmpty() 			// '' or NULL
->whereNotNull() 		// NOT NULL
->whereNull() 			// NULL
->whereGreaterThan() 		// < $value
->whereLessThan() 		// > $value
```

#### Options

```php
->orderBy()				// associative array ('column'=>'ASC')
->limitTo()				// $start, $end
->groupBy()				// ?
->first()				// return the first record instead of an array of records
```

#### Magic columns

* Columns ending with `_date`, `_on`, `_at` will be converted from `DD/MM/YYYY` to a timestamp and vice-versa
* Columns ending with `_datetime` will be converted from `DD/MM/YYYY HH:mm` to a timestamp and vice-versa
* Columns ending with `_array` will be converted and stored as json, then restored to their original type
* Columns ending with `_size` will be converted from bytes to human readable size

|                    Setters                   |     Stored as     |           Getters          |          var_dump         |
|:--------------------------------------------:|:-----------------:|:--------------------------:|:-------------------------:|
| ->set(['creation_date'=>'01/01/2018'])       |     1514808000    | ->get('creation_date')     | string '01/01/2018'       |
| ->set(['creation_at'=>'01/01/2018'])         |     1514808000    | ->get('creation_at', true) | string '1514808000'       |
| ->set(['creation_on'=>'1514808000'])         |     1514808000    | ->get('creation_on')       | string '01/01/2018'       |
| ->set(['creation_datetime'=>'1514808000'])   |     1514808000    | ->get('creation_datetime') | string '01/01/2018 12:00' |
| ->set(['products_array'=>['apple','peach']]) | ["apple","peach"] | ->get('products_array')    | array ['apple','peach']   |
| ->set(['picture_size'=>'24938'])             |       24938       | ->get('picture_size')      | string '24.4 Ko'          |
| ->set(['picture_size'=>'24938'])             |       24938       | ->get('picture_size',true) | string '24938'            |

You add easily add elements to the end of an `_array` column. 
Assuming you have a `Process` **object**/table, which has a `events_array` **attribute**/column.

```php
// create a new Process object
(new Process)
	// push an event into the events_array object
	->push('events_array', [
		// this array is arbitrary, you are free to push anything into the column
		'date'			=>time(),
		'is_important'	=>false,
		'message'		=>'Something just happened !'
	])
	// your can also ommit the _array, the framework will find the right column
	->push('events', [
		// this array is arbitrary, you are free to push anything into the column
		'date'			=>time(),
		'is_important'	=>true,
		'message'		=>'Something dubious just occured !'
	])
	->save();

```


#### XSS Protection

Invoking ->get() on any other columns will automatically escape special html symbols using PHP's `FILTER_SANITIZE_FULL_SPECIAL_CHARS`  as to prevent XSS attacks. 
In situation where you actually want the raw data from the database, add `true` as a second parameter as such `$object->get('column_name', true);` to retrieve the data "as is". 
Calling Format::htmlSafe() anywhere in your code will provide you with the same escaping features. 


### Data validators

**Data validation should be managed by the developer with `symfony/validator`, `respect/validation`, `wixel/gump`, or similar packages.** 
That being said, there is a very basic *(and optional)* built-in validator, to prevent corrupted data from entering the database while manipulating objects.

To enforce it, declare a `VALIDATORS` constant array in your model, each key being a column, and each value being a regex, an array of allowed values or a standard PHP filter name (ex. `FILTER_VALIDATE_IP`).

* Example

```php

Models\Accounts extends Polyfony\Security\Accounts {
// Normal model classes extend Polyfony\Record. 
// Accounts extends an intermediate (but transparent) class that adds authentication logic.

	const ID_LEVEL = [
		0		=>'Admin',
		5		=>'Privileged User',
		10		=>'Simple User',
	];

	const IS_ENABLED = [
		0	=>'No',
		1	=>'Yes'
	];

	const VALIDATORS = [

		// using PHP's built in validators
		'login'					=>FILTER_VALIDATE_EMAIL, 
		'last_login_origin'		=>FILTER_VALIDATE_IP,
		'last_failure_origin'	=>FILTER_VALIDATE_IP,

		// using arrays
		'is_enabled'=>self::IS_ENABLED,
		'id_level'	=>self::ID_LEVEL

	];
}
```

The validation occurs when `->set()` is invoked and will throw exceptions. 

Note that you don't have to include `NULL` or `EMPTY` values in your validators to allow them. `NULL/NOT NULL` are to be configured in your database, so that the framework knows which column can, and cannot be null.

**Please be aware that doing a batch ->update (aka : not using distinct objects/Records) on a table will circumvent those validators. This will get fixed in a later version.**


### Data filtering 

Data filtering and sanitizing can be used *in addition* or *instead* of data validators.
While validators throw exception when invalid data is encountered, data filters will clean up the data, so that it matches the expected nature of said data. 

To enforce data filtering, declare a `FILTERS` constant array in your model, each key being a column, and each value being a filter name, or an array of filters names that will be applied one after the other. 

* Example 

```php

// an imaginary group model, that represent a group of people
Models\Groups extends Polyfony\Record {

	const FILTERS = [
		// replaces , with a dot and removes everything except 0-9 + - .
		'group_monthly_allowance'	=> 'numeric', 
		// trim spaces, removes any special chars and capitalize each words
		'group_name'				=> ['trim','text','ucwords'], 
		// removes any special chars and capitalize each words
		'group_manager_name'		=> ['text','strtoupper'], 
		// cleanup an email address
		'group_manager_email'		=> 'email' 
	];

}
```

The filtering occurs when `->set()` is invoked, and after the validations (if any). 

##### List of available filters

| Filter name        | What that filter does                                                |
|--------------------|----------------------------------------------------------------------|
| strtoupper         | applies mb_strtoupper()                                              |
| strtolower         | applies mb_strtolower()                                              |
| ucfirst            | applies ucfirst()                                                    |
| ucwords            | applies ucwords()                                                    |
| trim               | applies trim()                                                       |
| numeric            | replaces coma with dot then applies FILTER_SANITIZE_NUMBER_FLOAT     |
| integer            | applies FILTER_SANITIZE_NUMBER_INT                                   |
| phone              | removes anything but 0 to 9 the plus sign and parenthesis            |
| email              | applies FILTER_SANITIZE_EMAIL                                        |
| text               | replaces ' with ’ then removes < > & " \ /                           |
| slug               | applies Polyfony/Format::slug()                                      |
| length{4-4096}     | applies mb_substr()                                                  |
| capslock{30,50,70} | applies ucfirst(mb_strtolower()) if the uppercase ratio exceeds XX % |

The `capslock30`, `capslock50` and `capslock70` don't affect the data if it has a low enough uppercase ratio. 
This filter allows for nicer and cleaner databases, it has been designed for older people who enjoy the *FUCK YEAH CAPS LOCK !!* lifestyle. 
	
**An added benefit of using model's filters is that your inputs and textarea automatically get the right html attributes and types.** 

Check out the following Model, View and HTML output.

```php
class User extends Polyfony\Record {
	const FILTERS = [
		'user_login'=>['email','length128']
	];
}
```
```php
<?= (new Models\Users->input('user_login'); ?>
```
```html
<input name="Users[user_login]" type="email" maxlength="128" value="" />
```

Your input also gets a `required="required"` attribute if the column cannot be null. This is deduced from the database schema.

**Please be aware that doing a batch ->update (aka : not using distinct objects/Records) on a table will circumvent those validators. This will get fixed in a later version.**


### [Router](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyrouter)

**A route maps an URL to an `Action`, which resides in a `Controller`, which resides in a `Bundle`**  
An `Action` is a *method*, a `Controller` is a *class*, a `Bundle` is a *folder*. 
Routes are to be declared in each bundle's `Loader` directory, in a file called `Route.php`

*Example : `Private/Bundles/{BundleName}/Loader/Route.php`*


###### Routes can accept a number of parameters, and lack thereof 
* `Router::map('/admin/:what/:id/', 'Bundle/Controller@{what}')`.
* `Router::map('/url/', 'Bundle/Controller@action)`.

###### The action can 
* be a parameter of the url (as with the first example. The action would be the 2nd parameter `{what}`)
* be ommited. In that case an `indexAction` is called. If it doesn't exist, `defaultAction()` will be called, if it doesn't exist an exception is thrown.

Before calling the desired action a `preAction()` method will be called on the controller. *You can declare one, or ommit it.*  
after the desired action has been be called, a `postAction()` method will be called on the controller. *You can declare one, or ommit it.*


* The following route will match a GET request to /about-us/  
```php
Router::get('/about-us/', 'Pages/Static@aboutUs');
```
It will call `Private/Bundles/Pages/Controllers/Static.php->aboutUsAction();`


* The following route will match a request of any method (GET,POST...) to /admin/{edit,update,delete,create}/ and /admin/  
```php
Router::map('/admin/:section/:id/', 'Admin/Main@{section}')
	->where([
		'section'=>[
			// section must be one of the following four
			// though you can ommit this, a wrong section value will trigger a defaultAction() on the controller
			// which means an exception if you have not declared it.
			'in_array'=>['edit','update','delete','create']
		],
		'id'=>[
			// id has to be a numeric value
			'is_numeric', 		
			// id can't be 0
			'!in_array'=>[0] 	
		]
	]);
```
It will call `Private/Bundles/Admin/Controllers/Main.php->{section}Action();`

*Route can also be generated dynamically, over database iterations.*

```php
// assuming you have a Pages table containing, well, pages. 
// those would have an "url" column that define the absolute url of the page.
// you should replace the _select with a cachable method in the model though.
foreach(Pages::_select(['url'])->execute() as $page) {

	// map that url to a single "Pages" controller 
	// that resides in a "Site" bundle
	Router::get(
		$page->get('url'),
		'Site/Pages@view'
	);

}

```

then, in `Bundles/Site/Controllers/Pages.php` 

```php
class PagesController extends Controller {

	public function viewAction() {

		// retrieve your webpage from the database
		// using not it's id, but it's url column value !
		$page = new Pages(['url'=>Request::getUrl()])

		// maybe set the title and description using that database object
		Response\HTML::set(
			'metas'=>[
				'title'			=>$page->get('title'),
				'description'	=>$page->get('description')
			]
		);

		// put the contents in $this, so that your view can use it
		$this->contents = $page->get('contents');

		// and pass it to the view
		$this->view('Pages/FromTheDatabase');

	}

}

```



###### URL Parameters constraints
* "in_array" => [allowed values]
* "!in_array" => [disalowed values]
* "preg_match" => "regex-to-match"
* "!preg_match" => "regex-not-to-match"
* "is_numeric"
* "!is_numeric" 
*If multiple constraints are declared, they all have to match.*  

###### Redirects declaration

* The following will redirect from `/some-old-url/` to `/the-new-url/` using a 301 status code.
```php
Router::redirect('/some-old-url/', '/the-new-url/', [$status_code=301]);
```
*Those are static redirections, not rewrite rules. They cannot include dynamic parameters.*

### Environments

https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyconfig

Environments characterize a context of execution, with their own set of variables. 
**Two environments exist in Polyfony** 
* `Dev`, the development environment (this is where your coding occurs, most likely on your local developement server, or your own computer), 
* `Prod`, the production environment (also refered to as `Live`).

Variables that are common to both environments should be put in the main configuration file `Private/Config/Config.ini` 
The environment detection can be based on either : 
* the domain name 
* the port. 

You can chose the detection method in `Config.ini` 

```
[polyfony]
detection_method = "domain" ; or "port"
```

Depending on the detected environment, either 
* `Private/Config/Dev.ini` or 
* `Private/Config/Prod.ini` 
will overload/merge with the main `Config.ini`


###### Bellow is sample `Dev.ini` with its development domain
```
Private/Config/Dev.ini

[router]
domain = my-project.my-company.ext.devel
port = 80
```

###### And a sample `Prod.ini` with its production domain
*The framework falls back to production if neither domain or port are matched*

```
Private/Config/Prod.ini

[router]
domain = my-project.my-company.ext
port 80

[response]
minify = 1
compress = 1
cache = 1
pack_js = 1
pack_css = 1

```

*Default configurations files with ready-to-go settings are put in place by composer during installation*

###### To retrieve configurations values (from the merged configurations files)
```php
// retrieve the whole 'response' group
Config::get('response');

// retrieve only a key from that group
Config::get('response', 'minify');
```

Having distinct configuration files allows you to :
* set a bypass email to catch all emails sent in development environment
* enable compression, obfuscation/minifying and caching only in production
* show the profiler in development (and even, in the early production stage if needed)
* use different database configuration
* harden security parameters in production while allowing softer settings during local tests
* etc.

### [Security](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonysecurity)

###### To secure a page (require a user to be logged in)
```php
Security::enforce();
```

Failure to authenticate will throw an exception, and redirect to `Private/Config/Config.ini` -> `[router]` -> `login_route = ""`

###### If you want to require a specific module (that can be bypassed by a level optionally)
```php
Security::enforce('MOD_NAME', $bypass_level);
```

Failure to comply with those requirements will throw an exception, but won't redirect the user anywhere.

###### To check manually for credentials 
```php
Security::hasModule($module_name);
Security::hasLevel($level);
```

### [Profiler](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyprofiler)

Set markers arounds heavy code blocks to estimate the time and memory impact of that block.
```php
Profiler::setMarker('ClueA.subclue1');
Profiler::releaseMarker('ClueA.subclue1')
```

If the `Config::get('profiler', 'enable')` if set to `true (1)` and your `Response` is of type `html`, you will see a nice bar at the bottom of the page, with lots of useful informations.
That bar depends on bootstrap 4 CSS and JS. Be sure to add those to your assets to enjoy the bull benefits of the Profiler.
By default, some markers are placed in key places (around every `Database` queries, around Controller forwarding...).

If your `Response` is of type `json`, then the `Profiler` ìnformations will be merged with your `Response` as an array.

![Profiler Demo1](https://i.imgur.com/rQoVmD3.png)

![Profiler Demo2](https://i.imgur.com/z4ohjVx.png)

### [Locales](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonylocales)

Locales are stored in csv files (tab + double-quotes), stored in each bundle in the `Bundles/MyBundle/Locales/` folder.
The files are parsed the first time you ask for a locale. The language is automatically detected using the browser's language, you can set it manually.

###### Retrieve a locale in the current language (auto-detection)

```php
Locales::get($key)
```

###### Retrieve a locale in a different languague

```php
Locales::get($key, $language)
```

###### Set the language (it is memorized in a cookie for a month)

```php
Locales::setLanguague($language)
```

### [Exception](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyexception)

Exception are routed to a route named « exception » if any, otherwise exception are thrown normally.
The status code is 500 by default, you can specify any HTTP status code. The cache is disabled by such a status code.

```php
Throw new Exception($error_message, $http_status_code);
```


### [Response](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyresponse)

The response if preconfigured according to the Config.ini
You can alter the response type and parameters at runtime, ex.

###### To redirect
```php
Response::setRedirect($url [, $after_seconds=0])
```

###### to change the charset
```php
Response::setCharset('utf-8')
```

###### to output a file inline
```php
Response::setType('file')
Response::setContent($file_path)
Response::render()
```

###### to download a file
```php
Response::setType('file')
Response::setContent($file_path)
Response::download('Myfilename.ext'[, $force_download=false])
```

###### to change the status code (to 400 Bad Request for example)
*Doing that will prevent the response from being cached. Only 200 status can be cached.*
```php
Response::setStatus(400)
```

###### to output plaintext
```php
Response::setType('text')
```

###### to output json
```php
Response::setType('json')
Response::setContent(['example'])
Response::render()
```

###### to add css files and headers links
```php
Response\HTML::setLinks(['//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css'])
```

You can also specify optional attributes, such as `media`
```php
Response\HTML::setLinks([
	'//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css'=>[
		'media'=>'screen'
	]
])
```

Or even set totaly different types of links, such as a favicon
```php
Response\HTML::setLinks([
	'/Assets/Shared/Svg/Favicon.svg'=>[
		'rel'	=>'icon',
		'sizes'	=>'any',
		'type'	=>'image/svg+xml'
	]
])
```

###### to add js files (local or remote)
```php
Response\HTML::setScripts([
	'//code.jquery.com/jquery-3.3.1.slim.min.js'
	'Shared/myfile.js' // this will import directly from the Bundle/Shared/Assets/Js folder
]) 
```

###### to add a meta tag
```php
Response\HTML::setMetas(['google-site-verification'=>'google-is-watching-you'])
```


Anything that is common to **all** responses, you will find in `Response`.
**As `links`, `scripts`, and `metas` are specific to HTML `Response`s, those are set in the subnamespace  `Response\HTML`** 


#### To output a spreadsheet

You can either output 
* CSV
* XLS (Requires phpoffice/spreadsheet)
* XLSX (Requires phpoffice/spreadsheet) 

```php
Response::setType('xlsx'); // xls or csv
Response::setContent([
	['A','B','C'],
	[1,2,3],
	[4,5,6]
]);
// Response::setContent(Models\Accounts::_select()->execute());
Response::download('Accounts.xlsx');
```

Note that arrays of objects from the database will automatically be converted to arrays.

#### In the case of CSV files
You can pass (optional) options thru the global configuration.

```ini

[response]
csv_delimiter = ','
csv_encloser = '"'
```

#### In the case of XLSX files
You can pass an (optional) compatibility option thru the global configuration.

```ini

[phpoffice]
office_2003_compatibility = 1
```


###### To cache the result of a reponse (all output type will be cached except `file`)
*Note that cache has to be enabled in your ini configuration, posted `Request` are not cached, errors `Response` neither.*
```php
Response::enableOutputCache($hours);
```

A cache hit will always use less than 400 Ko of RAM and execute much faster, under a millisecond on any decent server

###### The `Response` provides some headers by default *Relative slowness of this example is due the the filesystem being NFS thru wifi*

```
< HTTP/1.1 200 OK
< X-Powered-By: PHP
< Server: None of your business
< Content-Language: fr
< Content-type: text/html; charset=utf-8
< Content-Length: 11
< X-Memory-Usage: 436.9 Ko
< X-Execution-Time: 13.5 ms
```

###### The example bellow shows the same Hello World `Response` as above, but from the cache

```
< HTTP/1.1 200 OK
< X-Powered-By: PHP
< Server: None of your business
< Content-type: text/html; charset=utf-8
< Content-Encoding: gzip
< Content-Length: 31
< X-Footprint: 13.5 ms 436.9 Ko
< X-Environment: Prod
< Date: Mon, 19 Feb 2018 19:54:19 +0100
< Expires: Mon, 19 Feb 2018 23:54:19 +0100
< X-Cache: hit
< X-Cache-Footprint: 1.2 ms 418.2 Ko

```

### [Store](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonystorestoreinterface)

###### The Store interface looks like this
```php
Store\Engine::has($variable);
Store\Engine::put($variable, $value [, $overwrite = false]);
Store\Engine::get($variable); 
Store\Engine::remove($variable);
```

###### You can choose from different storage engines
```
Store\Cookie
Store\Filesystem
Store\Session
Store\Database
Store\Memcache
Store\Request
```
The last one stores your key-value only for the time of the current request.
Some of those engines have more capabilities than others, but all implement the basic interface and can store both variables, arrays, or raw data.

### [Bundle configurations](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyconfig)

###### Storing some bundle specific data 

Configurations that are specific to a bundle should be placed in `Bundles/MyBundle/Loader/Config.php` (ex. static list choices, etc.) 
*Note that these configurations are merged with `Config.ini` + `Dev.ini`/`Prod.ini` so all your configs are available with one interface : `Config::{get()/set()}`*

```php
Config::set($group, $key, $value);
```

###### Retrieve values (whole bundle, or a subset)

```php
Config::get($group);
Config::get($group, $key);
```


### [EMails](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonymail)

Emails are very simple to use and built over PHPMailer. 
They extend `Record` object, so there are normal database entries that have a few more methods, and they can be sent !

```php
(new Models\Emails)
	->set([
		'charset'	=>'utf8', 			// (get its defaults from Config.ini)
		'format'	=>'text', 			// (get its defaults from Config.ini)
		'from_name'	=>'Me me me', 		// (get its defaults from Config.ini)
		'from_email'=>'me@myself.com', 	// (get its defaults from Config.ini)
		'subject'	=>'Hello !',
		'body'		=>'Hello again ?',
		'reply_to'	=>'foo@bar.com',		// fake column
		'to'		=>'email@domain.com', 	// fake column
		'bcc'		=>'email2@domain.com', 	// fake column
		'cc'		=>[ 					// fake column
			'email1'=>'name1',
			'email2'=>'name2'
		],
		'files_array'=>[
			'../Private/Storage/Data/something.pdf'=>'Something.pdf'
		]
	])
	->send([bool $save=false])				// instead of ->send() you can ->save(), to send it later
```

An email that fails to send, but has ->send(true) will end up in the Emails table. You can send it later.
It's `creation_date` column will be filled, but it will have an empty `sending_date` column, making it really easy to retry later.

```php
(new Emails(267)) // assuming its id is 267
	->send()
```

Even though `->save()` isn't explicitely called, nor is `->send(true)`, 
since the email has been retrieved from the database, upon sending, it's sending_date column will be updated and **it will be saved**. 


###### Using templates

```php
(new Models\Emails)
	->set([
		'template'	=>'../Private/Storage/Data/Templates/Demo-Email.html',
		'subject'	=>'Another passionnating subject',
		'to'		=>[
			'email@domain.com'	=>'Jack',
			'email2@domain2.com'=>'Jill'
		],
		'body'		=>[
			'something'			=>'that I want replaced by this text',
			'something-else'	=>'that I also want replaced'
		]
	])
	->send(true)
	->isSent() ? do_something() : do_something_else();
```

Templates use variables enclosed with double braquets `{{variable_name}}`, example 

```html
<body>
	<h1>
		{{something}}
	</h1>
	<p>
		{{something-else}}
	</p>
</body>
```

get's transformed into

```html
<body>
	<div>
		that I want replaced by this text
	</div>
	<p>
		that I also want replaced
	</p>
</body>
```

Beware of inserting non-validated user inputs into emails.


### [Element](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyelement)

###### Create an HTML tag (similar to mootools' Element)

```php
<?= Element('img',['src'=>'/img/demo.png'])->set('alt','test'); ?>
```
```html
<img src="/img/demo.png" alt="test" />
```

###### Create an HTML element with an opening and a closing tag 

```php
$quote = new Element('quote',['text'=>'Assurément, les affaires humaines ne méritent pas le grand sérieux']);
$quote->adopt($image);
```
```html
<quote>Assurément, les affaires humaines ne méritent pas le grand sérieux<img src="/img/demo.png" alt="test" /></quote>
```

Setting `value` will escape its html so will with setting `text`.

### [Form](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyform)

###### Form helper allow you to build and preset form elements, ex.

```php
<?= Form::input($name[, $value=null [, $options=[]]]); ?>
```

###### This will build a two element select (with the class `form-control`), and preset Peach.

```php
<?= Form::select('sample', [] 0 => 'Apple', 1 => 'Peach' ], 1, ['class'=>'form-control']); ?>
```

###### This will build a select element with optgroups.
*Note that optgroup are replaced by a matching locale (if any), and values are also replaced by matching locale (if any).*

```php
<?= Form::select('sample', [
	'food'=>[
		0 => 'Cheese',
		1 => 'Houmus',
		2 => 'Mango'
	],
	'not_food'=>[
		3 => 'Dog',
		4 => 'Cow',
		5 => 'Lizard'
	]
], 3); ?>
```
```html
<select name="sample">
	<optgroup label="food">
		<option value="0">Cheese</option>
		<option value="1">Houmus</option>
		<option value="2">Mango</option>
	</optgroup>
	<optgroup label="not_food">
		<option value="3" selected="selected">Dog</option>
		<option value="4">Cow</option>
		<option value="5">Lizard</option>
	</optgroup>
</select>
```


Shortcuts are available from objects that extends the `Record` class (ex: your Models).

###### retrieve an account from its id
```php
<?= (new Accounts(1))
	->set('login', 'mylogin@example.com')
	->input('login', ['data-validators'=>'required']); 
?>
```
```html
<input type="text" name="Accounts[login]" value="mylogin@example.com" data-validators="required"/>
```

List of available elements :
* input
* select
* textarea
* checkbox

Form elements general syntax is : `$name, $value, $options` when you get a form element from a `Record`, the `$name` and `$value` are set automatically, only `$options` are available. The select elements is slighly different : `$name, $list, $value, $options`

To obtain, say, a password field, simply add this to your array of attributes : 'type'=>'password'

## [CRSF Protection](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyformtoken)

A CRSF Protection and double-submit guard is available.

###### In the middle of your html form (in a View)

```html
<form action="" method="post">
<!-- more form here -->

<?= new Polyfony\Form\Token(); ?>

<!-- more form here -->
</form>
```

###### In your controller

```php
Polyfony\Form\Token::enforce();
```

**That's it.** 

Instanciating a "Token" objet generates a unique token, stores it in the PHP SESSION and builds an html input element.  
The static enforce method, checks if a request has been POSTed, and if so, if a token exists, and matches one stored in the session. Otherwise, throws an exception and redirects to the previous page.


## [Captcha Protection](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyformcaptcha)

A Captcha provider is available, it's a wrapper of gregwar/captcha.

###### In the middle of your html form (in a View)

Show the captcha image itself
```php
<?= new Polyfony\Form\Captcha(
	5, // number of characters in the captcha (optional)
	150, // width of the captcha, in px (optional)
	40 // height of the captcha, in px (optional)
); ?>
```

Show an input to type the captcha in
```php
<?= Polyfony\Form\Captcha::input([
	// html attributes (optional)
	'class'=>'form-control',
	'placeholder'=>'Type the captcha here'
]); ?>
```

###### In your controller

```php
Polyfony\Form\Captcha::enforce();
```

**That's it.** 

Instanciating a "Captcha" objet generates a phrase, stores it in the PHP SESSION and builds a captcha image using gregwar/captcha builder.  
The static enforce method, checks if a request has been POSTed, and if so, if a captcha value exists, and matches one stored in the session. Otherwise, throws an exception and redirects to the previous page.
You can manually try/catch exception to avoid loosing what the user typed, in that case, use `Captcha::enforce(true)` to prevent automatic redirections. 

## Database structure

The framework has been extensively tested using SQLite, it *may* work with other engines, it defitively works without. 
Without, you'd just loose `Security`, the `Emails` storage feature, the `Store\Database` engine and the `Logger`'s database feature.

The database's structure is available by dumping the SQLite Database `Private/Storage/Database/Polyfony.db`.
The PDO driver can be changed to `MySQL`, `Postgres` or `ODBC` in `Private/Config/Config.ini`. **There is no `Query` object support for Postgres and ODBC.**


## Logging 

The framework exposes a **Logger class**, with the following **static** methods
* `debug(string $message, ?mixed $context) :void` (level 0)
* `info(string $message, ?mixed $context) :void` (level 1)
* `notice(string $message, ?mixed $context) :void` (level 2)
* `warning(string $message, ?mixed $context) :void` (level 3)
* `critial(string $message, ?mixed $context) :void` (level 4)

The logs can be sent to a file, or to your database (see `Config.ini [logger][type]`). 
The minimum level to log is configurable (see `Config.ini [logger][level]`) by default, `Dev` environement is configured to log from the `0` level, `Prod` environment is configured to log from the `1` level.
Critital level logs (`4`) can also be sent automatically via email (see `Config.ini [logger][mail]`. 

**Logged message/objects/array are also automatically made available in the Profiler** 


Example
```php
Logger::notice('Something not too worrying just happened');
Logger::debug('Someone did something', $some_kind_of_object);
Logger::critical('Failed to contact remote API', $api_handler);
```

## Updating the framework

#### To updade **the framework**, run this command from your project directory (beware of backward incompatible changes)
The first and last command allow you to preserve and restore your composer.json after the udpate

```bash
git stash
git pull
git stash apply
```

#### To updade **the dependencies**, run this command from your project directory

```bash
composer update
```

## Deprecated and discontinued features 

| **Previous Feature**   | **Status**   | **Replacement**         | **How to get it**                     |
|------------------------|--------------|-------------------------|---------------------------------------|
| Polyfony\HttpRequest() | DEPRECATED   | Curl\Curl()             | require php-curl-class/php-curl-class |
| Polyfony\Filesystem()  | DEPRECATED   | Filesystem\Filesystem() | require symfony/filesystem            |
| Polyfony\Uploader()    | DEPRECATED   | FileUpload\FileUpload() | require gargron/fileupload            |
| Polyfony\Validate()    | DISCONTINUED | Validator\Validation()  | require symfony/validator             |
| Polyfony\Thumnbail()   | DISCONTINUED | Intervention\Image()    | require intervention/image            |
| Polyfony\Notice()      | DISCONTINUED | Bootstrap\Alert()       | require polyfony-inc/bootstrap        |


## Release history

| Version   | Major change                                                                                                                                                             |
|-----------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| 2.0-alpha | Major rewrite from 1.x new folder structure, routes syntax, new helpers, new configuration files, MVC architecture, database entries are instanciated as Record objects. |
| 2.0       | Better ORM, Database entries now are instanciated as Models/{TableName} that inherit the Record class                                                                    |
| 2.1       | PHP 7.2 support, composer support, new debugging tools are introduced (Profiler), deprecation of old helpers                                                             |
| 2.2       | Old routes syntax have been dropped, redirections are now supported directly in routes declaration                                                                       |
| 2.3       | XSS escaping as default for all Record->get(), Filters are now supported, Validators are enhanced                                                                        |
| 2.4       | Query->first() used to return false when no result were found, it now returns null.                                                                                      |
| 2.5       | Query->get() shortcut for ->first()->execute(), enhanced Profiler, Console shortcut                                                                                      |
| 2.6       | Emails refactoring, Tests support via PHPUnit support, Events support                                                                                                    |

## [Performance](https://github.com/polyfony-inc/polyfony/wiki/Benchmark)
Polyfony has been designed to be fast, no compromise (> 2000 req/s). 
If implementating a « convenience » tool/function into the framework was to cost a global bump in execution time, it is either implemented in a more efficient manner, or not implemented at all.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, CodeClimate, RIPS, and Sonar.

## Coding Standard
Polyfony follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
