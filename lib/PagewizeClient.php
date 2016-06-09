<?php
namespace PagewizeClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

/**
 * Class Connector
 *
 * Enables communication with a Pagewize API. Can fetch complete pages or posts. Just anything that has a slug.
 *
 * @license LICENSE.md
 *
 * @package PagewizeConnect
 */
class PagewizeClient
{
    /**
     * @var bool $debug - Enable debug options
     */
    protected $debug = false;

    /**
     * @var string $apiKey - What Api key to use for the request
     */
    protected $apiKey;

    /**
     * @var string $apiUrl - Where the Pagewize API connector is connecting to
     */
    protected $apiUrl = 'api.pagewize.com';

    /**
     * @var string $connectorName - The name of this connector
     */
    protected $connectorName = 'Pagewize Connect';

    /**
     * @var string $connectorVersion - Version of the connector
     */
    protected $connectorVersion = '1.0';

    /**
     * Connector constructor.
     *
     * @param string $apiKey   - Api key
     * @param bool   $debug    - Output how the request is made
     * @param string $apiUrl   - Where is the API hosted
     * @param string $protocol - What protocol to enforce (http/https)
     */
    public function __construct($apiKey, $debug = false, $apiUrl = 'api.pagewize.com', $protocol = 'https')
    {
        if (empty($apiKey)) {
            throw new \InvalidArgumentException($apiKey . ' cannot be empty');
        }

        if (!in_array($protocol, ['http', 'https'])) {
            throw new \InvalidArgumentException($protocol . ' is not a valid protocol, please use http/https');
        }

        $this->apiKey = $apiKey;
        $this->debug = $debug;
        $this->apiUrl = $protocol . '://' . $apiUrl;
    }

    /**
     * Requests content!
     *
     * @param string $slug     - The slug the content has
     * @param string $language - ISO xxx representation of any language
     * @return array
     */
    public function fetchContent($slug = '/', $language = null)
    {
        if (!is_null($language) && strlen($language) > 2) {
            throw new \InvalidArgumentException($language . ' is not a valid ISO xxx value.');
        }

        return $this->_doRequest($slug, $language);
    }

    /**
     * Enables debug mode
     */
    public function enableDebugMode()
    {
        $this->debug = true;
    }

    /**
     * Disables debug mode
     */
    public function disableDebugMode()
    {
        $this->debug = false;
    }

    /**
     * Returns the Api url
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Will do the actual request
     *
     * @param string $slug     - What is the requested slug
     * @param string $language - In what language the content is requested in ISO xxx format
     * @return array
     */
    private function _doRequest($slug, $language = null)
    {
        if ($this->debug) {
            echo '<pre>';
        }

        if (!is_string($slug)) {
            throw new \InvalidArgumentException($slug . ' is not a valid slug value');
        }

        // echo endpoint
        if ($this->debug) {
            echo 'Url: ' . $this->getApiUrl() . "\n";
        }

        // define the request body
        $requestBody = ['slug' => $slug];

        if (!is_null($language)) {
            $requestBody = array_merge($requestBody, ['language' => $language]);
        }

        // json encode the body
        $requestBody = json_encode($requestBody);

        // echo payload
        if ($this->debug) {
            echo '<pre>';
            print_r($requestBody);
            echo '</pre>';
            echo "\n";
        }

        // init response variable
        $response = null;

        try {
            $client = new Client();
            $response = $client->post($this->getApiUrl(), [
                'timeout' => '20',
                'headers' => [
                    'User-Agent' => $this->connectorName . ' ' . $this->connectorVersion,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'X-Apikey' => $this->apiKey
                ],
                'body' => $requestBody,
                'debug' => $this->debug
            ]);

            // raw result
            if ($this->debug) {
                echo 'Status code: ' . $response->getStatusCode() . "\n";
                echo 'Raw result: ' . "\n";
                print_r(json_decode((string) $response->getBody(), JSON_PRETTY_PRINT));
            }

            // return as json object
            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $clientException) {
            echo 'Setup is incorrect.  Please debug using the following message:' . "\n" . $clientException->getMessage() . "\n";
        } catch (ServerException $serverException) {
            echo 'Something is wrong with the frontend-server. You can retry you\'re request but if it is consistent please submit a bug report.' . "\n" . $serverException->getMessage() . "\n";
        }

        if ($this->debug) {
            echo '<pre>';
        }

        return null;
    }
}