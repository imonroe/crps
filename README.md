# crps

CRPS is the Coldreader Persistent Storage system. On its own, you may not find it very useful; it doesn't have any front-end information in it at all.  For a working, real-world implementation, please [install the full Coldreader project.](https://github.com/imonroe/coldreader)

CRPS is designed to be a databasing system for when you don't know in advance what kind of information you want to store.  It implments a data model where you have a bunch of arbitrary subjects, each of which has an arbitrary number of Aspects.  

Aspects can be thought of as some kind of fact or content that is relevant to its Subject.  Aspect data is stored in the database as text, in the broad sense. JSON counts as text, for instance, as do hyperlinks, markup, etc.  In addition to storing data, Aspects can be purely functional--a call to an external API, a web scraper to grab a bit of content from elsewhere, and so forth.  Each type of Aspect has a display method that produces the markup for use in templates.  Additionally, each type of Aspect also has a parse() function which can be called automatically by scheduled jobs.  From this function, an Aspect may perform actions on the data model; fetching new data from an external source, updating its own value, or creating/updating other Subjects or Aspects.

New Aspect Types are easy and quick to build. Upon creation, the new Aspect Type will automatically add new boilerplate code for the necessary GUI forms (via the Laravel Collective Form API) and override methods; an Aspect Type definition is an OOP child class of the parent Aspect Type and inherits its methods. Custom Aspect Types can also be child classes of other custom Aspect Types, so if you build an APIResultsAspect class, you can make a FacebookAPIResultsAspect class that inherits the work you've already done for APIResultsAspect, for instance. 

## Dependencies
- Laravel 5.4+
- laravelcollective/html 5.4+
- spatie/laravel-medialibrary 6+
- league/commonmark 0.16+
- imonroe/mimeutils 0.1+

## Install

Via Composer

``` bash
$ composer require imonroe/crps
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