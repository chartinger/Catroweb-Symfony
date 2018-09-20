<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterBackgroundTested;

class Background
{

    public $steps;

    public $title;

    public $result;

    public function __construct(AfterBackgroundTested $event, $steps)
    {
        $num_steps = count($event->getBackground()->getSteps());
        $this->steps = array_slice($steps, - $num_steps);
        $this->title = $event->getBackground()->getTitle();
        $this->result = $event->getTestResult()->getResultCode();
    }

    public function getTitle()
    {
        $this->title;
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function getResult()
    {
        return $this->result;
    }
}