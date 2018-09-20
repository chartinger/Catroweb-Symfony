<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Gherkin\Node\StepNode;

class OutlineStep implements StepInterface
{

    public $arguments;

    public $keyword;

    public $baseText;

    public $result;

    public $line;

    public function __construct(StepNode $step)
    {
        $this->arguments = $this->createArguments($step);
        $this->keyword = $step->getKeyword();
        $this->baseText = $step->getText();
        $this->result = -1;
        $this->line = $step->getLine();
    }

    public function getText()
    {
        return $this->keyword . " " . $this->baseText;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getLine()
    {
        return $line;
    }

    private function createArguments($stepnode)
    {
        $arguments = array();
        
        foreach ($stepnode->getArguments() as $argument) {
            $argument_array = array();
            $argument_array["type"] = $argument->getNodeType();
            switch ($argument->getNodeType()) {
                case "PyString":
                    $argument_array["text"] = $argument->getRaw();
                    break;
                case "Table":
                    $argument_array["table"] = $argument->getTable();
                    break;
            }
            $arguments[] = $argument_array;
        }
        
        return $arguments;
    }
}