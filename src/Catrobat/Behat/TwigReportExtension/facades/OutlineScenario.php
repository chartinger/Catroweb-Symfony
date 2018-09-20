<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Catrobat\Behat\TwigReportExtension\facades\ScenarioInterface;
use Behat\Behat\EventDispatcher\Event\AfterOutlineTested;

class OutlineScenario implements ScenarioInterface
{

    public $parameters;

    public $examples;

    public $steps;

    public $title;

    public $result;

    public function __construct(AfterOutlineTested $event, $steps)
    {
        if (count($event->getOutline()
            ->getExampleTable()
            ->getTable()) > 0) {
            $this->parameters = array_values($event->getOutline()
                ->getExampleTable()
                ->getTable())[0];
        } else {
            $this->parameters = array();
        }
        $this->generateExamples($event, $steps);
        $this->generateSteps($event);
        $this->title = $event->getOutline()->getTitle();
        $this->result = $event->getTestResult()->getResultCode();
    }

    public function getSteps()
    {
        return $this->steps;
    }

    public function isOutline()
    {
        return true;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getExamples()
    {
        return $this->examples;
    }

    private function generateExamples($event, $steps)
    {
        $example_nodes = $event->getOutline()->getExamples();
        $example_value_table = $event->getOutline()->getExampleTable();
        
        $step_counter = 0;
        $example_counter = 0;
        
        $example_results = array();
        
        foreach ($example_nodes as $example_node) {
            $worst_result_from_steps = 0;
            $example_steps = array();
            
            $steps_per_example = count($example_node->getSteps());
            for ($example_step_index = 0; $example_step_index < $steps_per_example; $example_step_index ++) {
                if ($steps[$step_counter]->getResult() > $worst_result_from_steps) {
                    $worst_result_from_steps = $steps[$step_counter]->getResult();
                }
                $example_steps[] = $steps[$step_counter];
                $step_counter ++;
            }
            $example_results[] = new Example($worst_result_from_steps, $example_value_table->getRow($example_counter + 1), $example_steps);
            $example_counter ++;
        }
        
        $this->examples = $example_results;
    }

    private function generateSteps($event)
    {
        $this->steps = array();
        foreach ($event->getOutline()->getSteps() as $stepnode) {
            $this->steps[] = new OutlineStep($stepnode);
        }
    }
}