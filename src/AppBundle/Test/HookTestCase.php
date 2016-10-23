<?php
/**
 * Created by PhpStorm.
 * User: Jair
 * Date: 21/10/2016
 * Time: 21:48
 */

namespace AppBundle\Test;

use AppBundle\Entity\Destination;
use AppBundle\Entity\Message;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;



class HookTestCase extends WebTestCase
{


    protected function setUp() {
        $this->purgeDatabase();
    }

    public static function setUpBeforeClass()
    {
        self::bootKernel();
    }


    private function purgeDatabase()
    {
        $purger = new ORMPurger($this->getService('doctrine')->getManager());
        $purger->purge();
    }

    protected function getService($id)
    {
        static::bootKernel();

        return static::$kernel->getContainer()
            ->get($id);

    }

    /**
     * @return EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getService('doctrine.orm.entity_manager');
    }

    protected function createDestination($url){
        $destination = new Destination();

        $destination->setUrl($url);

        $em = $this->getEntityManager();
        $em->persist($destination);
        $em->flush();

        return $destination;
    }

    protected function createMessage(Destination $destination, $contentType, $msgBody) {
        $message = new Message();

        $em = $this->getEntityManager();

        $destination =  $em
            ->getRepository('AppBundle:Destination')
            ->find($destination->getId());

        $message->setDestination($destination);
        $message->setContentType($contentType);
        $message->setMsgBody($msgBody);
        $message->setCreatedAt(new \DateTime());


        $em->persist($message);
        $em->flush();

        return $message;
    }


    public function jsonRequest(Client $client, $method, $uri, array $data) {
        $client->request(
            $method,
            $uri,
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode($data));
    }

    public function debugResponse(Client $client) {
        print_r($client->getResponse()->headers);
        print_r($client->getResponse()->getContent());
    }

}