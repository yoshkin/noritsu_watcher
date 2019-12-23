<?php


namespace AYashenkov\services;

use AYashenkov\LogMessage;
use Psr\Log\LoggerInterface;

class SysCallSender implements SenderInterfase
{
    /** @var ftok key */
    private $key;
    /** @var message queue */
    private $msqid;
    /** @var LoggerInterface $logger */
    private $logger;

    /**
     * SysCallSender constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        /** @var $logger LoggerInterface */
        $this->logger = $logger;
        $this->init();
    }

    /**
     * init SysCallSender
     */
    public function init()
    {
        /* Generate key, param for ftok must be same as in C++ component */
        if(($this->key = ftok($_ENV['FTOK_PATH'], "s")) == -1) {
            $this->logger->critical('Could not generate Ftok key'); die();
        }

        if(!msg_queue_exists($this->key)) {
            $this->logger->critical("Message queue doesn't exists"); die();
        }

        /* Connect to message queue */
        if(($this->msqid = msg_get_queue($this->key)) === FALSE) {
            $this->logger->critical("Could not connect to Message queue"); die();
        }

//        return $this;
    }

    /**
     * Sending message to SysCall message queue
     *
     * @param LogMessage $message
     */
    public function send(LogMessage $message): void
    {
        if(!msg_send($this->msqid, $message->getCode(), $message->getLogMessageInBytes(), false)) {
            $this->logger->critical("Could not send message to Message queue"); die();
        }
    }
}