<?php

namespace AppBundle\Controller;

use AppBundle\Problem\Problem;
use AppBundle\Problem\ProblemException;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormInterface;

class BaseController extends Controller
{
    protected function createResponse($data, $statusCode = 200)
    {
        $json = $this->serialize($data);

        return new Response($json, $statusCode, array(
            'Content-Type' => 'application/json'
        ));
    }

    protected function serialize($data, $format = 'json')
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $groups = array('Default');
        $context->setGroups($groups);

        return $this->container->get('jms_serializer')
            ->serialize($data, $format, $context);
    }

    protected function processForm(Request $request, FormInterface $form)
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            $problem = new Problem(400, Problem::TYPE_INVALID_REQUEST_BODY_FORMAT);

            throw new ProblemException($problem);
        }

        $clearMissing = $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

    protected function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    protected function throwProblemValidationException(FormInterface $form)
    {
        $errors = $this->getErrorsFromForm($form);

        $problem = new Problem(
            400,
            Problem::TYPE_VALIDATION_ERROR
        );
        $problem->set('errors', $errors);

        throw new ProblemException($problem);
    }
}
