# Shortpixel integration for laravel 9+

[![Latest Version on Packagist](https://img.shields.io/packagist/v/walrussoup/laravel-shortpixel.svg?style=flat-square)](https://packagist.org/packages/walrussoup/laravel-shortpixel)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/walrussoup/laravel-shortpixel/run-tests?label=tests)](https://github.com/walrussoup/laravel-shortpixel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/walrussoup/laravel-shortpixel/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/walrussoup/laravel-shortpixel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/walrussoup/laravel-shortpixel.svg?style=flat-square)](https://packagist.org/packages/walrussoup/laravel-shortpixel)

Makes using the shortpixel reducer api with laravel slightly less painful.

## Archiving
Archiving this to move to BunnyCDN. It's cheaper, and their API for using the on the fly image optimization is only $9.99 a month - literally cannot beat those prices. 

## Installation

You can install the package via composer:

```bash
composer require walrussoup/laravel-shortpixel
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-shortpixel-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-shortpixel-config"
```

This is the contents of the published config file:

```php
return [
    'api_key' => env('SHORTPIXEL_API_KEY'),
    'plugin_version' => env('SHORTPIXEL_PLUGIN_VERSION'),
    'log_channel' => env('SHORTPIXEL_LOG_CHANNEL', 'default')
];
```

## Usage

I recommend setting a log channel specific to compression. I set this in logging & simply set the output to another log file.

```php
$laravelShortpixel = new WalrusSoup\LaravelShortpixel();
// set this up yourself or just let the container do it
$laravelShortpixel->setApiKey('your api key');
$laravelShortpixel->setPluginVersion('your plugin version');
$laravelShortpixel->setLogChannel('your log channel');

// Create an image configuration that compresses the original format and also outputs a webp format
$configuration = (new CompressionConfig())
        ->resizeToCover(500, 400)
        ->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')
        // or, add multiple images using addImages()
        ->useLosslessCompression()
        ->convertToWebp()
        ->retainOriginalFormat();

/** @var ShortpixelCompressionResult $results */
$results = $laravelShortpixel->callShortpixelAndWait($configuration);

foreach($results as $result) {
    ray($result->getOriginalUrl(), $result->getCompressedUrl());
}
```

### Understanding The API... kinda
The Shortpixel API accepts the original configuration in it's entirety to keep track of compression results. If you are not using a long-running job
with `callShortpixelAndWait()` you will need to store the full configuration somewhere. I recommend letting a job handle this via its serialization.

The other thing is keeping track of the original names. For the sake of keeping things consistent, I made some methods for these.

```php
// If you requested a conversion to another format, this will help you get the original name
$originalName = $result->getOriginalFilenameWithNewExtension();
// WEBP and AVIF are split off into a separate key, so you can use this to get it I suppose
$originalNameWebp = $result->getOriginalFilenameWebpLossy();
// Again, these are names only. You will need to still download the URL from the other key, for instance:
file_get_contents($result->getWebpLosslessURL());
```

### Job Example
This is how I use it. It could probably be better, but it works for my case since jobs can be retried later.

```php
namespace App\Jobs;

use WalrusSoup\LaravelShortpixel\CompressionConfig;
use WalrusSoup\LaravelShortpixel\LaravelShortpixel;

class CompressImage implements ShouldQueue
{
    public function __construct(public CompressionConfig $config) {}
    
    public function handle(LaravelShortpixel $laravelShortpixel)
    {
        // Queue the job and forget about it for 5 minutes
        $laravelShortpixel->callShortPixel($this->config);
        // Fetch the result later
        CheckCompressionResults::dispatch($this->config)->delay(now()->addMinutes(5));
    }
}

// another job
class CheckCompressionResults implement ShouldQueue
{
    public $tries = 1;
    
    public function __construct(public CompressionConfig $config) {}
    
    public function handle(LaravelShortpixel $laravelShortpixel)
    {
        // Again, we have to give them the original config. It won't compress again, it will just return the results
        $results = $laravelShortpixel->callShortPixelAndWait($this->config);
        
        foreach($results as $result) {
            // do something with the result
        }
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [JL](https://github.com/WalrusSoup)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
