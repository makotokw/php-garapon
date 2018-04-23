<?php

namespace Makotokw\Garapon;

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Makotokw\Garapon\Exception\GaraponException;

class HttpClient
{
    /**
     * @var GuzzleHttpClient
     * @see http://docs.guzzlephp.org/en/stable/
     */
    protected $gzlClient;

    /**
     * @var string
     */
    protected $devId;

    /**
     * @var string
     */
    protected $sessionId;

    /**
     * @param string $baseUri
     */
    public function __construct($baseUri)
    {
        $this->gzlClient = new GuzzleHttpClient(
            [
                'base_uri' => $baseUri,
            ]
        );
    }

    /**
     * @return array
     */
    protected function createQuery()
    {
        $query = ['dev_id' => $this->getDevId()];
        if ($sessionId = $this->getSessionId()) {
            $query['gtvsession'] = $sessionId;
        }
        return $query;
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    public function post($url, $params)
    {
        try {
            $response = $this->gzlClient->request(
                'POST',
                $url,
                [
                    'query' => $this->createQuery(),
                    'form_params' => $params,
                ]
            );
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                if (!$data) {
                    throw new GaraponException('can not decode json', GaraponException::ERROR_RESPONSE);
                }
                if (isset($data['status']) && $data['status'] == StatusCode::SUCCESS) {
                    if ($url == 'auth') {
                        $this->sessionId = @$data['gtvsession'];
                    }
                }
                return $data;
            } else {
                throw new GaraponException($response->getReasonPhrase(), GaraponException::ERROR_NETWORK);
            }
        } catch (GuzzleException $e) {
            throw new GaraponException($e->getMessage(), GaraponException::ERROR_NETWORK, $e);
        }
    }

    /**
     * @param string $devId
     * @return HttpClient
     */
    public function setDevId($devId)
    {
        $this->devId = $devId;
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
