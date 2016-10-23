<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Test\HookTestCase;



class DestinationControllerTest extends HookTestCase
{
    private $urlTest = 'http://requestb.in/zfdcbdzf';
    private $debugResponse = false;

    public function testPOSTDestinationAdd()
    {
        $data = array(
            'url' => $this->urlTest,
        );

        $client = static::createClient();

        $this->jsonRequest(
            $client,
            'POST',
            '/destinations',
            $data);

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        $this->assertArrayHasKey('id', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['url'], $data['url']);
    }

    public function testPOSTDestinationInvalidURL()
    {
        $data = array(
            'url' => 'abc',
        );

        $client = static::createClient();

        $this->jsonRequest(
            $client,
            'POST',
            '/destinations',
            $data);

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
        $this->assertTrue(
            $client->getResponse()->headers->contains(
                'Content-Type',
                'application/problem+json'
            )
        );

        $this->assertArrayHasKey('errors', json_decode((string)$client->getResponse()->getContent(), true));
    }

    public function testPOSTDestinationInvalidJson() {

        $client = static::createClient();

        //Request without or with invalid json
        $client->request(
            'POST',
            '/destinations');


        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    public function testGETDestination() {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);


        $client->request(
            'GET',
            '/destinations/' . $destination->getId()
        );


        if ($this->debugResponse) {
            $this->debugResponse($client);
        }



        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('id', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['url'], $this->urlTest);

    }

    public function testDestination404Exception() {
        $client = static::createClient();

        $client->request(
            'GET',
            '/destinations/0'
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertArrayHasKey('status', json_decode((string)$client->getResponse()->getContent(), true));
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['type'], 'about:blank');
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['title'], 'Not Found');

    }

    public function testDELETEDestination() {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);

        $client->request(
            'DELETE',
            '/destinations/' . $destination->getId()
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }

    public function testGETDestinationsCollection()
    {
        $client = static::createClient();

        for ($i = 1; $i <= 5; $i++) {
            $this->createDestination($this->urlTest . $i);
        }

        $client->request(
            'GET',
            '/destinations'
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)[0]['url'], $this->urlTest . '1');
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)[4]['url'], $this->urlTest . '5');
    }

    public function testPUTDestination()
    {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);

        $data = array(
            'url' => 'http://newurl.com',
        );

        $this->jsonRequest(
            $client,
            'PUT',
            '/destinations/' . $destination->getId(),
            $data
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['url'], $data['url']);
        // the ID is immutable on edits
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['id'], $destination->getId());
    }

    public function testPATCHDestination()
    {
        $client = static::createClient();

        $destination = $this->createDestination($this->urlTest);

        $data = array(
            'url' => 'http://newurl.com',
        );

        $this->jsonRequest(
            $client,
            'PATCH',
            '/destinations/' . $destination->getId(),
            $data
        );

        if ($this->debugResponse) {
            $this->debugResponse($client);
        }

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['url'], $data['url']);
        // the ID is immutable on edits
        $this->assertEquals(json_decode((string)$client->getResponse()->getContent(), true)['id'], $destination->getId());
    }

}
