# crps

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

CRPS is the Coldreader Persistent Storage system.

** Please note, this package is in development right now, and not yet ready to use **

CRPS is designed to be a databasing system for when you don't know in advance what kind of information you want to store.  It implments a data model where you have a bunch of arbitrary subjects, each of which has an arbitrary number of Aspects.  

Aspects can be thought of as some kind of fact or content that is relevant to its Subject.  Aspect data is stored in the database as text, in the broad sense. JSON counts as text, for instance, as do hyperlinks, markup, etc.  In addition to storing data, Aspects can be purely functional--a call to an external API, a web scraper to grab a bit of content from elsewhere, and so forth.  Each type of Aspect has a display method that produces the markup for use in templates.  Additionally, each type of Aspect also has a parse() function that it automatically called by a scheduled task once every five minutes.  From this function, an Aspect may perform actions on the data model; updating its own value, or creating/updating other Subjects or Aspects.

New Aspect Types are easy and quick to build. Upon creation, the new Aspect Type will automatically add new boilerplate code for the necessary GUI forms (via the Laravel Collective Form API) and override methods; an Aspect Type definition is an OOP child class of the parent Aspect Type and inherits its methods. Custom Aspect Types can also be child classes of other custom Aspect Types, so if you build an APIResultsAspect class, you can make a FacebookAPIResultsAspect class that inherits the work you've already done for APIResultsAspect, for instance. 

## Dependencies
- Laravel 5.4
- jQuery

## Install

Via Composer

``` bash
$ composer require imonroe/crps
```

In your /config/app.php file, add the following to the 'providers' array:
``` php
imonroe\crps\crpsServiceProvider::class,
```
You should also add the following class aliases:
``` php
'Aspect' => imonroe\crps\Aspect::class,
'AspectType' => imonroe\crps\AspectType::class,
'Subject' => imonroe\crps\Subject::class,
'SubjectType' => imonroe\crps\SubjectType::class,
'Ana' => imonroe\crps\Ana::class
```

Add the following line to the top of your /app/Console/Kernel.php file:
``` php
use imonroe\crps\Aspect;
```
Then, in the your schedule method, make it look like:
``` php
protected function schedule(Schedule $schedule){
	$schedule->call(function () {
		$aspects = Aspect::all();
		foreach ($aspects as $aspect){
			$aspect->parse();
		}
	})->name('parse_loop')->everyFiveMinutes()->withoutOverlapping();
}
```
Now we need to set up a file for your own custom aspects.

First, copy the CustomAspects.php file to your /app directory.  This is where boilerplate code for new aspect types will be written.  This is the heart of customizing the system

You're going to need to add a line to your application's composer.json, in the "autoload" key:
``` php
"files": ["app/CustomAspects.php"]
```


## Usage

``` php

$subject = new Subject;
foreach ($subject->aspects() as $aspect){
	$aspect->display_aspect();
}

```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email ian@ianmonroe.com instead of using the issue tracker.

## Credits

- [Ian Monroe][link-author]
- [All Contributors][link-contributors]

## License

GPL V3. Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/imonroe/crps.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/imonroe/crps/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/imonroe/crps.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/imonroe/crps.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/imonroe/crps.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/imonroe/crps
[link-travis]: https://travis-ci.org/imonroe/crps
[link-scrutinizer]: https://scrutinizer-ci.com/g/imonroe/crps/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/imonroe/crps
[link-downloads]: https://packagist.org/packages/imonroe/crps
[link-author]: https://github.com/imonroe
[link-contributors]: ../../contributors
