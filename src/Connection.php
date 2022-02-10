<?php

namespace zhuzixian520\meilisearch;

use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\helpers\Json;

/**
 * Meilisearch Connection is used to connect to an Meilisearch server version 0.25 or higher
 *
 * @property-read string $baseUrl Base Url
 * @property-read string $driverName Name of the DB driver. This property is read-only.
 *
 * @author Trevor <zhuzixian520@126.com>
 */
class Connection extends Component
{
    /**
     * @event \yii\base\Event an event that is triggered after a DB connection is established
     */
    const EVENT_AFTER_OPEN = 'afterOpen';

    /**
     * @var string the hostname or ip address to use for connecting to the Meilisearch server. Defaults to 'localhost'.
     */
    public $hostname = 'localhost';
    /**
     * @var integer the port to use for connecting to the Meilisearch server. Default port is 7700.
     */
    public $port = 7700;
    /**
     * The keys route allows you to create, manage, and delete API keys
     * @var string|null
     */
    public $apiKey;
    /**
     * @var boolean Send request over SSL protocol. Default state is false.
     * @since 2.0.12
     */
    public $useSSL = false;
    /**
     * @var float|null timeout to use for connecting to an Meilisearch server.
     * This value will be used to configure the curl `CURLOPT_CONNECTTIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $connectionTimeout = null;
    /**
     * @var float|null timeout to use when reading the response from an Meilisearch server.
     * This value will be used to configure the curl `CURLOPT_TIMEOUT` option.
     * If not set, no explicit timeout will be set for curl.
     */
    public $dataTimeout = null;

    /**
     * @var resource the curl instance returned by [curl_init()](http://php.net/manual/en/function.curl-init.php).
     */
    private $_curl;

    /**
     * Closes the connection when this component is being serialized.
     * @return array
     */
    public function __sleep()
    {
        $this->close();

        return array_keys(get_object_vars($this));
    }

    /**
     * Get base Url
     * @return string
     */
    public function getBaseUrl()
    {
        $protocol = $this->useSSL ? 'https' : 'http';
        $host = $this->hostname . ':' .$this->port;

        return "$protocol://$host";
    }

    /**
     * Establishes a DB connection.
     * It does nothing if a DB connection has already been established.
     */
    public function open()
    {
        $this->_curl = curl_init();

        $this->initConnection();
    }

    /**
     * Closes the currently active DB connection.
     * It does nothing if the connection is already closed.
     */
    public function close()
    {
        Yii::trace('Closing connection to Meilisearch. Base url was: ' . $this->baseUrl, __CLASS__);

        if ($this->_curl) {
            curl_close($this->_curl);
            $this->_curl = null;
        }
    }

    /**
     * Initializes the DB connection.
     * This method is invoked right after the DB connection is established.
     * The default implementation triggers an [[EVENT_AFTER_OPEN]] event.
     */
    protected function initConnection()
    {
        $this->trigger(self::EVENT_AFTER_OPEN);
    }

    /**
     * Returns the name of the DB driver for the current [[dsn]].
     * @return string name of the DB driver
     */
    public function getDriverName()
    {
        return 'meilisearch';
    }

    /**
     * Creates a command for execution.
     * @param array $config the configuration for the Command class
     * @return Command the DB command
     */
    public function createCommand($config = [])
    {
        $this->open();
        $config['db'] = $this;
        return new Command($config);
    }

