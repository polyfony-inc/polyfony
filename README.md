[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony 2 is a simple and powerful PHP micro-framework.

Compared to major PHP frameworks, Polyfony covers 95% of what we need most of the time, whilst using 5% of ressources, space, configuration files and dependencies required by major frameworks.
Our approach is to allow you to know how everything works by keeping the codebase extremely small. Instead of refering to the documentation, you can look at the source code almost as easily.
Polyfony is fast by design (≤ 20ms/hit on your average app & server), and can get even faster (1~5ms) using the different integrated caching options.

#### Features
routing, bundles, controllers, views, database abstraction, environments, locales, cache, vendor, helpers, authentication, profiler…

#### Philosophy
Inspired by Symfony and Laravel but tailored to favour an inclination towards extreme simplicity and efficiency

#### Footprint (of an Hello World)
* ≤ 400 Ko of disk space (35% of comment lines)
* ≤ 550 Ko of RAM

## Requirements
* **See composer.json for the requirement**
Current requirements are PHP >= 7.1, ext-pdo, ext-sqlite, ext-mbstring and a rewrite module (mod_rewrite)

## Installation

* Run this command to download the framework *(and its dependencies)* to your project directory

```
composer create-project sib-retail/polyfony --stability=dev your-project-folder
```

* With lighttpd, set the webroot of your webserver to `Public/` *(or requivalent config for Apache/NginX)*
```
server.document-root = "/var/www/your-project-folder/Public/"
```

* With lighttpd, set this rewrite rule *(or equivalent rule for Apache/NginX)*

```
url.rewrite-once = (
    "^(?!/Assets/).*" => "/?"
)
```


## Updating the framework

* To updade the framework, run this command from your project directory

```bash
composer update
```

This will update the framework (and its dependencies) to the newest compatible version without damaging your code or data.

## Quick tour
You can read this quick tour, or just browse the `Private/Bundles/Demo/` code.
The code bellow assumes that your are using the `Polyfony` namespace before each call.

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

* check if the request is done using SSL
```php
Request::isSecure();
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
$account = new Models\Accounts(1);
```

* Retrieve a single record by its ID and generate an input to change a property
```php
$account = new Models\Accounts(1);
echo $account->input('login');
```
```html
<input type="text" name="Accounts[login]" value="root" />
```

* Create a record, populate and insert it
```php
$account = new Accounts();
$account
	->set('login', 'test')
	->set('id_level', '1')
	// magic column name ending with _date will be translated to a timestamp automatically
	->set('last_login_date', '18/04/1995')
	// magic column name ending with _array will be translated to a JSON string automatically
	->set('modules_array', array('MOD_BOOKS', 'MOD_USERS', 'MOD_EXAMPLE'))
	->set('password', Security::getPassword('test'))
	->save();
```

* List of search parameters

```php
->where()				// == $value
->whereNot()			// <> $value
->whereBetween()		// BETWEEN $min_value AND $max_value
->whereMatch()			// MATCH column AGAINST $value
->whereContains()		// % $value %
->whereEndWith()		// % $value
->whereStartsWith() 	// $value %
->whereNotEmpty() 		// <> '' and NOT NULL
->whereEmpty() 			// '' or NULL
->whereNotNull() 		// NOT NULL
->whereNull() 			// NULL
```

* List of options

```php
->orderBy()				// associative array ('column'=>'ASC')
->limitTo()				// $start, $end
->groupBy()				// ?
->first()				// return the first record instead of an array of records
```

* Validating data

You can prevent corrupted data from entering the database. To do so, declare a validator for each column that you want to secure.
The declaration has to me done in the model, with the constant `VALIDATORS` being an array. 
The key being the column, the value being an array of authorized values, or a REGEX. Example :

```php

Models\Accounts extends pf\Records {
	
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
		'login'		=> '/^\S+@\S+\.\S+$/', // validate an email as login
		'id_level'	=> self::ID_LEVEL // validate any key from the const id_level
		'is_enabled'=> self::IS_ENABLED // validate 0 or 1
	];

}
```

The validation occurs when `->set()` is invoked and will throw exceptions.
Note that you don't have to include `NULL` or `EMPTY` values in your validators to allow them. 
Instead, allow NULL values for that column in the Table configuration of your database engine, 
the framework will import that information (and cache it, so empty the cache if you change `NULL`/`NOT NULL` of certain fields in the database).



### Router

* Each bundle has a file to place your routes
```php
Private/Bundles/{BundleName}/Loader/Route.php
```

In these files you can declare as many routes as you like. 
Static routes (not accepting parameters, require a perfect URL match) or dynamic routes, accepting parameters, that you can optionally restrict.
All routes must point to a Bundle and Controller, specificied by the `->destination($bundle,$controller[,$action])` method.

In the case of static route, you must provide the action in the route.
Dynamic routes can point to different actions depending on a URL parameter, specified by the `->trigger($url_parameter)` method.
If no action is provided, indexAction is called, if an action is provided but none match, `defaultAction()` is called.
A `preAction()` and `postAction()` wrap the action to be called.

* This static route will match /about-us/ and call `Private/Bundles/Pages/Controllers/Static.php->aboutUsAction();`

```php
Router::addRoute('about-us')
	->url('/about-us/')
	->destination('Pages','Static','aboutUs');
```

* This dynamic route will match /admin/{edit,update,delete,create}/ and /admin/
It will call `Private/Bundles/Admin/Controllers/Main.php->{edit,update,delete,create,index}Action();`
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

By default the `Private/Config/Config.ini` file is loaded.
You can use environment specific configuration files by detecting the current domain, or the current port.
```
[polyfony]
detection_method = "domain" ; or "port"
```
Then the framework will overrides Config.ini values with those of :
```
Private/Config/Dev.ini

[router]
domain = development.domain.dev
port = 8080
```
Or, if you are not running the development port :
```
Private/Config/Prod.ini

[router]
domain = production.domain.prod
port 80
```

* To retrieve configurations values (from the environment specific ini file)
```php
Config::get('group', 'key')
```

Having specific ini configuration files for development and production allows your to :
* set a bypass email to redirect all email sent in development environment
* enable compression, obfuscation/minifying and caching only in production
* show the profiler stack in development
* use different database configuration in development or production
* etc.

### Security

* To secure a page (require a user to be logged in)
```php
Security::enforce();
```

Failure to authenticate will throw an exception, and redirect to `Private/Config/Config.ini` -> `[router]` -> `login_route = ""`

* If you want to require a specific module (that can be bypassed by a level optionally)
```php
Security::enforce('MOD_NAME', $bypass_level);
```

Failure to comply with those requirements will throw an exception, but won't redirect the user anywhere.

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

### Bundle configurations

* Store some bundle specific data in Bundles/MyBundle/Loader/Config.php (ex. static list choices, etc.)
* Note that these configurations are merged with Config.php + Dev.ini/Prod.ini so all your configs are available in one place, with one interface : `Config`

```php
Config::set($group, $key, $value);
```

* Retrieve values (whole bundle, or a subset)

```php
Config::get($group);
Config::get($group, $key);
```

### Thumbnail

`Thumbnails` are generated in JPEG format. The source image can be PNG or JPEG.

```php
$thumbnail = new Thumbnail();
$status = $thumbnail
	->source("Private/Storage/Data/Photos/Original/photo.jpg")
	->destination("Private/Storage/Data/Photos/400/photo.jpg")
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
	->destination('Private/Storage/Data/Estimates/')
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
$request = new HttpRequest([$url, [$method, [$timeout, [$retry]]]]);
$status = $this->Request
	->url('https://maps.googleapis.com/maps/api/geocode/json')
	->timeout(15)
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

* Mail are very simple to use and built over PHPMailer

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

* Create an HTML element with an opening and a closing tag 

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
), 3)
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


Shortcuts are available from `Record` object (and objects inheriting Record class), ex.

* retrieve an account from its id
```php
$record = new Accounts(1);
$record->set('login', 'mylogin@example.com')

echo $record->input('login', array('data-validators'=>'required'));
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

### Filesystem

The filesystem class allows you to manipulate directories and files easily.
It is recommanded that you store all data in `Privata/Storage/Data/` wich is configured by default in `Config.ini` under `[filesystem]`.
You can force all operation to occur inside of this path by setting chroot option to true under `[filesystem]`.


```php
Filesystem::mkdir('/my-directory/', '0777');
Filesystem::mkdir('../../../../my-directory/', '0777');
```
* Both commands will create `Private/Storage/Data/my-directory/`

If you want to operate outside of the Data storage folder, set the last parameter, which is a chroot bypass, to true. The following example will break out of the chroot. Or simply disable the chroot option in the `Config.ini`

```php
Filesystem::get('/tmp/somefile.raw', true);
```

## Database structure

The framework can work without any database. 
You just loose `Security`, the `Mail` storage feature, the `Store\Database` engine and the `Logger`'s file feature.

* Bellow is the SQL statement used to create the database. The framework has been extensively tested with SQlite but should work with MySQL as well. SQlite generally is more that sufficient and a DBMS would be overkill.

```sql

CREATE TABLE "Accounts" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "id_level" numeric NULL,
  "is_enabled" numeric NULL,
  "creation_date" numeric NULL,
  "login" text NULL,
  "password" text NULL,
  "modules_array" text NULL,
  "account_expiration_date" numeric NULL,
  "session_expiration_date" numeric NULL,
  "session_key" text NULL,
  "last_login_origin" text NULL,
  "last_login_agent" text NULL,
  "last_login_date" numeric NULL,
  "last_failure_origin" numeric NULL,
  "last_failure_agent" numeric NULL,
  "last_failure_date" numeric NULL
);

CREATE TABLE "Logs" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "id_account" integer NULL,
  "id_level" integer NULL,
  "creation_date" numeric NULL,
  "bundle" text NULL,
  "controller" text NULL,
  "method" text NULL,
  "post_array" text NULL,
  "cookies_array" text NULL,
  "request_origin" text NULL,
  "request_method" text NULL,
  "request_uri" text NULL,
  "message" text NULL,
  FOREIGN KEY ("id_account") REFERENCES "Accounts" ("id") ON DELETE RESTRICT ON UPDATE NO ACTION
);

CREATE TABLE "Mails" (
  "id" integer NULL PRIMARY KEY AUTOINCREMENT,
  "is_sent" numeric NULL,
  "creation_date" numeric NULL,
  "sending_date" numeric NULL,
  "format" text NULL,
  "from_mail" text NULL,
  "from_name" text NULL,
  "body" text NULL,
  "subject" text NULL,
  "bcc_array" text NULL,
  "cc_array" text NULL,
  "to_array" text NULL,
  "files_array" text NULL
);

CREATE TABLE "Store" (
  "id" text NULL,
  "key" text NULL,
  "content" blob NULL
);

```

## Performance
Polyfony has been designed to be fast, no compromise. 
If implementating a « convenience » tool/function into the framework was to cost a global 30% bump in execution time, it is either implemented in a more efficient manner, or not implemented at all.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
