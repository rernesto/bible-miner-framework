<?php

namespace App\Tests\Api;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BibleVersionApiTest extends WebTestCase
{
    /**
     * The Guzzle HTTP Client
     *
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    public function setUp()
    {
        $this->httpClient = new \GuzzleHttp\Client(
            ['base_uri' => 'http://localhost:8000']
        );
        parent::setUp();
    }

    public function testApi()
    {
        $this->assertBibleVersionGet();
    }



    public function assertBibleVersionGet()
    {
        $response = $this->httpClient->get(
            'api/bible_versions/5cfb2699db8376755f620e84'
        );

        $bodyResponse = \GuzzleHttp\json_decode($response->getBody(), true);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertArrayHasKey('id', $bodyResponse);
        $this->assertArrayHasKey('shortName', $bodyResponse);
    }
}