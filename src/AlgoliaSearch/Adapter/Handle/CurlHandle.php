<?php

namespace AlgoliaSearch\Adapter\Handle;

use AlgoliaSearch\ClientContext;

class CurlHandle implements HandleInterface
{
    protected $cResource = null;

    public function __construct(ClientContext $context, $method, $url, $data)
    {
        $this->init();
        $this->setupResource($context, $method, $url, $data);
    }

    public function __destruct()
    {
        if (!$this->isOpen()) {
            return;
        }

        $this->close();
    }

    public function close()
    {
        if (!$this->isOpen()) {
            return;
        }

        curl_close($this->cResource);
        $this->cResource = null;
    }

    public function execute()
    {
        return curl_exec($this->cResource);
    }

    public function getResource()
    {
        return $this->cResource;
    }

    public function getHttpStatus()
    {
        return (int) $this->getInfo(CURLINFO_HTTP_CODE);
    }

    public function getInfo($opt = 0)
    {
        return curl_getinfo($this->cResource, $opt);
    }

    public function getError()
    {
        return curl_error($this->cResource);
    }

    public function isOpen()
    {
        return ($this->cResource !== null);
    }

    protected function init()
    {
        $this->cResource = curl_init();
    }

    protected function setupResource(ClientContext $context, $method, $url, $data)
    {
        //curl_setopt($curlHandle, CURLOPT_VERBOSE, true);
        if ($context->adminAPIKey == null) {
            curl_setopt($this->cResource, CURLOPT_HTTPHEADER, array(
                'X-Algolia-Application-Id: ' . $context->applicationID,
                'X-Algolia-API-Key: ' . $context->apiKey,
                'Content-type: application/json'
            ));
        } else {
            curl_setopt($this->cResource, CURLOPT_HTTPHEADER, array(
                'X-Algolia-Application-Id: ' . $context->applicationID,
                'X-Algolia-API-Key: ' . $context->adminAPIKey,
                'X-Forwarded-For: ' . $context->endUserIP,
                'X-Forwarded-API-Key: ' . $context->rateLimitAPIKey,
                'Content-type: application/json'
            ));
        }
        curl_setopt($this->cResource, CURLOPT_USERAGENT, "Algolia for PHP 1.2.2");
        //Return the output instead of printing it
        curl_setopt($this->cResource, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->cResource, CURLOPT_FAILONERROR, true);
        curl_setopt($this->cResource, CURLOPT_ENCODING, '');
        curl_setopt($this->cResource, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->cResource, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->cResource, CURLOPT_CAINFO, __DIR__ . '../../../resources/ca-bundle.crt');
        
        curl_setopt($this->cResource, CURLOPT_URL, $url);
        curl_setopt($this->cResource, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->cResource, CURLOPT_NOSIGNAL, 1); # The problem is that on (Li|U)nix, when libcurl uses the standard name resolver, a SIGALRM is raised during name resolution which libcurl thinks is the timeout alarm.
        curl_setopt($this->cResource, CURLOPT_FAILONERROR, false);

        if ($method === 'GET') {
            curl_setopt($this->cResource, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($this->cResource, CURLOPT_HTTPGET, true);
            curl_setopt($this->cResource, CURLOPT_POST, false);
        } else if ($method === 'POST') {
            $body = ($data) ? json_encode($data) : '';
            curl_setopt($this->cResource, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->cResource, CURLOPT_POST, true);
            curl_setopt($this->cResource, CURLOPT_POSTFIELDS, $body);
        } elseif ($method === 'DELETE') {
            curl_setopt($this->cResource, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($this->cResource, CURLOPT_POST, false);
        } elseif ($method === 'PUT') {
            $body = ($data) ? json_encode($data) : '';
            curl_setopt($this->cResource, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($this->cResource, CURLOPT_POSTFIELDS, $body);
            curl_setopt($this->cResource, CURLOPT_POST, true);
        }
    }
}
