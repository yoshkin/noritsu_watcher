<?php


namespace AYashenkov\services;


use AYashenkov\LogMessage;

interface SenderInterfase
{
    /**
     * @param LogMessage $message
     */
    public function send(LogMessage $message): void;
}