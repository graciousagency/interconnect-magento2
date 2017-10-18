<?php
namespace Gracious\Interconnect\Reporting;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class OutputAdapter
 * @package Gracious\Interconnect\Reporting
 * Adapts an OutputInterface instance so it can be passed as a logger instance. Particularly useful for scenarios where
 * console commands and none-cli scripts execute the same logic but differ in the way they output progress.
 */
class OutputAdapter implements LoggerInterface
{

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * OutputAdapter constructor.
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = array())
    {
        $this->write($message, $context,OutputInterface::OUTPUT_NORMAL);
    }

    /**
     * @param string $message
     * @param array $context
     * @param string $outputType
     */
    protected function write($message, array $context, $outputType) {
        $this->output->writeln($message.', '.json_encode($context), $outputType);
    }
}