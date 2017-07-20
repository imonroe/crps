# crps

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

CRPS is the Coldreader Persistent Storage system.
Not much here yet.

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


## Usage

``` php
$cprs = new imonroe\crps();
echo $cprs->echoPhrase('Hello, world');
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
