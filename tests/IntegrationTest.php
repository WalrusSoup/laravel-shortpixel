<?php

use Illuminate\Support\Facades\Http;
use WalrusSoup\LaravelShortpixel\CompressionConfig;

$firstImage = 'https://images.unsplash.com/photo-1590796583326-afd3bb20d22d';
$secondImage = 'https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11';

it('can submit two images for resizing', function () use ($firstImage, $secondImage) {
    $configuration = new CompressionConfig();
    $configuration->addImages([$firstImage, $secondImage])
        ->resizeToContain(500, 500)
        ->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'urllist' => [
            $firstImage,
            $secondImage,
        ],
    ]);
});

it('returns a list of ShortpixelCompressionResults', function () use ($firstImage, $secondImage) {
    $configuration = new CompressionConfig();
    $configuration->addImages([$firstImage, $secondImage])
        ->resizeToContain(500, 500)
        ->useLossyCompression();

    $shortpixelService = new \WalrusSoup\LaravelShortpixel\LaravelShortpixel();
    Http::fake([
        'https://api.shortpixel.com/v2/reducer.php' => Http::response([
            [
                'Status' => [
                    'Code' => '2',
                    'Message' => 'Success',
                ],
                'OriginalURL' => 'https://images.unsplash.com/photo-1590796583326-afd3bb20d22d',
                'LosslessURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970.jpg',
                'LossyURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970-lossy.jpg',
                'WebPLosslessURL' => 'NA',
                'WebPLossyURL' => 'NA',
                'AVIFLosslessURL' => 'NA',
                'AVIFLossyURL' => 'NA',
                'OriginalSize' => '1382256',
                'LosslessSize' => '64662',
                'LoselessSize' => '64662',
                'LossySize' => '36001',
                'WebPLosslessSize' => 'NA',
                'WebPLoselessSize' => 'NA',
                'WebPLossySize' => 'NA',
                'AVIFLosslessSize' => 'NA',
                'AVIFLossySize' => 'NA',
                'TimeStamp' => '2022-09-17 21:57:31',
                'PercentImprovement' => '97.40',
            ],
            [
                'Status' => [
                    'Code' => '2',
                    'Message' => 'Success',
                ],
                'OriginalURL' => 'https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11',
                'LosslessURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970.jpg',
                'LossyURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970-lossy.jpg',
                'WebPLosslessURL' => 'NA',
                'WebPLossyURL' => 'NA',
                'AVIFLosslessURL' => 'NA',
                'AVIFLossyURL' => 'NA',
                'OriginalSize' => '1382256',
                'LosslessSize' => '64662',
                'LoselessSize' => '64662',
                'LossySize' => '36001',
                'WebPLosslessSize' => 'NA',
                'WebPLoselessSize' => 'NA',
                'WebPLossySize' => 'NA',
                'AVIFLosslessSize' => 'NA',
                'AVIFLossySize' => 'NA',
                'TimeStamp' => '2022-09-17 21:57:31',
                'PercentImprovement' => '97.40',
            ],
        ]),
    ]);

    $results = $shortpixelService->callShortPixelAndWait($configuration);
    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);
    expect($results[0])->toBeInstanceOf(\WalrusSoup\LaravelShortpixel\ShortpixelCompressionResult::class);
    expect($results[1])->toBeInstanceOf(\WalrusSoup\LaravelShortpixel\ShortpixelCompressionResult::class);

    expect($results[0]->getOriginalUrl())->toBe($firstImage);
    expect($results[1]->getOriginalUrl())->toBe($secondImage);
});

it('can return mixed compression results', function () use ($firstImage, $secondImage) {
    $configuration = new CompressionConfig();
    $configuration->addImages([$firstImage, $secondImage])
        ->resizeToContain(500, 500)
        ->useLossyCompression();

    $shortpixelService = new \WalrusSoup\LaravelShortpixel\LaravelShortpixel();
    Http::fake([
        'https://api.shortpixel.com/v2/reducer.php' => Http::response([
            [
                'Status' => [
                    'Code' => '2',
                    'Message' => 'Success',
                ],
                'OriginalURL' => 'https://images.unsplash.com/photo-1590796583326-afd3bb20d22d',
                'LosslessURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970.jpg',
                'LossyURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970-lossy.jpg',
                'WebPLosslessURL' => 'NA',
                'WebPLossyURL' => 'NA',
                'AVIFLosslessURL' => 'NA',
                'AVIFLossyURL' => 'NA',
                'OriginalSize' => '1382256',
                'LosslessSize' => '64662',
                'LoselessSize' => '64662',
                'LossySize' => '36001',
                'WebPLosslessSize' => 'NA',
                'WebPLoselessSize' => 'NA',
                'WebPLossySize' => 'NA',
                'AVIFLosslessSize' => 'NA',
                'AVIFLossySize' => 'NA',
                'TimeStamp' => '2022-09-17 21:57:31',
                'PercentImprovement' => '97.40',
            ],
            [
                'Status' => [
                    'Code' => '-201',
                    'Message' => 'Invalid image format',
                ],
                'OriginalURL' => 'https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11',
                'LosslessURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970.jpg',
                'LossyURL' => 'http://api.shortpixel.com/f/ed7536a6d2c316bfc47b6e35b7d19970-lossy.jpg',
                'WebPLosslessURL' => 'NA',
                'WebPLossyURL' => 'NA',
                'AVIFLosslessURL' => 'NA',
                'AVIFLossyURL' => 'NA',
                'OriginalSize' => '1382256',
                'LosslessSize' => '64662',
                'LoselessSize' => '64662',
                'LossySize' => '36001',
                'WebPLosslessSize' => 'NA',
                'WebPLoselessSize' => 'NA',
                'WebPLossySize' => 'NA',
                'AVIFLosslessSize' => 'NA',
                'AVIFLossySize' => 'NA',
                'TimeStamp' => '2022-09-17 21:57:31',
                'PercentImprovement' => '97.40',
            ],
        ]),
    ]);

    $results = $shortpixelService->callShortPixelAndWait($configuration);
    expect($results)->toBeArray();
    expect($results)->toHaveCount(2);
    expect($results[0])->toBeInstanceOf(\WalrusSoup\LaravelShortpixel\ShortpixelCompressionResult::class);
    expect($results[1])->toBeInstanceOf(\WalrusSoup\LaravelShortpixel\ShortpixelCompressionResult::class);

    expect($results[1]->failed())->toBeTrue();
});
