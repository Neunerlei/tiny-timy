# Tiny Timy - DateTimy
There are a lot of really powerful date and time libraries out there ([carbon](https://carbon.nesbot.com/docs/), [chronos](https://github.com/cakephp/chronos), ...) that are valid if you have to do a lot of date-related actions. For the most part of my daily work I just need date formatting and correct timezone handling. Using huge classes with thousands of lines of code for formatting a date time object always felt wrong. 

That's why there is tiny DateTimy, it's a tiny (150 lines without comments) extension for the native DateTime object and therefore 100% compatible with it. 

It brings common formats (sql, javascript, rss...), translated time formats (using LOCALE), a more resilient constructor and "automatic" timezone conversion.

## Installation
Install this package using composer:

```
composer require neunerlei/tiny-timy
```

#### Usage
You can use the DateTimy class in exactly the same way you would work with a DateTime object. 
```php
use Neunerlei\TinyTimy\DateTimy;
$date = new DateTimy();
$date = new DateTimy("now");
```

## Features

### Timezone conversion
The concept of timezone conversion is build on the assumption that you have (for the most part) two different main timezones you work with.
a.) the timezone of your server (in my reality UTC for the most part) and b.) the timezone of the client when you render a time for the end user.

So by default all new instances of the DateTimy class will be set to the "server" timezone which is UTC.
The given $time given to the constructor is also assumed to be in the "server" timezone until you provide another timezone manually.

If you want to convert the timezone to your client's needs you can use the "toClient()" method.
By default the "client" timezone is defined by date_default_timezone_get() until you manually change it.

To configure the timezones you can use the static configureTimezone() method to set both timezones to your needs.

```php
use Neunerlei\TinyTimy\DateTimy;

// Set the client timezone
DateTimy::configureTimezone("Europe/London");
// Set the server timezone
DateTimy::configureTimezone("Europe/Moscow", true);

// New created instances ar now per default in the "Europe/Moscow" timezone
$i = new DateTimy();
$i->getTimezone()->getName(); // "Europe/Moscow"

// Convert it to the client timezone
$i->toClientTimezone()->getTimezone()->getName(); // "Europe/London"

// And back to the server timezone
$i->toServerTimezone()->getTimezone()->getName(); // "Europe/Moscow"
```

### Extended constructor
The constructor of the DateTimy class was extended to handle different time options more reliable than the default constructor would.

##### Numeric values
Numeric values will automatically be parsed as timestamp, so no need to put an "@" in front of it.
```php
use Neunerlei\TinyTimy\DateTimy;
$date = new DateTimy(time() + 400);
```

##### Cloning / Transforming Datetime objects
You can pass an instance of a DateTime() or DateTimy() class as $time. The constructor will handle the conversion for you
```php
use Neunerlei\TinyTimy\DateTimy;
$date = new DateTimy(new DateTimy());
$date = new DateTimy(new DateTime());
```

##### Timezone from string
The constructor can now handle timezones based on a string as well as the default DateTimeZone() objects.
```php
use Neunerlei\TinyTimy\DateTimy;
$date = new DateTimy("now", "Europe/Berlin");
```

##### Using a format
While using createFromFormat() to create a new instance based on a string that follows a non-standard format is still possible, 
the constructor now has the capability to read a string from a format as well.

```php
use Neunerlei\TinyTimy\DateTimy;
$date = new DateTimy("2020.03.13 00:00", null, "Y.m.d H:i");

// You can use registered formats as well
$date = new DateTimy("2020.03.13 00:00", null, DateTimy::FORMAT_TYPE_DATE_AND_TIME);
```

#### Common Formats
I am a lazy person when it comes to work I have to do repeatedly. Date / Time Formats in PHP is such a task. Therefore the 
class provides you with some preconfigured formats and the option to change them or to create completely new ones.
The default formats included are:

* date => Y.m.d
* time => H:i
* dateAndTime => Y.m.d H:i
* sql => Y-m-d H:i:s
* sqlDate => Y-m-d
* js => D M d Y H:i:s O
* rss => D, d M Y H:i:s T

All formats can be applied using the "format" method, or by using the magic method that brings support for every registered format like: formatDateAndTime()

```php
use Neunerlei\TinyTimy\DateTimy;
$i = new DateTimy();
$i->format(DateTimy::FORMAT_TYPE_DATE);
// or
$i->formatDateAndTime();
// or
$i->format("dateAndTime");
```

You can add a new format or modify preconfigured formats using the configureFormat() method.

```php
use Neunerlei\TinyTimy\DateTimy;
// Editing an existing format
DateTimy::configureFormat(DateTimy::FORMAT_TYPE_DATE, "d.m.Y");
// Add your own format
DateTimy::configureFormat("specialFormat", "d-m-Y");
```

After you added your own format you can use it like any preconfigured format like:

```php
use Neunerlei\TinyTimy\DateTimy;
// Create from your special format
$i = new DateTimy("...", null, "specialFormat");
// Print your special format with
$i->formatSpecialFormat();
// or
$i->format("specialFormat");
```

#### Localized formatting
By default php only translates your time formats for month or weekday names only when you use the strftime() method.
This is not supported for DateTime objects and requires its own special syntax (I forget the chars every time...).

For that the DateTimy class provides you with the formatLocalized() method. It translates F, l, M or D based on your current LOCALE exactly like strftime() would
**while keeping the same syntax as format() does**!

```php
use Neunerlei\TinyTimy\DateTimy;
// LOCALE de_DE.UTF8
$i = new DateTimy();
$i->formatLocalized("D"); // Montag
```

## Running tests

- Clone the repository
- Install the dependencies with ```composer install```
- Run the tests with ```composer test```

## Special Thanks
Special thanks goes to the folks at [LABOR.digital](https://labor.digital/) (which is the word german for laboratory and not the english "work" :D) for making it possible to publish my code online.

## Postcardware
You're free to use this package, but if it makes it to your production environment I highly appreciate you sending me a postcard from your hometown, mentioning which of our package(s) you are using.

You can find my address [here](https://www.neunerlei.eu/). 

Thank you :D 
