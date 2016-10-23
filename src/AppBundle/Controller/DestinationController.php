<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Destination;
use AppBundle\Form\DestinationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class DestinationController extends BaseController
{
    /**
     * @Route("/destinations")
     * @Method("POST")
     * @ApiDoc(
     *  statusCodes={201="Resource was created"},
     *  responseMap = {
     *     201 = {
     *          "class" = "AppBundle\Entity\Destination",
     *     }
     *  },
     *  input={"class"="AppBundle\Form\DestinationType", "name"=""},
     *  section="Destinations"
     * )
     */
    public function addAction(Request $request) {
        $destination = new Destination();
        $form = $this->createForm(DestinationType::class, $destination);
        $this->processForm($request, $form);

        if(!$form->isValid()){
            $this->throwProblemValidationException($form);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $em->persist($destination);
        $em->flush();

        $response = $this->createResponse($destination, 201);
        $responseUrl = $this->generateUrl(
            'destination_show',
            ['id' => $destination->getId()]
        );
        $response->headers->set('Location', $responseUrl);

        return $response;

    }

    /**
     * @Route("/destinations/{id}", name="destination_show")
     * @Method("GET")
     * @ApiDoc(
     *  output="AppBundle\Entity\Destination",
     *  section="Destinations"
     * )
     */
    public function showAction($id)
    {
        $destination = $this->getDoctrine()
            ->getRepository('AppBundle:Destination')
            ->find($id);

        if (!$destination) {
            throw $this->createNotFoundException(sprintf(
                'No destination found with id "%s"',
                $id
            ));
        }

        $response = $this->createResponse($destination, 200);

        return $response;
    }

    /**
     * @Route("/destinations/{id}")
     * @Method("DELETE")
     * @ApiDoc(
     *  statusCodes={204="Resource deleted"},
     *  section="Destinations"
     *
     * )
     */
    public function deleteAction($id)
    {
        $destination = $this->getDoctrine()
            ->getRepository('AppBundle:Destination')
            ->find($id);

        if ($destination) {
            // debated point: should we return 404 on an unknown id?
            // or should we just return a nice 204 in all cases?
            // I choose give 204 in all cases
            $em = $this->getDoctrine()->getManager();
            $em->remove($destination);
            $em->flush();
        }

        return new Response(null, 204);
    }

    /**
     * @Route("/destinations", name="destinations_collection")
     * @Method("GET")
     * @ApiDoc(
     *  resource=true,
     *  output="array<AppBundle\Entity\Destination>",
     *  section="Destinations"
     * )
     */
    public function listAction()
    {
        $qb = $this->getDoctrine()
            ->getRepository('AppBundle:Destination')
            ->findAll();

         $response = $this->createResponse($qb, 200);

        return $response;
    }

    /**
     * @Route("/destinations/{id}")
     * @Method({"PUT", "PATCH"})
     * @ApiDoc(
     *  input={"class"="AppBundle\Form\DestinationType", "name"=""},
     *  output="AppBundle\Entity\Destination",
     *  section="Destinations"
     * )
     */
    public function updateAction($id, Request $request)
    {
        $destination = $this->getDoctrine()
            ->getRepository('AppBundle:Destination')
            ->find($id);

        if (!$destination) {
            throw $this->createNotFoundException(sprintf(
                'No destination found with id "%s"',
                $id
            ));
        }

        $form = $this->createForm(DestinationType::class, $destination);
        $this->processForm($request, $form);

        if (!$form->isValid()) {
            $this->throwProblemValidationException($form);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($destination);
        $em->flush();

        $response = $this->createResponse($destination, 200);

        return $response;
    }
}
