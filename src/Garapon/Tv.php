<?php

namespace Garapon;

use Guzzle\Http\Client as HttpClient;

class Tv
{
    protected $apiVersionString;

    /**
     * @var string
     */
    protected $firmwareVersion;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var string
     */
    protected $devId;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * @param string $host
     * @param int $port
     * @param string $apiVersionString
     */
    public function __construct($host, $port = 80, $apiVersionString = 'v3')
    {
        $this->setHostAddressAndPort($host, $port);
        $this->setApiVersionString($apiVersionString);
        $this->httpClient = $this->createHttpClient();
    }

    /**
     * @param string $host
     * @param int $port
     */
    public function setHostAddressAndPort($host, $port = 80)
    {
        $this->host = $host;
        $this->port = $port;
        if ($this->httpClient) { // replace
            $this->httpClient = $this->createHttpClient();
        }
    }

    /**
     * @param $version
     */
    public function setApiVersionString($version)
    {
        $this->apiVersionString = $version;
        if ($this->httpClient) { // replace
            $this->httpClient = $this->createHttpClient();
        }
    }

    /**
     * httpClient Factory
     * @return HttpClient
     */
    public function createHttpClient()
    {
        return new HttpClient(
            sprintf('http://%s:%d/gapi/{version}', $this->host, $this->port),
            array('version' => $this->apiVersionString)
        );
    }

    /**
     * @param int $timestamp
     * @return bool|string
     */
    public static function formatDate($timestamp = null)
    {
        return date('Y-m-d', $timestamp);
    }

    /**
     * @param int $timestamp
     * @return bool|string
     */
    public static function formatDateTime($timestamp = null)
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * @param $loginId
     * @param $password
     * @return int
     */
    public function loginByRawPassword($loginId, $password)
    {
        return $this->login($loginId, md5($password));
    }

    /**
     * @param $login
     * @param $md5pswd
     * @return int
     */
    public function login($login, $md5pswd)
    {
        $response = $this->httpClient->post(
            'auth',
            null,
            array(
                'type'    => 'login',
                'loginid' => $login,
                'md5pswd' => $md5pswd,
            )
        )->send();

        $result = 0;
        if ($response->isSuccessful()) {
            $data = $response->json();
            $status = intval(@$data['status']);
            $result = intval(@$data['login']);
            if ($status == 1 && $result == 1) {
                $this->devId = @$data['dev_id'];
                $this->sessionId = @$data['gtvsession'];
            }
            $this->firmwareVersion = @$data['version'];
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getSessionQueryString()
    {
        $gtvsession = $this->getSessionId();
        $dev_id = $this->getDevId();
        if (!empty($dev_id)) {
            unset($dev_id);
        }

        return http_build_query(
            compact('gtvsession', 'dev_id'),
            null,
            '&'
        );
    }

    /**
     * @return int
     */
    public function logout()
    {
        $response = $this->httpClient->post(
            'auth' . '?' . $this->getSessionQueryString(),
            null,
            array(
                'type' => 'logout',
            )
        )->send();

        $result = 0;
        if ($response->isSuccessful()) {
            $data = $response->json();
            $result = intval(@$data['logout']);
            if ($result == 1) {
                $this->devId = '';
                $this->sessionId = '';
            }
        }
        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    public function search($params = array())
    {
        $response = $this->httpClient->post(
            'search' . '?' . $this->getSessionQueryString(),
            null,
            $params
        )->send();

        $result = false;
        if ($response->isSuccessful()) {
            $result = $response->json();
        }
        return $result;
    }

    /**
     * @param $gtvid
     * @param $rank
     * @return int
     */
    public function favorite($gtvid, $rank)
    {
        $response = $this->httpClient->post(
            'favorite' . '?' . $this->getSessionQueryString(),
            null,
            compact('gtvid', 'rank')
        )->send();

        $status = -1;
        if ($response->isSuccessful()) {
            $data = $response->json();
            $status = intval(@$data['status']);
        }
        return $status;
    }

    /**
     * @return array
     */
    public function channel()
    {
        $response = $this->httpClient->post(
            'channel' . '?' . $this->getSessionQueryString(),
            null,
            array()
        )->send();

        $result = false;
        if ($response->isSuccessful()) {
            $result = $response->json();
        }
        return $result;
    }

    /**
     * @param string $devId
     */
    public function setDevId($devId)
    {
        $this->devId = $devId;
    }

    /**
     * @return string
     */
    public function getDevId()
    {
        return $this->devId;
    }

    /**
     * @return string
     */
    public function getSessionId()
    {
        return $this->sessionId;
    }

    /**
     * @return bool
     */
    public function hasSessionId()
    {
        return !empty($this->sessionId);
    }
}
