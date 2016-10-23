<?php

namespace AppBundle\EventListener;

use AppBundle\Problem\Problem;
use AppBundle\Problem\ProblemException;
use AppBundle\Problem\ResponseFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $debug;

    private $responseFactory;

    private $logger;

    public function __construct($debug, ResponseFactory $responseFactory, LoggerInterface $logger)
    {
        $this->debug = $debug;
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $e = $event->getException();

        $statusCode = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        // allow 500 errors to be thrown
        if ($this->debug && $statusCode >= 500) {
            return;
        }

        $this->logException($e);

        if ($e instanceof ProblemException) {
            $problem = $e->getProblem();
        } else {


            $problem = new Problem(
                $statusCode
            );

            /*
             * If it's an HttpException message (e.g. for 404, 403),
             * we'll say as a rule that the exception message is safe
             * for the client. Otherwise, it could be some sensitive
             * low-level exception, which should *not* be exposed
             */
            if ($e instanceof HttpExceptionInterface) {
                $problem->set('detail', $e->getMessage());
            }
        }

        $response = $this->responseFactory->createResponse($problem);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => 'onKernelException'
        );
    }

    /**
     * Adapted from the core Symfony exception handling in ExceptionListener
     *
     * @param \Exception $exception
     */
    private function logException(\Exception $exception)
    {
        $message = sprintf('Uncaught PHP Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine());
        $isCritical = !$exception instanceof HttpExceptionInterface || $exception->getStatusCode() >= 500;
        $context = array('exception' => $exception);
        if ($isCritical) {
            $this->logger->critical($message, $context);
        } else {
            $this->logger->error($message, $context);
        }
    }
}
