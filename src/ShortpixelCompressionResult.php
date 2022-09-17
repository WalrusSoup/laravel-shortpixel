<?php

namespace WalrusSoup\LaravelShortpixel;

use Exception;
use WalrusSoup\LaravelShortpixel\Exceptions\ShortpixelApiException;

class ShortpixelCompressionResult
{
    public int $code;
    public string $message = '';

    public ?string $originalURL = null;
    public ?string $losslessURL = null;
    public ?string $lossyURL = null;
    public ?string $webpLosslessURL = null;
    public ?string $webpLossyURL = null;
    public ?string $avifLosslessURL = null;
    public ?string $avifLossyURL = null;

    public ?int $originalSize = null;
    public ?int $losslessSize = null;
    public ?int $loselessSize = null;
    public ?int $lossySize = null;
    public ?int $webPLosslessSize = null;
    public ?int $webPLoselessSize = null;
    public ?int $webPLossySize = null;
    public ?int $avifLosslessSize = null;
    public ?int $avifLossySize = null;

    public ?string $timeStamp = null;
    public ?string $percentImprovement = null;

    /**
     * Returns true if this compression is ready
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->code === 2;
    }

    /**
     * Returns true if this compression is still processing
     *
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->code === 1;
    }

    /**
     * Returns true if this compression failed
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->code < 0;
    }

    public function throwException(): void
    {
        if ($this->failed()) {
            throw ShortpixelApiException::createFromApiResponse($this->code);
        }
    }

    /**
     * Returns the message from the shortpixel api
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Returns the URL of the original image
     *
     * @return string|null
     */
    public function getOriginalURL(): ?string
    {
        return $this->originalURL;
    }

    /**
     * Returns the URL of the lossless version of the image.
     *
     * @return string|null
     */
    public function getLosslessURL(): ?string
    {
        return $this->losslessURL;
    }


    /**
     * Returns the URL of the lossy version of the image
     *
     * @return string|null
     */
    public function getLossyURL(): ?string
    {
        return $this->lossyURL;
    }

    /**
     * Returns the URL of the lossless WebP version of the image.
     *
     * @return string|null
     */
    public function getWebpLosslessURL(): ?string
    {
        return $this->webpLosslessURL;
    }

    /**
     * Returns the URL of the lossy WebP version
     *
     * @return string|null
     */
    public function getWebpLossyURL(): ?string
    {
        return $this->webpLossyURL;
    }

    /**
     * Returns the AVIF lossless URL, or null if none was requested
     *
     * @return string|null
     */
    public function getAvifLosslessURL(): ?string
    {
        return $this->avifLosslessURL;
    }

    /**
     * Returns the AVIF lossy URL, or null if none was requested
     *
     * @return string|null
     */
    public function getAvifLossyURL(): ?string
    {
        return $this->avifLossyURL;
    }

    /**
     * In case you need this to write it to a new directory
     *
     * @return string|null
     */
    public function getOriginalClientName(): ?string
    {
        return pathinfo($this->originalURL, PATHINFO_BASENAME);
    }

    /**
     * To get the original name of the file (without the extension)
     *
     * @return string|null
     */
    public function getOriginalClientNameWithoutExtension(): ?string
    {
        return pathinfo($this->originalURL, PATHINFO_FILENAME);
    }

    /**
     * This will return the original filename with a new extension, if a new extension was given by ShortPixel in LossyUrl
     *
     * @return string
     */
    public function getOriginalFilenameWithNewExtension(): string
    {
        return $this->getOriginalClientNameWithoutExtension() . '.' . pathinfo($this->lossyURL, PATHINFO_EXTENSION);
    }

    /**
     * This will return the avif name, using the original filename
     *
     * @return string
     */
    public function getOriginalFilenameAvifLossy(): string
    {
        return $this->getOriginalClientNameWithoutExtension() . '.' . pathinfo($this->avifLossyURL, PATHINFO_EXTENSION);
    }

    /**
     * This will return the avif name, using the original filename
     *
     * @return string
     */
    public function getOriginalFilenameAvifLossless(): string
    {
        return $this->getOriginalClientNameWithoutExtension() . '.' . pathinfo($this->avifLosslessURL, PATHINFO_EXTENSION);
    }

    /**
     * This will return the webp name, using the original filename
     *
     * @return string
     */
    public function getOriginalFilenameWebpLossless(): string
    {
        return $this->getOriginalClientNameWithoutExtension() . '.' . pathinfo($this->webpLosslessURL, PATHINFO_EXTENSION);
    }

    /**
     * This will return the webp name, using the original filename
     *
     * @return string
     */
    public function getOriginalFilenameWebpLossy(): string
    {
        return $this->getOriginalClientNameWithoutExtension() . '.' . pathinfo($this->webpLossyURL, PATHINFO_EXTENSION);
    }

    /**
     * Decodes the shortpixel reducer api response to a more usable form with some minor typing
     *
     * @param array $response
     * @return static
     */
    public static function createFromResponse(array $response): static
    {
        $result = new static();
        if(!isset($response['Status'])) {
            if(isset($response['Code'])) {
                $result->code = (int)$response['Code'];
            }
            if(isset($response['Message'])) {
                $result->message = (string)$response['Message'];
            }

            return $result;
        }

        $result->code = (int)$response['Status']['Code'];
        $result->message = $response['Status']['Message'];

        $result->originalURL = $response['OriginalURL'] ?? null;
        $result->losslessURL = $response['LosslessURL'] ?? null;
        $result->lossyURL = $response['LossyURL'] ?? null;

        $result->webpLosslessURL = static::handleNaToString($response['WebPLosslessURL']);
        $result->webpLossyURL = static::handleNaToString($response['WebPLossyURL']);
        $result->avifLosslessURL = static::handleNaToString($response['AVIFLosslessURL']);
        $result->avifLossyURL = static::handleNaToString($response['AVIFLossyURL']);

        /**
         * These can be NA or a string with a number in it, in bytes.
         */
        $result->originalSize = static::handleNaToInt($response['OriginalSize']);
        $result->losslessSize = static::handleNaToInt($response['LosslessSize']);
        $result->loselessSize = static::handleNaToInt($response['LoselessSize']);
        $result->lossySize = static::handleNaToInt($response['LossySize']);
        $result->webPLosslessSize = static::handleNaToInt($response['WebPLosslessSize']);
        $result->webPLoselessSize = static::handleNaToInt($response['WebPLoselessSize']);
        $result->webPLossySize = static::handleNaToInt($response['WebPLossySize']);
        $result->avifLosslessSize = static::handleNaToInt($response['AVIFLosslessSize']);
        $result->avifLossySize = static::handleNaToInt($response['AVIFLossySize']);

        // This is not a unix timestamp but a date
        $result->timeStamp = $response['TimeStamp'];
        $result->percentImprovement = $response['PercentImprovement'];

        return $result;
    }

    /**
     *  This is more of a helper, since I'm not sure what to do with these. We want null instead of an NA string in the response.
     *
     * @param string $value
     *
     * @return string|null
     */
    private static function handleNaToString(string $value): ?string
    {
        return $value === 'NA' ? null : $value;
    }

    /**
     * This is more of a helper, since I'm not sure what to do with these. The API returns strings and not integer values,
     *
     * @param $value
     * @return int|null
     */
    private static function handleNaToInt($value): ?int
    {
        return $value === 'NA' ? null : (int)$value;
    }
}
