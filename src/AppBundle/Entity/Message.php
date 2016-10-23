<?php
/**
 * Created by PhpStorm.
 * User: Jair
 * Date: 22/10/2016
 * Time: 01:08
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @ORM\Entity
 * @ORM\Table(name="message")
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "message_show",
 *          parameters = { "id"= "expr(object.getId())" }
 *      )
 * )
 */
class Message
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Destination", inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     */
    private $destination;

    /**
     * @ORM\Column(type="string")
     */
    private $contentType;

    /**
     * @ORM\Column(type="text")
     */
    private $msgBody;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Destination
     */
    public function getDestination()
    {
        return $this->destination;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function getContentType()
    {
        return $this->contentType;
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function getMsgBody()
    {
        return $this->msgBody;
    }

    public function setMsgBody($msgBody)
    {
        $this->msgBody = $msgBody;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }


}