<?php
namespace Catrobat\Behat\TwigReportExtension\facades;

use Behat\Behat\EventDispatcher\Event\AfterStepTested;
use Behat\Testwork\Tester\Result\ExceptionResult;

class Step implements StepInterface
{

    private $keyword;

    private $baseText;

    private $result;

    private $hasException;

    private $exception;

    private $line;

    private $arguments;

    public function __construct(AfterStepTested $event)
    {
        $this->keyword = $event->getStep()->getKeyword();
        $this->baseText = $event->getStep()->getText();
        $this->result = $event->getTestResult()->getResultCode();
        $this->hasException = $event->getTestResult() instanceof ExceptionResult && $event->getTestResult()->getException();
        if ($this->hasException) {
            $this->exception = $event->getTestResult()->getException();
        }
        $this->line = $event->getStep()->getLine();
        $this->arguments = $this->createArguments($event);
    }

    public function getText()
    {
        return $this->keyword . " " . $this->baseText;
    }

    public function getResult()
    {
        return $this->result; 
    }

    private function hasException()
    {
        return $this->hasException;
    }

    public function getException()
    {
        return $this->exception;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getLine()
    {
        return $this->line;
    }

    private function createArguments($event)
    {
        $arguments = array();
        
        foreach ($event->getStep()->getArguments() as $argument) {
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