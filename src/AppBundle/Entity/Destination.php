<?php
/**
 * Created by PhpStorm.
 * User: Jair
 * Date: 21/10/2016
 * Time: 21:42
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
/**
 * @ORM\Entity
 * @ORM\Table(name="destination")
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "destination_show",
 *          parameters = { "id"= "expr(object.getId())" }
 *      )
 * )
 * @Serializer\ExclusionPolicy("all")
 */
class Destination
{
    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Serializer\Expose()
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please inform a destination URL.")
     * @Assert\Url(message="Please inform a valid URL.")
     * @Serializer\Expose()
     */
    private $url;

    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="destination")
     */
    private $messages;


    public function getId()
    {
        return $this->id;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }


}