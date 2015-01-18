[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony 2 is a simple and powerful PHP micro-framework.

Compared to major PHP frameworks, Polyfony covers 95% of what we need most of the time, whilst using 5% of ressources, space, configuration files and dependencies required by major frameworks.

#### Features
routing, bundles, controllers, views, database abstraction, environments, locales, cache, vendor, helpers, authentication, profiler…

#### Footprint (of an Hello World)
* 400 Ko of disk space (35% of comment lines)
* 750 Ko of RAM (or 350 Ko from the cache)


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
The code bellow assumes that your are using the `Polyfony` namespace in your controller.

### Request
* retrieve an url parameter
```php
Request::get('format');
```

* retrieve a posted field named `search`
```php
Request::post('search');
```

* retrieve a file
```php
Request::files('attachment_document');
```

* retrieve a request header
```php
Request::header('Accept-Encoding');
```

* check if the method is post
```php
Request::isPost();
```

* check if the request is done using ajax
```php
Request::isAjax();
```


### Database

* Retrieve the login and id of 5 accounts with level 1 that logged in, in the last 24h
```php
// demo query
$accounts = Database::query()
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
$account = new Record('Accounts',1);
```

* Retrieve a single record by its ID and generate an input to change a property
```php
$account = new Record('Accounts',1);
echo $account->input('login');
```
```html
<input type="text" name="Accounts[login]" value="root" />
```

* Create a record, populate and insert it
```php
$account = new Record('Accounts');
$account
	->set('login', 'test')
	->set('id_level', '1')
	->set('last_login_date', '18/04/1995')
	->set('modules_array', array('MOD_BOOKS', 'MOD_USERS', 'MOD_EXAMPLE'))
	->set('password', Security::getPassword('test'))
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
Router::addRoute('about-us')
	->url('/about-us/')
	->destination('Pages','Static','aboutUs');
```

* This dynamic route will match /admin/{edit,update,delete,create}/ and /admin/
It will call `../Private/Bundles/Admin/Controllers/Main.php->{edit,update,delete,create,index}Action();`
```php
Router::addRoute('admin')
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

### Environments

By default the `../Private/Config/Config.ini` file is loaded.

If you access the framework using the port defined in 
```
[polyfony]
dev_port = 8080
```
Then the framework will overrides Config.ini values with those of :
```
../Private/Config/Dev.ini
```
Or, if you are not running the development port :
```
`../Private/Config/Prod.ini
```

* To retrieve configurations values (from the environment specific ini file)
```php
Config::get('group', 'key')
```

Having specific ini configuration files for development and production allows your to :
* set an bypass email to redirect all email sent in development environment
* enable compression, obfuscation and cache only in production
* enable code indentation in development
* show the profiler stack in development
* use different database configuration in development or production
* etc.

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

### Profiler

Set markers arounds heavy code blocks to estimate the time and memory impact of that bloc.
```php
Profiler::setMarker('begining_of_a_heavy_operation');
Profiler::setMarker('end_of_heavy_opeartion')
```

If the `Config::get('profiler', 'enable_stack')` if set to true,
the stack of markers will be added at the bottom an html `Response` as a nice ul/li lists, or merged into a json `Response`


### Locales

Locales are stored in csv files (tab + double-quotes), stored in each bundle in the `Bundles/MyBundle/Locales/` folder.
The files are parsed the first time you ask for a locale. The language is automatically detected using the browser's language, you can set it manually.

* Retrieve a locale in the current language (auto-detection)

```php
Locales::get($key)
```

* Retrieve a locale in a different languague

```php
Locales::get($key, $language)
```

* Set the language (it is memorized in a cookie for a month)

```php
Locales::setLanguague($language)
```

### Exception

Exception are routed to a route named « exception » if any, otherwise exception are thrown normally.
The status code is 500 by default, you can specify any HTTP status code. The cache is disabled by such a status code.

```php
Throw new Exception($error_message, $http_status_code);
```

### Notice

You can use from different types of notice elements to suit your needs
```php
Notice($message, $title=null)
Notice\Danger($message, $title=null)
Notice\Success($message, $title=null)
Notice\Warning($message, $title=null)
```
All will be converted to string elegantly in HTML or text depending on the context (CLI, Ajax…) and it uses bootstrap-friendly classes.

Manually getting back notice text
```php
$notice->getMessage($html_safe=true)
$notice->getTitle($html_safe=true)
```

Typical example

```php
// set a notice depending on the presence of errors
$this->notice = $has_error ? 
	new Pf\Notice\Danger('Cache directory has not been emptied', 'Error!') :
	new Pf\Notice\Success('Cache directory has been emptied', 'Success!');
```

### Response

The response if preconfigured according to the Config.ini
You can alter the response type and parameters at runtime, ex.

* To redirect
```php
Response::setRedirect($url [, $after_seconds=0])
```

* to change the charset
```php
Response::setCharset('utf-8')
```

* to output a file inline
```php
Response::setType('file')
Response::setContent($file_path)
Response::render()
```

* to download a file
```php
Response::setType('file')
Response::setContent($file_path)
Response::download('Myfilename.ext'[, $force_download=false])
```

* to change the status code (to 400 Bad Request for example)
Doing that will prevent the response from being cached. Only 200 status can be cached.
```php
Response::setStatus(400)
```

* to output plaintext
```php
Response::setType('text')
```

* to output json
```php
Response::setType('json')
Response::setContent(array('example'))
Response::render()
```

* to add css files
```php
Response::setAssets('css','//maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css')
```

* to add js files
```php
Response::setAssets('js','/Assets/js/myfile.js')
```

* to add a meta tag
```php
Response::setMetas('google-site-verification', 'google-is-watching-you')
```

* To cache the result of a reponse (all output type will be cached except `file`)
Note that cache has to be enabled in your ini configuration, posted `Request` are not cached, errors `Response` neither.
```php
Response::enableOutputCache($hours);
```

A cache hit will always use less than 400 Ko of RAM and execute much faster, under a millisecond on any decent server

* The `Response` provides some headers by default

```
< HTTP/1.1 200 OK
< X-Powered-By: Polyfony
< Server: Undisclosed
< Content-Language: fr
< Content-type: text/html; charset=utf-8
< Content-Encoding: gzip
< Content-Length: 667
< X-Memory-Usage: 832 Ko
< X-Execution-Time: 21 ms
```

The example bellow shows the same Hello World `Response` as above, but from the cache

```
< HTTP/1.1 200 OK
< X-Powered-By: Polyfony
< Server: Undisclosed
< Content-Language: fr
< Content-type: text/html; charset=utf-8
< Content-Encoding: gzip
< Content-Length: 667
< X-Memory-Usage: 389 Ko
< X-Execution-Time: 1 ms
< X-From-Cache: hit
< X-Cached-On: Sat, 17 Jan 2015 00:51:38 +0100
< X-Cached-Until: Sun, 18 Jan 2015 00:51:38 +0100
```

### Store

The Store interface looks like this :
```php
Store\Engine::has($variable);
Store\Engine::put($variable, $value [, $overwrite = false]);
Store\Engine::get($variable); 
Store\Engine::remove($variable);
```

You can choose from different storage engines
```
Store\Cookie
Store\Filesystem
Store\Session
Store\Database
Store\Apc
Store\Memcache
Store\Request
```
The last on stores your key-value only for the time of the current request.
Some of those engines have more capabilities than others, but all implement the basic interface and can store both variables, arrays, or raw data.

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

`Thumbnails` are generated in JPEG format. The source image can be PNG or JPEG.

```php
$thumbnail = new Thumbnail();
$status = $thumbnail
	->source("../private/data/storage/photos/original/photo.jpg")
	->destination("../private/data/storage/photos/400/photo.jpg")
	->size(400)
	->quality(90)
	->execute();
```
```php
boolean $status
```

### Uploader

`Uploader` will generate a unique name for your file if you only provided a destination folder.
You can get the generated name using the `name()` method, `info()` will also give you lots of informations about your uploaded file, including its size, mimetype, etc.

```php
$uploader = new Uploader();
$status = $uploader
	->source(Request::files('estimate_file'))
	->destination('../private/data/storage/estimates/')
	->limitTypes(array('application/pdf'))
	->limitSize(1024*1024*2)
	->execute();
```
```php
 boolean $status
 array $uploader->info()
 string $uploader->error()
 string $uploader->name()
 ```

### HttpRequest

This class provides a simple interface to build HTTP Requests

```php
$request = new HttpRequest();
$status = $this->Request
	->url('https://maps.googleapis.com/maps/api/geocode/json')
	->timeout(15)
	->retry(2)
	->data('address','Paris')
	->get();
```

This example will tro access `https://maps.googleapis.com/maps/api/geocode/json?address=Paris`, 
waiting 15 seconds for a response, retrying 2 times if doesn't get a 200 status code.
The default waiting is 60 seconds, and retry is 3 times.

```php
boolean $status
string $request->getHeader('Content-Type')
mixed $request->getBody()

```
Responses of type application/json will be decoded to array, response of type application/xml will be decoded to SimpleXML object.

* Attaching a file 
```php
$request->file($field_name, $path);
```

* Sending a cookie
```php
$request->cookie($key, $value)
```


### Mail

* Mail are very simple to use and build over PHPMailer

```php
$mail = new Mail();
$status = $mail
	->to($email [, $name=null])
	->cc($email [, $name=null])
	->bcc($email [, $name=null])
	->format($format[html,text])
	->file($path)
	->from($email, $name)
	->subject($subject)
	->body($body)
	->send($save=true)
```

```php
boolean $status
string $mail->error()
```

* Mail with a template using smtp

```php
$mail = new Mail();
$status = $this->Mail
	->smtp($host, $user, $pass)
	->to('text@email.com', 'Name')
	->format('text')
	->subject($subject)
	->template($path)
	->set($key1, $value2)
	->set($key1, $value2)
	->send($save=true)
```

The template uses variables named `__{$variable}__` ex:

```html
<body>
	<div>__message__</div>
</body>
```

```php
$mail->set('message','My example')
```

```html
<body>
	<div>My example</div>
</body>
```

If the mail format is html, your value will be escaped automatically

### Element

* Create an HTML tag (similar to mootools' Element)

```php
$image = new Element('img',array('src'=>'/img/demo.png'))->set('alt','test');
echo $image;
```
```html
<img src="/img/demo.png" alt="test" />
```

* Create an HTML element with an opening a closing tag
`text` will escape html 

```php
$quote = new Element('quote',array('text'=>'Assurément, les affaires humaines ne méritent pas le grand sérieux'));
$quote->adopt($image);
```
```html
<quote>Assurément, les affaires humaines ne méritent pas le grand sérieux<img src="/img/demo.png" alt="test" /></quote>
```

Setting `value` will escape its html so will with setting `text`.

### Form

Form helper allow you to build and preset form elements, ex.

```php
echo Form::input($name[, $value=null [, $options=array()]]);
```

This will build a two element select (with the class `form-control`), and preset Peach.

```php
echo Form::select('sample', array( 0 => 'Apple', 1 => 'Peach' ), 1, array('class'=>'form-control'));
```

This will build a select element with optgroups.
Note that optgroup are replaced by a matching locale (if any), and values are also replaced by matching locale (if any).

```php
echo Form::select('sample', array(
	'food'=>array(
		0 => 'Cheese',
		1 => 'Houmus',
		2 => 'Mango'
	),
	'not_food'=>array(
		3 => 'Dog',
		4 => 'Cow',
		5 => 'Lizard'
	)
))
```

Shortcuts are available from `Record` object, ex.

* retrieve an account from its id
```php
$record = new Record('Accounts',1);
$record->set('login', 'mylogin@example.com')

echo $record->input('login', array('data-validators'=>'required'));
```
```html
<input type="text" name="Accounts[login]" value="mylogin@example.com" data-validators="required"/>
```

List of available elements :
* input
* hidden
* password
* submit
* select
* textarea
* radio
* checkbox

Form elements general syntax is : `$name, $value, $options` when you get a form element from a `Record`, the `$name` and `$value` are set automatically, only `$options` are available. The select elements is slighly different : `$name, $list, $value, $options`

### Filesystem

The filesystem class allows you to manipulate directories and files easily.
Most methods have a `chroot` option that will force all operations to appen in the `../Private/Storage/Data/` directory. 
You can define a different storage directory in your `Config.ini` under `[polyfony]` and `data_path`.

```php
Filesystem::mkdir('/my-directory/', '0777', true);
Filesystem::mkdir('../../../../my-directory/', '0777', true);
```
* Both commands will create `../Private/Storage/Data/my-directory/`

If you want to operate outside of the Data storage folder, ommit the chroot parameter or set it to false. 

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
$photo_url = $photo
//	->position($lat,$lnt)
	->address('Some normal address')
	->size(500,500)
	->url();
```

#### Google\Map

* Retrieve a static map with a marker
```php
$map = new \Google\Map();
$map_url = $map
	->center($lat,$lng);
	->zoom(7)
	->retina(true)
	->marker($lat,$lng)
	->size(600,600)
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
