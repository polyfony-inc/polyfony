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

* retrieve a cookie
```php
pf\Request::cookie('pfLanguage');
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

* If you want to require a specific level of account
```php
// code soon
```

* If you want to require a specific module (that can be bypassed by a level optionally)
```php
// code soon
```

* To check for credentials 
```php
Security::hasModule($module_name);
Security::hasLevel($level);
```

### Locales

### Exception

### Notice

### Response

### Store

### Runtime

## Performance
Polyfony has been designed to be fast, no compromise.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
