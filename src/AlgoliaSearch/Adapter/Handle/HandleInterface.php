<?php

namespace AlgoliaSearch\Adapter\Handle;

interface HandleInterface
{
    public function close();

    public function execute();

    public function getResource();
    
    public function isOpen();
}