    /**
     * Performs GET HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     */
    public function get($url, $options = [], $body = null, $raw = false)
    {
        $this->open();
        return $this->httpRequest('GET', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs HEAD HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @return mixed response
     * @throws Exception
     */
    public function head($url, $options = [], $body = null)
    {
        $this->open();
        return $this->httpRequest('HEAD', $this->createUrl($url, $options), $body);
    }

    /**
     * Performs POST HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     */
    public function post($url, $options = [], $body = null, $raw = false)
    {
        $this->open();
        return $this->httpRequest('POST', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs PUT HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     */
    public function put($url, $options = [], $body = null, $raw = false)
    {
        $this->open();
        return $this->httpRequest('PUT', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs DELETE HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     */
    public function delete($url, $options = [], $body = null, $raw = false)
    {
        $this->open();
        return $this->httpRequest('DELETE', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Performs PATCH HTTP request
     *
     * @param string|array $url URL
     * @param array $options URL options
     * @param string $body request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed response
     * @throws Exception
     */
    public function patch($url, $options = [], $body = null, $raw = false)
    {
        $this->open();
        return $this->httpRequest('PATCH', $this->createUrl($url, $options), $body, $raw);
    }

    /**
     * Creates URL
     *
     * @param string|array $path path
     * @param array $options URL options
     * @return string
     */
    private function createUrl($path, $options = [])
    {
        if (!is_string($path)) {
            $url = implode('/', array_map(function ($a) {
                return urlencode(is_array($a) ? implode(',', $a) : $a);
            }, $path));
            if (!empty($options)) {
                $url .= '?' . http_build_query($options);
            }
        } else {
            $url = $path;
            if (!empty($options)) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($options);
            }
        }

        return "$this->baseUrl/$url";
    }

    /**
     * Performs HTTP request
     *
     * @param string $method method name
     * @param string $url URL
     * @param string $requestBody request body
     * @param bool $raw if response body contains JSON and should be decoded
     * @return mixed if request failed
     * @throws Exception if request failed
     */
    protected function httpRequest($method, $url, $requestBody = null, $raw = false)
    {
        $method = strtoupper($method);

        // response body and headers
        $headers = [];
        $headersFinished = false;
        $body = '';

        $options = [
            CURLOPT_USERAGENT      => 'Yii Framework ' . Yii::getVersion() . ' ' . __CLASS__,
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_HEADER         => false,
            // http://www.php.net/manual/en/function.curl-setopt.php#82418
            CURLOPT_HTTPHEADER     => [
                'Expect:',
                'Content-Type: application/json',
            ],

            CURLOPT_WRITEFUNCTION  => function ($curl, $data) use (&$body) {
                $body .= $data;
                return mb_strlen($data, '8bit');
            },
            CURLOPT_HEADERFUNCTION => function ($curl, $data) use (&$headers, &$headersFinished) {
                if ($data === '') {
                    $headersFinished = true;
                } elseif ($headersFinished) {
                    $headersFinished = false;
                }
                if (!$headersFinished && ($pos = strpos($data, ':')) !== false) {
                    $headers[strtolower(substr($data, 0, $pos))] = trim(substr($data, $pos + 1));
                }
                return mb_strlen($data, '8bit');
            },
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_FORBID_REUSE   => false,
        ];

        // HTTP Bearer token authentication
        if (!empty($this->apiKey)) {
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BEARER;
            $options[CURLOPT_HTTPHEADER][] = 'Authorization: Bearer ' . $this->apiKey;
        }

        if ($this->connectionTimeout !== null) {
            $options[CURLOPT_CONNECTTIMEOUT] = $this->connectionTimeout;
        }
        if ($this->dataTimeout !== null) {
            $options[CURLOPT_TIMEOUT] = $this->dataTimeout;
        }
        if ($requestBody !== null) {
            $options[CURLOPT_POSTFIELDS] = $requestBody;
        }
        if ($method == 'HEAD') {
            $options[CURLOPT_NOBODY] = true;
            unset($options[CURLOPT_WRITEFUNCTION]);
        } else {
            $options[CURLOPT_NOBODY] = false;
        }

        if (!empty($url)) {
            $profile = "$method $url#$requestBody";
        }else {
            $profile = false;
        }

        Yii::trace("Sending request to Meilisearch server: $method $url\n$requestBody", __METHOD__);
        if ($profile !== false) {
            Yii::beginProfile($profile, __METHOD__);
        }

        $this->resetCurlHandle();
        curl_setopt($this->_curl, CURLOPT_URL, $url);
        curl_setopt_array($this->_curl, $options);
        if (curl_exec($this->_curl) === false) {
            throw new Exception('Meilisearch request failed: ' . curl_errno($this->_curl) . ' - ' . curl_error($this->_curl), [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseHeaders' => $headers,
                'responseBody' => $this->decodeErrorBody($body),
            ]);
        }

        $responseCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);

        if ($profile !== false) {
            Yii::endProfile($profile, __METHOD__);
        }

        if ($responseCode >= 200 && $responseCode < 300) {
            if ($method === 'HEAD') {
                return true;
            } else {
                if (isset($headers['content-length']) && ($len = mb_strlen($body, '8bit')) < $headers['content-length']) {
                    throw new Exception("Incomplete data received from Meilisearch: $len < {$headers['content-length']}", [
                        'requestMethod' => $method,
                        'requestUrl' => $url,
                        'requestBody' => $requestBody,
                        'responseCode' => $responseCode,
                        'responseHeaders' => $headers,
                        'responseBody' => $body,
                    ]);
                }
                if (isset($headers['content-type'])) {
                    if (!strncmp($headers['content-type'], 'application/json', 16)) {
                        return $raw ? $body : Json::decode($body);
                    }
                    if (!strncmp($headers['content-type'], 'text/plain', 10)) {
                        return $raw ? $body : array_filter(explode("\n", $body));
                    }
                }
                throw new Exception('Unsupported data received from Meilisearch: ' . $headers['content-type'], [
                    'requestMethod' => $method,
                    'requestUrl' => $url,
                    'requestBody' => $requestBody,
                    'responseCode' => $responseCode,
                    'responseHeaders' => $headers,
                    'responseBody' => $this->decodeErrorBody($body),
                ]);
            }
        } elseif ($responseCode == 404) {
            return false;
        } else {
            throw new Exception("Meilisearch request failed with code $responseCode. Response body:\n{$body}", [
                'requestMethod' => $method,
                'requestUrl' => $url,
                'requestBody' => $requestBody,
                'responseCode' => $responseCode,
                'responseHeaders' => $headers,
                'responseBody' => $this->decodeErrorBody($body),
            ]);
        }
    }

    private function resetCurlHandle()
    {
        // these functions do not get reset by curl automatically
        static $unsetValues = [
            CURLOPT_HEADERFUNCTION => null,
            CURLOPT_WRITEFUNCTION => null,
            CURLOPT_READFUNCTION => null,
            CURLOPT_PROGRESSFUNCTION => null,
            CURLOPT_POSTFIELDS => null,
        ];
        curl_setopt_array($this->_curl, $unsetValues);
        if (function_exists('curl_reset')) { // since PHP 5.5.0
            curl_reset($this->_curl);
        }
    }

    /**
     * Try to decode error information if it is valid json, return it if not.
     * @param $body
     * @return mixed
     */
    protected function decodeErrorBody($body)
    {
        try {
            $decoded = Json::decode($body);
            if (isset($decoded['error']) && !is_array($decoded['error'])) {
                $decoded['error'] = preg_replace('/\b\w+?Exception\[/', "<span style=\"color: red;\">\\0</span>\n               ", $decoded['error']);
            }
            return $decoded;
        } catch(InvalidArgumentException $e) {
            return $body;
        }
    }
}