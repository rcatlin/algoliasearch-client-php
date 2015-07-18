<?php

namespace AlgoliaSearch\Adapter;

use AlgoliaSearch\ClientContext;
use AlgoliaSearch\Adapter\Handle\CurlHandle;
use AlgoliaSearch\Adapter\Handle\MultiCurlHandle;

class MultiCurlAdapter extends AbstractAdapter
{
    protected $mHandle;

    public function __construct()
    {
        $this->mHandle = new MultiCurlHandle();
    }

    public function __destruct() {
        if ($this->mHandle === null) {
            return;
        }

        $this->close();
    }

    public function close()
    {
        if ($this->mHandle === null) {
            return;
        }

        $this->mHandle->close();
        $this->mHandle = null;
    }

    public function doRequest(ClientContext $context, $method, $host, $path, $params, $data)
    {
        $url = $this->buildUrl($host, $path, $params);

        // Create Curl Handle
        $cHandle = new CurlHandle($context, $method, $url, $data);

        // Add Handle to MultiHandle
        $this->mHandle->add($cHandle);

        // Execute
        $this->mHandle->execute();

        // Get Http Status
        $httpStatus = $cHandle->getHttpStatus();

        // Get Response
        $response = $this->mHandle->getContent($cHandle->getResource());

        // Check for Errors
        $error = $cHandle->getError();
        if (!empty($error)) {
            throw new \Exception($error);
        }

        // Check Http Status
        if ($httpStatus === 0 || $httpStatus === 503) {
            // Could not reach host or service unavailable, try with another one if we have it
            return null;
        }

        // Decode Response
        $answer = json_decode($response, true);

        // Release/close Handle from MultiHandle
        $this->mHandle->release($cHandle->getResource());
        
        // Evaluate Http Status
        $this->evaluateHttpStatus($httpStatus);

        // Evaluate Json for Errors
        $this->evaluateJsonLastError(json_last_error());

        return $answer;
    }
}
