<?php
namespace PagewizeClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
/**
 * Class Connector
 *
 * Enables communication with the Pagewize API. This class enables the (theme) developer to;
 *
 * 1. Fetch a page, post or post category by their slug
 * 2. Add a Comment by using the Pagewize Comment Api
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
    protected $connectorVersion = 'alpha';

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

        if (!is_string($slug)) {
            throw new \InvalidArgumentException($slug . ' is not a valid slug value');
        }

        // define the request body
        $requestBody = ['slug' => $slug];

        if (!is_null($language)) {
            $requestBody = array_merge($requestBody, ['language' => $language]);
        }

        return $this->_doRequest($this->getApiUrl() . '/slugs', $requestBody);
    }

    /**
     * Places a new Comment under the given $postId. When the Comment is a reply to define the $parentCommentId variable
     * with the id of the Comment you are replying too
     *
     * @param string   $name
     * @param string   $email
     * @param string   $comment
     * @param null|int $postId
     * @param null|int $parentCommentId
     * @return array
     */
    public function addComment($name, $email, $comment, $postId = null, $parentCommentId = null)
    {
        if (empty($name) || empty($email) || empty($comment)) {
            throw new \InvalidArgumentException('One of the required variables is not set');
        }

        $requestBody = [
            'name' => $name,
            'email' => $email,
            'comment' => $comment
        ];

        if (!is_null($postId)) {
            $requestBody = array_merge($requestBody, ['postId' => $postId]);
        }

        if (!is_null($parentCommentId)) {
            $requestBody = array_merge($requestBody, ['parentComment' => $postId]);
        }

        return $this->_doRequest($this->getApiUrl() . ' /comments', $requestBody);
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
     * @param string $apiEndPoint - What endpoint for the request body
     * @param array  $requestBody - Data to POST to the Pagewize Api
     * @return array
     */
    private function _doRequest($apiEndPoint, array $requestBody)
    {
        if (empty($apiEndPoint)) {
            throw new \InvalidArgumentException('$apiEndPoint has to be set');
        }

        if ($this->debug) {
            echo '<pre>';
        }

        // echo endpoint
        if ($this->debug) {
            echo 'Url: ' . $apiEndPoint . "\n";
        }

        // json encode the body
        $requestBody = json_encode($requestBody);

        // echo payload
        if ($this->debug) {
            print_r($requestBody);
            echo "\n";
        }

        // init response variable
        $response = null;

        try {
            $client = new Client();
            $response = $client->post(
                $apiEndPoint, [
                    'timeout' => '20',
                    'headers' => [
                        'User-Agent' => $this->connectorName . ' ' . $this->connectorVersion,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'X-Apikey' => $this->apiKey
                    ],
                    'body' => $requestBody,
                    'debug' => $this->debug
                ]
            );

            // raw result
            if ($this->debug) {
                echo 'Status code: ' . $response->getStatusCode() . "\n";
                echo 'Raw result: ' . "\n";
                print_r(json_decode((string) $response->getBody(), JSON_PRETTY_PRINT));
            }

            // return as json object
            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $clientException) {
            echo 'Setup is incorrect. Please debug using the following message:' . "\n" . $clientException->getMessage() . "\n";
        } catch (ServerException $serverException) {
            echo 'Something is wrong with the frontend-server. You can resend the request, but if it is consistent please submit a bug report.' . "\n" . $serverException->getMessage() . "\n";
        }

        if ($this->debug) {
            echo '</pre>';
        }

        return null;
    }
}