<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterFeatureTested;

class Feature
{

    public $scenarios;

    public $background;
    
    public $file;

    public $title;

    public $description;

    public $result;

    public function __construct(AfterFeatureTested $event, $scenarios, $background)
    {
        $this->scenarios = $scenarios;
        $this->background = $background;
        $this->title = $event->getFeature()->getTitle();
        $this->description = $event->getFeature()->getDescription();
        $this->result = $event->getTestResult()->getResultCode();
        $this->file = $event->getFeature()->getFile();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getScenarios()
    {
        return $this->scenarios;
    }

    public function getBackground()
    {
        return $this->background;
    }

    public function getFile()
    {
        return $this->file;
    }
}