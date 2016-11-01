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

        return $this->_doRequest('/slugs', $requestBody);
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
            $requestBody = array_merge($requestBody, ['parentComment' => $parentCommentId]);
        }

        return $this->_doRequest('/comments', $requestBody);
    }

    /**
     * Submits a Form to the API. Please submit all form elements to this function in a multidimensional array; for
     * instance. The $formData cannot contain data that isn't part of the form.
     *
     * @param int   $formId      - Id of the Form object                           ($block.form.id)
     * @param int   $formBlockId - The id of the form block within the Page / Post ($block.id)
     * @param array $formData    - Data actually submitted
     *
     * @return array|string|null
     */
    public function submitForm($formId, $formBlockId, array $formData)
    {
        // must have an id
        if (empty($formId)) {
            throw new \InvalidArgumentException('Form id cannot be empty');
        }

        if (empty($formBlockId)) {
            throw new \InvalidArgumentException('Form block id cannot be empty');
        }

        // must have data!
        if (empty($formData)) {
            throw new \InvalidArgumentException('No form data to submit');
        }

        // compose the form data array
        $requestData = array_merge($formData, [
            'formId' => $formId,
            'formBlockId' => $formBlockId
        ]);

        // execute and return the request
        return $this->_doRequest('/forms', $requestData);
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
     * @return array|string|null
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
            echo 'Request body: ' . "\n";
            print_r($requestBody);
            echo "\n";
        }

        // init response & returnMessage variable
        $response = null;
        $returnMessage = null;

        try {
            $client = new Client(['base_uri' => $this->getApiUrl()]);
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
            $returnMessage = json_decode((string) $response->getBody(), true);
            $returnMessage['code'] = $response->getStatusCode();
        } catch (ClientException $clientException) {
            $returnMessage = json_decode((string) $clientException->getResponse()->getBody(), true);
            return $returnMessage;
        } catch (ServerException $serverException) {
            $returnMessage = json_decode((string) $serverException->getResponse()->getBody(), true);
            return $returnMessage;
        }

        if ($this->debug) {
            echo '</pre>';
        }

        return $returnMessage;
    }
}