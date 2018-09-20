<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

class Example
{

    public $result;

    public $parameters;

    public $steps;

    public function __construct($result, $parameters, $steps)
    {
        $this->result = $result;
        $this->parameters = $parameters;
        $this->steps = $steps;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getSteps()
    {
        return $this->steps;
    }
}