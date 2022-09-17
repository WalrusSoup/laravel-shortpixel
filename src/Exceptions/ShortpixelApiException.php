<?php

namespace WalrusSoup\LaravelShortpixel\Exceptions;

use Exception;

class ShortpixelApiException extends Exception
{
    public static function createFromApiResponse(int $statusCode)
    {
        $message = match ($statusCode) {
            1 => 'No errors, image scheduled for processing.',
            2 => 'No errors, image processed, download URL available.',
            -102 => 'Invalid URL. Please make sure the URL is properly urlencoded and points to a valid image file.',
            -105 => 'URL is missing for the call.',
            -106 => 'URL is inaccessible from our server(s) due to access restrictions.',
            -107 => 'Too many URLs in a POST, maximum allowed has been exceeded.',
            -108 => 'Invalid user used for optimizing images from a particular domain.',
            -113 => 'Too many inaccessible URLs from the same domain, please check accessibility and try again.',
            -201 => 'Invalid image format.',
            -202 => 'Invalid image or unsupported image format.',
            -203 => 'Could not download file.',
            -204 => 'The file couldn\'t be optimized, possibly timedout.',
            -205 => 'The file\'s width and/or height is too big.',
            -206 => 'The PDF file is password protected and it cannot be optimized.',
            -301 => 'The file is larger than the remaining quota.',
            -302 => 'The file is no longer available.',
            -303 => 'Internal API error: the file was not written on disk.',
            -305 => 'Internal API error: Unknown, details usually in message.',
            -401 => 'Invalid API key. Please check that the API key is the one provided to you.',
            -403 => 'Quota exceeded. You need to subscribe to a larger plan or to buy an additional one time package to increase your quota.',
            -404 => 'The maximum number of URLs in the optimization queue reached. Please try again in a minute.',
            -500 => 'API is in maintenance mode. Please come back later.',
            default => 'Unknown error.',
        };

        return new self($message);
    }
}
