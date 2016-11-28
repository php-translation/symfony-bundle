<?php

namespace Translation\Bundle\Tests\Functional\app\Service;

use GuzzleHttp\Psr7\Response;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;

class DummyHttpClient implements HttpClient
{
    public function sendRequest(RequestInterface $request)
    {
        return new Response(200);
    }
}
