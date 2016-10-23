<?php

namespace AppBundle\Controller;


use AppBundle\Entity\Message;
use AppBundle\Form\MessageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class MessageController extends BaseController
{
    /**
     * @Route("/messages")
     * @Method("POST")
     * @ApiDoc(
     *  statusCodes={201="Resource was created"},
     *  responseMap = {
     *     201 = {
     *          "class" = "AppBundle\Entity\Message",
     *     }
     *  },
     *  input={"class"="AppBundle\Form\MessageType", "name"=""},
     *  parameters={
     *      {"name"="destination", "dataType"="integer", "required"=true, "description"="Destination ID."}
     *  },
     *  section="Messages"
     * )
     */
    public function addAction(Request $request) {
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $this->processForm($request, $form);

        if(!$form->isValid()){
            $this->throwProblemValidationException($form);
        }

        $message->setCreatedAt(new \DateTime());

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($message);
        $em->flush();

        $response = $this->createResponse($message, 201);
        $responseUrl = $this->generateUrl(
            'message_show',
            ['id' => $message->getId()]
        );
        $response->headers->set('Location', $responseUrl);

        return $response;

    }

    /**
     * @Route("/messages/{id}", name="message_show")
     * @Method("GET")
     * @ApiDoc(
     *  output="AppBundle\Entity\Message",
     *  section="Messages"
     * )
     */
    public function showAction($id)
    {
        $message = $this->getDoctrine()
            ->getRepository('AppBundle:Message')
            ->find($id);

        if (!$message) {
            throw $this->createNotFoundException(sprintf(
                'No message found with id "%s"',
                $id
            ));
        }

        $response = $this->createResponse($message, 200);

        return $response;
    }

    /**
     * @Route("/messages/{id}")
     * @Method("DELETE")
     * @ApiDoc(
     *  statusCodes={204="Resource deleted"},
     *  section="Messages"
     * )
     */
    public function deleteAction($id)
    {
        $message = $this->getDoctrine()
            ->getRepository('AppBundle:Message')
            ->find($id);

        if ($message) {
            // debated point: should we return 404 on an unknown id?
            // or should we just return a nice 204 in all cases?
            // I choose give 204 in all cases
            $em = $this->getDoctrine()->getManager();
            $em->remove($message);
            $em->flush();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/messages", name="messages_collection")
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  output="array<AppBundle\Entity\Message>",
     *  section="Messages"
     * )
     */
    public function listAction()
    {
        $qb = $this->getDoctrine()
            ->getRepository('AppBundle:Message')
            ->findAll();

        $response = $this->createResponse($qb, 200);

        return $response;
    }
}
