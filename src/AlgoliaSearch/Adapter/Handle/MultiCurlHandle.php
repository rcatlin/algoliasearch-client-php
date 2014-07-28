<?php

namespace AlgoliaSearch\Adapter\Handle;

class MultiCurlHandle implements HandleInterface
{
    protected $mResource = null;
    protected $cResources = array();

    public function __construct()
    {
        $this->init();
    }

    public function __destruct()
    {
        if (!$this->isOpen()) {
            return;
        }

        $this->close();
    }

    public function add(CurlHandle $cHandle)
    {
        $cResource = $cHandle->getResource();

        $int = curl_multi_add_handle($this->mResource, $cResource);

        if ($int === 0) {
            $this->cResources[] = $cResource;
        }

        return $int;
    }

    public function getContent($cResource)
    {
        return curl_multi_getcontent($cResource);
    }

    public function release($cResource)
    {
        curl_multi_remove_handle($this->mResource, $cResource);
    }

    public function releaseAll()
    {
        foreach ($this->cResources as $index => $cResource) {
            unset($this->cResources[$index]);
            $this->release($cResource);
        }
    }

    public function close()
    {
        if (!$this->isOpen()) {
            return;
        }

        curl_multi_close($this->mResource);
        $this->mResource = null;
    }

    public function execute()
    {
        // Verify handles have been added.
        if (empty($this->cResources)) {
            return;
        }

        // Initialize if closed.
        if (!$this->isOpen()) {
            $this->init();
        }

        // Do all the processing.
        $running = null;
        do {
            curl_multi_exec($this->mResource, $running);
            curl_multi_select($this->mResource);
        } while ($running > 0);

        // Release all handles.
        $this->releaseAll();
    }

    public function getResource()
    {
        return $this->mResource;
    }

    public function isOpen()
    {
        return ($this->mResource !== null);
    }

    protected function init()
    {
        $this->mResource = curl_multi_init();
    }
}
