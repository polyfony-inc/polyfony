[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony is an intuitive, lightweight and powerful PHP micro-framework.

#### Philosophy
Inspired by Symfony and Laravel but tailored to favour an inclination towards extreme simplicity and efficiency.

Compared to major PHP frameworks, Polyfony covers 95%+ of what we need most of the time, and does so using a fragment of the ressources, space, configuration files and dependencies required by major frameworks.

#### Features
Routing, bundles, controllers, views, ORM, environments, locales, cache, authentication, form helper... and limitless extensibility via composer.

#### Footprint [of an Hello World](https://github.com/polyfony-inc/polyfony/wiki/Benchmark)
* ≤ 300 Ko of disk space *(35% of comment lines)*
* ≤ 400 Ko of RAM
* ≤ 2.5 ms (cold)

## Requirements
Current *hard* requirements are : Linux/MacOS/xBSD, PHP >= 7.1, ext-pdo, ext-sqlite3, ext-mbstring, ext-msgpack and a rewrite module (mod_rewrite)

## Installation

* Run this command to download the framework *(and its dependencies)* to your project directory

```
composer create-project --stability=dev polyfony-inc/polyfony your-project-folder
```
*--stability=dev allows you to git pull later on*

* With lighttpd, set the webroot of your webserver to `Public/`
```
server.document-root = "/var/www/your-project-folder/Public/"
```

* With lighttpd, set this rewrite rule

```
url.rewrite-once = (
    "^(?!/Assets/).*" => "/?"
    # Let's Encrypt version bellow
    # "^(?!/Assets/)(?!/\.well-known/).*" => "/?"
)
```


## Quick tour
You can read this quick tour, or just browse the `Private/Bundles/Demo/` code.
The code bellow assumes that your are using the `Polyfony` namespace before each call.

### [Request](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyrequest)

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

* check if the request is done thru TLS/SSL
```php
Request::isSecure();
```

* check if the request is from the command line
```php
Request::isCli();
```

### [Database](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyquery)

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

* Retrieve a single account by its ID
```php
$account = new Models\Accounts(1);
```

* Retrieve a single account by its email
```php
$account = new Models\Accounts(['email'=>'root@local.domain']);
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
->where()					// == $value
->whereNot()			// <> $value
->whereBetween()		// BETWEEN $min_value AND $max_value
->whereMatch()			// MATCH column AGAINST $value
->whereContains()		// % $value %
->whereEndWith()		// % $value
->whereStartsWith() 		// $value %
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
The declaration has to be done in the model, with the constant `VALIDATORS` being an array. 
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



### [Router](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyrouter)

* Each bundle has a file to place your routes
```php
Private/Bundles/{BundleName}/Loader/Route.php
```

In these files you can declare as many routes as you like. 
Static routes (not accepting parameters, require a perfect URL match) or dynamic routes, accepting parameters, that you can optionally restrict.
All routes must point to a Bundle and Controller, specificied by the second parameter of `Router::map('/url/', 'Bundle/Controller')` method.

**Static** route, you must provide the action in the route, as such 
`Router::map('/url/', 'Bundle/Controller@action)`.

**Dynamic** routes can point to different actions depending on a URL parameter, as such 
`Router::map('/admin/:what/:id/', 'Bundle/Controller@{what}')`.

If no action is provided, `indexAction` is called, if an action is provided but none match, `defaultAction()` is called.
A `preAction()` and `postAction()` wrap the action to be called.

* The following static route will match a GET request to /about-us/ and call `Private/Bundles/Pages/Controllers/Static.php->aboutUsAction();`

```php
Router::get('/about-us/', 'Pages/Static@aboutUs');
```

`Router::get()` is a shortcut for the fourth parameter of `Router::map()`, also available are `Router::post()`, `Router::delete()`, `Router::put()`

* The following dynamic route will match a request of any method to /admin/{edit,update,delete,create}/ and /admin/
It will call `Private/Bundles/Admin/Controllers/Main.php->{edit,update,delete,create,index}Action();`
```php
Router::map('/admin/:action/:id/', 'Admin/Main@{action}')
	->where([
		'action'=>['edit','update','delete','create']
	]);
```

You can restrict parameters further, you can pass :
an array of allowed value (it will also match no value)
a regex (it will also match no value)
a boolean true (it will match anything but a missing value)

### Environments

https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyconfig

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
Or, if you are not in development environment :
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

Having specific ini configuration files for development and production allows you to :
* set a bypass email to redirect all email sent in development environment
* enable compression, obfuscation/minifying and caching only in production
* show the profiler in development
* use different database configuration in development or production
* etc.

### [Security](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonysecurity)

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

### [Exception](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyexception)

Exception are routed to a route named « exception » if any, otherwise exception are thrown normally.
The status code is 500 by default, you can specify any HTTP status code. The cache is disabled by such a status code.

```php
Throw new Exception($error_message, $http_status_code);
```


### [Response](https://github.com/polyfony-inc/polyfony/wiki/Reference#class-polyfonyresponse)

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

* The `Response` provides some headers by default *Relative slowness of this example is due the the filesystem being NFS thru wifi*

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

The example bellow shows the same Hello World `Response` as above, but from the cache

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

### [Bundle configurations](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyconfig)

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


### [Mail](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonymail)

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

### [Element](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyelement)

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

### [Form](https://github.com/polyfony-inc/polyfony/wiki/Reference#interface-polyfonyform)

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
$account = new Accounts(1);
$account->set('login', 'mylogin@example.com')

echo $account->input('login', array('data-validators'=>'required'));
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

In the middle of your html form (in a View)

```html
<form action="" method="post">
<!-- more form here -->

<?= new Polyfony\Form\Token(); ?>

<!-- more form here -->
</form>
```

In your controller

```php
Polyfony\Form\Token::enforce();
```

**That's it.** 

* Instanciating a "Token" objet generates a unique token, stores it in the PHP SESSION and builds an html input element. 
* The static enforce method, checks if a request has been POSTed, and if so, if a token exists, and matches one stored in the session. Otherwise, throws an exception and redirects to the previous page.

## Database structure

The framework has been extensively tested using SQLite, it *may* work with other engines, it defitively works without. 
Without, you'd just loose `Security`, the `Mail` storage feature, the `Store\Database` engine and the `Logger`'s database feature.

The database's structure is available by dumping the SQLite Database `Private/Storage/Database/Polyfony.db`.
The PDO driver can be changed for MySQL, PosgreSQL in `Private/Config/Config.ini`.



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
| Polyfony\Notice()      | DEPRECATED   | Bootstrap\Alert()       | require sib-retail/polyfony-bootstrap |
| Polyfony\Thumnbail()   | DEPRECATED   | Intervention\Image()    | require intervention/image            |
| Polyfony\HttpRequest() | DEPRECATED   | Curl\Curl()             | require php-curl-class/php-curl-class |
| Polyfony\Filesystem()  | DEPRECATED   | Filesystem\Filesystem() | require symfony/filesystem            |
| Polyfony\Uploader()    | DEPRECATED   | FileUpload\FileUpload() | require gargron/fileupload            |
| Polyfony\Validate()    | DISCONTINUED | Validator\Validation()  | require symfony/validator              |


## [Performance](https://github.com/polyfony-inc/polyfony/wiki/Benchmark)
Polyfony has been designed to be fast, no compromise (> 2000 req/s). 
If implementating a « convenience » tool/function into the framework was to cost a global bump in execution time, it is either implemented in a more efficient manner, or not implemented at all.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
