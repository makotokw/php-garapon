<?php

namespace Makotokw\Garapon;

use Makotokw\Garapon\Exception\GaraponException;

class Tv
{
    /**
     * @var string
     */
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
        $this->setHostAddressAndPort($host, $port)
             ->setApiVersionString($apiVersionString);
        $this->httpClient = $this->createHttpClient();
    }

    /**
     * @param string $path
     * @return string
     */
    public function makeUrl($path = '')
    {
        if ($this->port == 80) {
            return sprintf('http://%s%s', $this->host, $path);
        }
        return sprintf('http://%s:%d%s', $this->host, $this->port, $path);
    }

    /**
     * @param string $host
     * @param int $port
     * @return Tv
     */
    public function setHostAddressAndPort($host, $port = 80)
    {
        $this->host = $host;
        $this->port = $port;
        if ($this->httpClient) {
            $this->httpClient = $this->createHttpClient();
        }
        return $this;
    }

    /**
     * @param $version
     * @return Tv
     */
    public function setApiVersionString($version)
    {
        $this->apiVersionString = $version;
        if ($this->httpClient) {
            $this->httpClient = $this->createHttpClient();
        }
        return $this;
    }

    /**
     * httpClient Factory
     * @return HttpClient
     */
    protected function createHttpClient()
    {
        return new HttpClient($this->makeUrl('/gapi/') . $this->apiVersionString . '/');
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
     * @param $gtvid
     * @return string
     */
    public function makeThumbnailUrl($gtvid)
    {
        return $this->makeUrl('/thumbs/' . $gtvid);
    }

    /**
     * @param $gtvid
     * @return string
     */
    public function makeHttpLiveStreamingUrl($gtvid)
    {
        return $this->makeUrl(
            '/cgi-bin/play/m3u8.cgi?' . $gtvid . '-' . $this->httpClient->getSessionId() . '&dev_id=' . $this->getDevId()
        );
    }

    /**
     * @param string $loginId
     * @param string $password
     * @return int
     */
    public function loginByRawPassword($loginId, $password)
    {
        return $this->login($loginId, md5($password));
    }

    /**
     * @param string $login
     * @param string $md5pswd
     * @return int ResultCode
     */
    public function login($login, $md5pswd)
    {
        $resultCode = LoginResultCode::ERROR;
        try {
            $data = $this->httpClient->post(
                'auth',
                [
                    'type' => 'login',
                    'loginid' => $login,
                    'md5pswd' => $md5pswd,
                ]
            );
            $statusCode = intval(@$data['status']);
            if (isset($data['login'])) {
                $resultCode = intval(@$data['login']);
            }
            $this->firmwareVersion = @$data['version'];
        } catch (GaraponException $e) {
        }
        return $resultCode;
    }

    /**
     * @return int LogoutResultCode
     */
    public function logout()
    {
        $resultCode = LogoutResultCode::ERROR;
        try {
            $data = $this->httpClient->post(
                'auth',
                [
                    'type' => 'logout',
                ]
            );
            $statusCode = intval(@$data['status']);
            if (isset($data['logout'])) {
                $resultCode = intval(@$data['logout']);
            }
        } catch (GaraponException $e) {
        }
        return $resultCode;
    }

    /**
     * @param array $params
     * @return false|array
     */
    public function search($params = [])
    {
        $result = false;
        try {
            $result = $this->httpClient->post('search', $params);
        } catch (GaraponException $e) {
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
        $statusCode = StatusCode::ERROR;
        try {
            $data = $this->httpClient->post('favorite', compact('gtvid', 'rank'));
            $statusCode = intval(@$data['status']);
        } catch (GaraponException $e) {
        }
        return $statusCode;
    }

    /**
     * @return array
     */
    public function channel()
    {
        $result = false;
        try {
            $result = $this->httpClient->post('channel', []);
        } catch (GaraponException $e) {
        }
        return $result;
    }

    /**
     * @param string $devId
     * @return Tv
     */
    public function setDevId($devId)
    {
        $this->devId = $devId;
        $this->httpClient->setDevId($devId);
        return $this;
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
        return $this->httpClient->getSessionId();
    }
}
