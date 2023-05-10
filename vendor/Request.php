<?php

namespace tplLib;
class Request
{

    private array $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function param($key)
    {
        return $this->request[$key] ?? '';
    }

    public function __toString()
    {
        return sprintf('<pre>%s</pre>', print_r($this->request, true));
    }

}