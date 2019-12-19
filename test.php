<?php

require 'vendor/autoload.php';

/*
 * Testing send message to C component (test.c)
 */

/* Generate key, param fot ftok must be same as in test_msg.c */
if(($key = ftok("test", "s")) == -1) {
    die("ftok");
}

if(!msg_queue_exists($key)) {
    die("message queue doesn't exists");
}

/* Connect to message queue */
if(($msqid = msg_get_queue($key)) === FALSE) {
    die("msg_get_queue");
}

echo "Sending text to msg queue.\n";
$msg = new \AYashenkov\LogMessage(0, 10001, 'Test description', 'test comment');

/* Send message to C program */
if(!msg_send($msqid, $msg->getCode(), $msg->getLogMessageInBytes(), false))
    die("msg_send");

echo "Done";