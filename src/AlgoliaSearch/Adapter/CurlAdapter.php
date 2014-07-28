<?php

namespace AlgoliaSearch\Adapter;

use AlgoliaSearch\ClientContext;
use AlgoliaSearch\Adapter\Handle\CurlHandle;

class CurlAdapter extends AbstractAdapter
{
    public function doRequest(ClientContext $context, $method, $host, $path, $params, $data)
    {
        $url = $this->buildUrl($host, $path, $params);        
        
        // Create Curl Handle
        $cHandle = new CurlHandle($context, $method, $url, $data);

        // Execute and get Response
        $response = $cHandle->execute();

        // Get Http Status
        $httpStatus = $cHandle->getHttpStatus();

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

        // Close Handle
        $cHandle->close();
        
        // Evaluate Http Status
        $this->evaluateHttpStatus($httpStatus);

        // Evaluate Json for Errors
        $this->evaluateJsonLastError(json_last_error());

        return $answer;
    }
}
