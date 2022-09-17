<?php

use WalrusSoup\LaravelShortpixel\CompressionConfig;

it('can generate proper "contain" resize requests', function () {
    $configuration = new CompressionConfig();
    $configuration
        ->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')
        ->resizeToContain(500, 500)
        ->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'resize' => 3,
        'resize_width' => 500,
        'resize_height' => 500,
    ]);
});

it('can generate proper "cover" resize requests', function () {
    $configuration = new CompressionConfig();
    $configuration
        ->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')
        ->resizeToCover(500, 500)
        ->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'resize' => 1,
        'resize_width' => 500,
        'resize_height' => 500,
    ]);
});

it('does not include resize width, height values when no resizing is done', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->useLossyCompression();

    expect($configuration->getPayload())->not()->toHaveKey('resize_width');
    expect($configuration->getPayload())->not()->toHaveKey('resize_height');
});

it('automatically disposes of EXIF data', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'keep_exif' => 0,
    ]);
});

it('can be set to keep EXIF data', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->keepExif()->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'keep_exif' => 1,
    ]);
});

it('can convert to webp', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->convertToWebp()->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'convertto' => 'webp',
    ]);
});

it('can convert to avif', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->convertToAvif()->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'convertto' => 'avif',
    ]);
});

it('can convert to webp and avif', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')->convertToAvif()->convertToWebp()->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'convertto' => 'webp|avif',
    ]);
});

it('can convert to webp and avif with the original image format included', function () {
    $configuration = new CompressionConfig();
    $configuration->addImage('https://images.unsplash.com/photo-1611457194403-d3aca4cf9d11')
        ->convertToAvif()
        ->convertToWebp()
        ->retainOriginalFormat()
        ->useLossyCompression();

    expect($configuration->getPayload())->toMatchArray([
        'convertto' => '+webp|+avif',
    ]);
});
