<?php

namespace WalrusSoup\LaravelShortpixel;

class CompressionConfig
{
    protected const RESIZE_CONTAIN = 3;
    protected const RESIZE_COVER = 1;

    protected const COMPRESSION_LOSSY = 1;
    protected const COMPRESSION_GLOSSY = 2;
    protected const COMPRESSION_LOSSLESS = 0;

    protected int $resizeMethod = 0;
    protected int $resizeWidth = 0;
    protected int $resizeHeight = 0;

    protected bool $lossyMethod = true;
    protected bool $forceRefresh = false;
    protected bool $keepExif = false;

    protected bool $retainOriginalFormat = false;
    protected bool $convertToWebp = false;
    protected bool $convertToJpg = false;
    protected bool $convertToPng = false;
    protected bool $convertToAvif = false;
    protected bool $convertToRgb = true;

    public array $images = [];
    public array $metadata = [];

    public function addImages(array $images): static
    {
        $this->images = array_merge($this->images, $images);

        return $this;
    }

    public function addImage(string $image, callable $callback = null): static
    {
        $this->images[] = $image;

        return $this;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    public function clearImages(): static
    {
        $this->images = [];

        return $this;
    }

    public function resizeToContain(int $width, int $height): static
    {
        $this->resizeMethod = static::RESIZE_CONTAIN;
        $this->resizeWidth = $width;
        $this->resizeHeight = $height;

        return $this;
    }

    public function resizeToCover(int $width, int $height): static
    {
        $this->resizeMethod = static::RESIZE_COVER;
        $this->resizeWidth = $width;
        $this->resizeHeight = $height;

        return $this;
    }

    /**
     * Whether we should force shortpixel to reprocess this image
     *
     * @param bool $forceReprocessing this will tell shortpixel to reprocess the image again, even if the image configuration is the same
     *
     * @return $this
     */
    public function forceReprocessing(bool $forceReprocessing): static
    {
        $this->forceRefresh = $forceReprocessing;

        return $this;
    }

    public function keepExif(bool $keepExif = true): static
    {
        $this->keepExif = $keepExif;

        return $this;
    }

    public function useLossyCompression(): static
    {
        $this->lossyMethod = self::COMPRESSION_LOSSY;

        return $this;
    }

    public function useLosslessCompression(): static
    {
        $this->lossyMethod = self::COMPRESSION_LOSSLESS;

        return $this;
    }

    public function useGlossyCompression(): static
    {
        $this->lossyMethod = self::COMPRESSION_GLOSSY;

        return $this;
    }


    public function convertToJpeg(bool $convertToJpg = true): static
    {
        $this->$convertToJpg = $convertToJpg;

        return $this;
    }

    public function convertToPng(bool $convertToPng = true): static
    {
        $this->convertToPng = $convertToPng;

        return $this;
    }

    public function retainOriginalFormat(bool $retainOriginalFormat = true): static
    {
        $this->retainOriginalFormat = $retainOriginalFormat;

        return $this;
    }

    public function convertToWebp(bool $convertToWebp = true): static
    {
        $this->convertToWebp = $convertToWebp;

        return $this;
    }

    public function convertToAvif(bool $convertToAvif = true): static
    {
        $this->convertToAvif = $convertToAvif;

        return $this;
    }

    public function convertToRgb(bool $convertToRgb = true): static
    {
        $this->convertToRgb = $convertToRgb;

        return $this;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }


    public function convertToFormats(array $formats): static
    {
        foreach($formats as $format) {
            match ($format) {
                'jpg', 'jpeg' => $this->convertToJpeg(),
                'png' => $this->convertToPng(),
                'webp' => $this->convertToWebp(),
                'avif' => $this->convertToAvif(),
            };
        }
        return $this;
    }

    public function getOutputFormats() : array
    {
        $formats = [];
        if($this->convertToJpg) {
            $formats[] = 'jpg';
        }
        if($this->convertToPng) {
            $formats[] = 'png';
        }
        if($this->convertToWebp) {
            $formats[] = $this->retainOriginalFormat ? '+webp' : 'webp';
        }
        if($this->convertToAvif) {
            $formats[] =  $this->retainOriginalFormat ? '+avif' : 'avif';
        }

        return $formats;
    }

    public function getPayload(string $apikey = '', string $pluginVersion = ''): array
    {
        $shortpixelConfiguration = [
            'key' => $apikey,
            'lossy' => $this->lossyMethod,
            'keep_exif' => (int)$this->keepExif,
            'cmyk2rgb' => (int)$this->convertToRgb,
            'resize' => $this->resizeMethod,
            'convertto' => implode('|', $this->getOutputFormats()),
            'urllist' => $this->images,
        ];

        if($this->resizeMethod !== 0) {
            $shortpixelConfiguration['resize_width'] = $this->resizeWidth;
            $shortpixelConfiguration['resize_height'] = $this->resizeHeight;
        }

        if($this->forceRefresh) {
            $shortpixelConfiguration['refresh'] = 1;
        }

        return $shortpixelConfiguration;
    }
}
