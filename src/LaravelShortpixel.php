<?php

namespace WalrusSoup\LaravelShortpixel;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaravelShortpixel
{
    protected string $logChannel = 'default';

    protected string $apiKey = '';

    protected string $pluginVersion = '';

    /**
     * Use to set a compression log channel, which will be used to log compression events.
     *
     * @param  string  $logChannel
     * @return void
     */
    public function setLogChannel(string $logChannel): void
    {
        $this->logChannel = $logChannel;
    }

    /**
     * This is your API key. You can find it in your Shortpixel account.
     *
     * @param  string  $apikey
     * @return void
     */
    public function setApiKey(string $apikey): void
    {
        $this->apiKey = $apikey;
    }

    /**
     * This is required, maximum of 5 characters. Trimming it for sanity.
     *
     * @param  string  $pluginVersion
     * @return void
     */
    public function setPluginVersion(string $pluginVersion): void
    {
        $this->pluginVersion = Str::substr($pluginVersion, 0, 5);
    }

    /**
     * This will call shortpixel and simply return the results. Call this later at your leisure with the same configuration to get results.
     *
     * @param  CompressionConfig  $compressionConfig
     * @return array
     */
    public function callShortPixel(CompressionConfig $compressionConfig): array
    {
        return $this->handleShortpixelResponse($this->makeHttpCall($compressionConfig->getPayload()));
    }

    /**
     * Call shortpixel and let this class wait for a response. This is intended to run within a job, not a regular http request.
     *
     * @param  CompressionConfig  $compressionConfig
     * @param  int  $maximumAttempts
     * @param  int  $sleepFor
     * @return array
     */
    public function callShortPixelAndWait(CompressionConfig $compressionConfig, int $maximumAttempts = 10, int $sleepFor = 10): array
    {
        $currentAttempt = 0;
        $compressionPayload = $compressionConfig->getPayload($this->apiKey, $this->pluginVersion);
        Log::channel($this->logChannel)->info('Calling ShortPixel API With Configuration: '.json_encode(array_merge(['apikey' => ''], $compressionPayload)));

        while ($currentAttempt < $maximumAttempts) {
            $currentAttempt++;
            $shortpixelResponse = $this->makeHttpCall($compressionPayload);
            $compressionResults = $this->handleShortpixelResponse($shortpixelResponse);

            foreach ($compressionResults as $compressionResult) {
                if ($compressionResult->isProcessing()) {
                    Log::channel($this->logChannel)->info('Not all images ready, sleeping for '.$sleepFor.' seconds');

                    continue 2;
                }
            }
            Log::channel($this->logChannel)->info('All images ready, returning results');

            return $compressionResults;
        }
        // This would be an odd case. What happened?
        return [];
    }

    /**
     * Converts all shortpixel responses into a collection of ShortpixelResponse objects.
     *
     * @param  array  $compressionResponse
     * @return array
     */
    public function handleShortpixelResponse(array $compressionResponse): array
    {
        $compressionResults = [];

        foreach ($compressionResponse as $imageResponse) {
            $compressionResult = ShortpixelCompressionResult::createFromResponse($imageResponse);
            $compressionResults[] = $compressionResult;
        }

        return $compressionResults;
    }

    /**
     * Performs the HTTP call using laravels built-in http client.
     *
     * @param  array  $payload
     * @return array|mixed
     */
    protected function makeHttpCall(array $payload)
    {
        return Http::retry(3, 1000)->post('https://api.shortpixel.com/v2/reducer.php', $payload)->json();
    }

    /**
     * Creates an instance of this class with api key, plugin, and log channel set
     *
     * @param  array  $config
     * @return static
     */
    public static function createFromConfig(array $config): static
    {
        $shortpixel = new self();
        $shortpixel->setApiKey($config['api_key']);
        $shortpixel->setPluginVersion($config['plugin_version']);
        $shortpixel->setLogChannel($config['log_channel']);

        return $shortpixel;
    }
}
