<?php

namespace AlgoliaSearch\Adapter;

use AlgoliaSearch\AlgoliaException;
use AlgoliaSearch\Client;
use AlgoliaSearch\ClientContext;

abstract class AbstractAdapter 
{
    abstract public function doRequest(ClientContext $context, $method, $host, $path, $params, $data);

    public function close() {
        return;
    }

    protected function buildUrl($host, $path, $params) 
    {
        if (strpos($host, "http") === 0) {
            $url = $host . $path;
        } else {
            $url = "https://" . $host . $path;
        }
        if ($params != null && count($params) > 0) {
            $params2 = array();
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    $params2[$key] = json_encode($val);
                } else {
                    $params2[$key] = $val;
                }
            }
            $url .= "?" . http_build_query($params2);
            
        }

        return $url;
    }

    protected function evaluateHttpStatus($httpStatus)
    {
        if ($httpStatus == 400) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Bad request");
        }
        elseif ($httpStatus === 403) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Invalid Application-ID or API-Key");
        }
        elseif ($httpStatus === 404) {
            throw new AlgoliaException(isset($answer['message']) ? $answer['message'] : "Resource does not exist");
        }
        elseif ($httpStatus != 200 && $httpStatus != 201) {
            throw new Exception($httpStatus . ": " . $response);
        }   
    }

    protected function evaluateJsonLastError($jsonLastError)
    {
        switch ($jsonLastError) {
            case JSON_ERROR_DEPTH:
                $errorMsg = 'JSON parsing error: maximum stack depth exceeded';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $errorMsg = 'JSON parsing error: unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $errorMsg = 'JSON parsing error: syntax error, malformed JSON';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $errorMsg = 'JSON parsing error: underflow or the modes mismatch';
                break;
            case (defined('JSON_ERROR_UTF8') ? JSON_ERROR_UTF8 : -1): // PHP 5.3 less than 1.2.2 (Ubuntu 10.04 LTS)
                $errorMsg = 'JSON parsing error: malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            case JSON_ERROR_NONE:
            default:
                $errorMsg = null;
                break;
        }
        
        if ($errorMsg !== null) 
            throw new AlgoliaException($errorMsg);
    }
}
