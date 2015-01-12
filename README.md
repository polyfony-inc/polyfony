[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony 2 is a simple and powerful PHP micro-framework.

Compared to major PHP frameworks, Polyfony follows the 80-20 Pareto principle :
It provides 95% of what we need most of the time, whilst using 5% of ressources, space, configuration files and dependencies required by major frameworks.

Features : routing, bundles, controllers, views, database abstraction, environments, locales, cache, vendor, helpers, authentication, profiler…
Footprint : 400 Ko of disk space, 650 Ko or RAM.


## Requirements
* PHP >= 5.3 with mbstring and PDO
* A rewrite module (mod_rewrite)

## Installation
* Point your domain to `/Public/`
* Under Apache, `/Public/.htaccess` already rewrites everything
* Under lighttpd, set this rewrite rule
```php
url.rewrite-once = ("^(?!/Assets/).*" => "/?")
```

## Quick tour
You can read this quick tour, or just browse the `../Private/Bundles/Demo/` code.


### Request
* Retrieve an url parameter
```php
// get a parameter named format from the url
pf\Request::get('format');
```

* Retrieve a posted field named `search`
```php
pf\Request::post('search');
```

* Retrieve a file
```php
pf\Request::files('attachment_document');
```

* retrieve a request header
```php
pf\Request::header('Accept-Encoding');
```

* check if the method is post
```php
pf\Request::isPost();
```

* check if the request is done using ajax
```php
pf\Request::isAjax();
```


### Database

* Retrieve the login and id of 5 accounts with level 1 that logged in, in the last 24h
```php
// demo query
$this->Accounts = pf\Database::query()
	->select(array('login','id'))
	->from('Accounts')
	->where(array(
		'id_level'=>1
	))
	->whereHigherThan('last_login_date',time()+24*3600)
	->limitTo(0,5)
	->execute();
```

* Retrieve a single record by its ID
```php
$root_account = new pf\Record('Accounts',1);
echo $root_account;
```

* Retrieve a single record by its ID and generate an input to change a property
```php
$root_account = new pf\Record('Accounts',1);
echo $root_account->input('login');
// <input type="text" name="Accounts[login]" value="root" />
```

* Create a record, populate and insert it
```php
$account = new pf\Record('Accounts');
$account
	->set('login','test')
	->set('id_level','1')
	->set('last_login_date','18/04/1995')
	->set('modules_array',array('MOD_BOOKS','MOD_USERS','MOD_EXAMPLE'))
	->set('password',pf\Security::getPassword('test'))
	->save();
```

### Router

* Each bundle has a file to place your routes
```php
../Private/Bundles/{BundleName}/Loader/route.php
```

In these files you can declare as many routes as you like. 
Static routes (not accepting parameters, require a perfect URL match) or dynamic routes, accepting parameters, that you can optionally restrict.
All routes must point to a Bundle and Controller, specificied by the `->destination($bundle,$controller[,$action])` method.

In the case of static route, you must provide the action in the route.
Dynamic routes can point to different actions depending on a URL parameter, specified by the `->trigger($url_parameter)` method.
If no action is provided, indexAction is called, if an action is provided but none match, `defaultAction()` is called.
A `preAction()` and `postAction()` wrap the action to be called.

* This static route will match /about-us/ and call `../Private/Bundles/Pages/Controllers/Static.php->aboutUsAction();`

```php
Polyfony\Router::addRoute('about-us')
	->url('/about-us/')
	->destination('Pages','Static','aboutUs');
```

* This dynamic route will match /admin/{edit,update,delete,create}/ and /admin/
It will call `../Private/Bundles/Admin/Controllers/Main.php->{edit,update,delete,create,index}Action();`
```php
Polyfony\Router::addRoute('admin')
	->url('/admin/:action/:id/')
	->destination('Admin','Main')
	->restrict(array(
		'action'=>array('edit','update','delete','create')
	))
	->trigger('action');
```

You can restrict parameters further, you can pass :
an array of allowed value (it will also match no value)
a regex (it will also match no value)
a boolean true (it will match anything but a missing value)

### Form

### Security

* To secure a page (require a user to be logged in)
```php
Security::enforce();
```

* If you want to require a specific module (that can be bypassed by a level optionally)
```php
Security::enforce('MOD_NAME', $bypass_level);
```

* To check manually for credentials 
```php
Security::hasModule($module_name);
Security::hasLevel($level);
```

### Locales

Locales are stored in csv files (tab + double-quotes), stored in each bundle in the `Bundles/MyBundle/Locales/` folder.
The files are parsed the first time to ask for a locale. The language is automatically detected using the browser's language, you can set it manually.

* Retrieve a locale in the current language (auto-detection)

```php
Locale::get($key);
```

* Retrieve a local in a different languague

```php
Locale::get($key, $language);
```

* Set the language (it is memorized in a cookie)

```php
Locales::setLanguague($language);
```

### Exception

Polyfony\Exception are routed to a route named « exception » if any, otherwise exception are thrown normally.
The status code is 500 by default, you can specify any HTTP status code.

```php
Throw new Polyfony\Exception($error_message, $http_status_code);
```

### Notice

You can choose from different types of notice
```php
Notice($message,$title=null)
// default information notice
Notice\Danger($message,$title=null)
// danger notice
Notice\Success($message,$title=null)
// success notice
Notice\Warning($message,$title=null)
// warning notice
```
All will be converted to string elegantly in HTML or text depending on the context (CLI, Ajax…) and it uses bootstrap-friendly classes.

Manually getting back notice text
```php
$notice->getMessage($html_safe=true)
$notice->getTitle($html_safe=true)
```

### Response

### Store

The Store interface looks like this :
```php
Store\Engine::has($variable);
Store\Engine::put($variable, $value, $overwrite);
Store\Engine::get($variable); 
Store\Engine::remove($variable);
```

You can choose from different storage engine
```
Store\Cookie
// uses a cookie to compress and store the key
Store\Filesystem
// uses a file to store the key
Store\Session
// uses a PHP session to store the key
Store\Database
// uses a database table to store the key
Store\Request
// uses a variable to store the key for the time of a query only
Store\Apc
// uses apc engine to store the key
Store\Memcache
// uses memcache to store the key
```
Some of them have little specificities, but all implement the basic interface.

### Runtime

* Store some bundle specific data in Bundles/MyBundle/Loader/Runtime.php (ex. static list choices, etc.)

```php
Runtime::set($bundle_name, $key, $value);
```

* Retrieve values (whole bundle, or a subset)

```php
Runtime::get($bundle_name);
Runtime::get($bundle_name, $key);
```

### Thumbnail

```php
$this->Thumbnail = new Polyfony\Thumbnail();
$this->Status = $this->Thumbnail
	->source("../private/data/storage/photos/original/{$id}")
	->destination("../private/data/storage/photos/400/{$id}")
	->size(400)
	->quality(90)
	->execute();
```
```php
boolean $this->Status
```

### Uploader

```php
$this->Uploader = new Polyfony\Uploader();
$this->Status = $this->Uploader
	->source(Request::files('estimate_file'))
	->destination('../private/data/storage/estimates/')
	->limitTypes(array('application/pdf'))
	->limitSize(1024*1024*2)
	->execute();
```
```php
 boolean $this->Status
 array $this->Uploader->info()
 string $this->Uploader->error()
 ```

### HttpRequest

This class provides a simple interface to build HTTP Requests

```php
$this->Request = new Polyfony\HttpRequest();
$this->Success = $this->Request
	->url('https://maps.googleapis.com/maps/api/geocode/json')
	->data('address','Paris')
	->get();
```
```php
boolean $this->Success)
string $this->Request->getHeader('Content-Type')
mixed $this->Request->getBody()

```
Responses of type application/json will be decoded to array, response of type application/xml will be decoded to SimpleXML object.

### Element

* Create an HTML tag (similar to mootools' Element)

```php
echo new Polyfony\Element(
	'img',
	array(
		'src'=>'/img/demo.png',
		'alt'=>'test'
	));

// <img src="/img/demo.png" alt="test" />

echo new Polyfony\Element(
	'quote',
	array(
		'text'=>'Assurément, les affaires humaines ne méritent pas le grand sérieux',
	));
	
// <quote>Assurément, les affaires humaines ne méritent pas le grand sérieux</quote>
```

#### Google\Position

* Geocoding

```php
\Google\Position::address('Paris')
```

* Reverse geocoding

```php
\Google\Position::reverse(48.856614,2.3522219)
```

#### Google\Photo

* Retrieve a photo from streetview
```php
$photo = new \Google\Photo();
$image_url = $photo
//	->position($lat,$lnt)
	->address('Some normal address')
	->size(500,500)
	->url();
```

#### Google\Map

* Retrieve a static map with a marker
```php
$map = new \Google\Map();
$image_url = $map
	->center($lat,$lng);
	->zoom(7)
	->retina()
	->marker($lat,$lng)
	->size(600x600)
	->url();
```

#### Google\QRCode

* Generate a QRCode url

```php
Google\QRCode::url($data, $size)
```

## Performance
Polyfony has been designed to be fast, no compromise.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
