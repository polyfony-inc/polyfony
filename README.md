[![SensioLabsInsight](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0/big.png)](https://insight.sensiolabs.com/projects/713fa5be-b3d6-4a10-b544-90ef45580ec0)

## Polyfony 2 is a PHP micro framework that brings the cool parts of Symfony in a simpler way

Features : routing, bundles, controllers, views, database abstraction, environments, locales, cache, vendor, helpers, authentication, profiler…
Without pre-compilation, cumbersome cache, dozens configuration files, composer, cli binary or other annoying steps.


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

### Form

### Security

### Locales

### Exception

### Notice

### Response

### Store

### Runtime

## Performance
Polyfony has been designed to be fast, no compromise.
The whole framework takes less than 400 Kb of disk space (a third of it being comment lines) and runs on 650 Kb of RAM.

## Security
The codebase is small, straightforward and abundantly commented. It's audited using SensioInsight, RIPS, and Sonar.

## Coding Standard
Polyfony2 follows the PSR-0, PSR-1, PSR-4 coding standards. It does not respect PSR-2, as tabs are used for indentation.
