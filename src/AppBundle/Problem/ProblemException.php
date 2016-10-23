<?php

namespace AppBundle\Problem;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ProblemException extends HttpException
{
    private $problem;

    public function __construct(Problem $problem, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->problem = $problem;
        $statusCode = $problem->getStatusCode();
        $message = $problem->getTitle();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }

    public function getProblem()
    {
        return $this->problem;
    }
}
