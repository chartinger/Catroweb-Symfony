<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterScenarioTested;

class Scenario implements ScenarioInterface
{

    public $steps;

    public $title;

    public $result;

    public function __construct(AfterScenarioTested $event, $steps)
    {
        $this->steps = $steps;
        $this->title = $event->getScenario()->getTitle(); 
        $this->result = $event->getTestResult()->getResultCode();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function isOutline()
    {
        return false;
    }

    public function getParameters()
    {
        return array();
    }

    public function getExamples()
    {
        return array();
    }
}