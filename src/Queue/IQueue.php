<?php

namespace ShoppinPal\YapepCommon\Queue;

interface IQueue
{
    /**
     * Sends a message in the queue.
     *
     * @param string $queueConfigName
     * @param mixed  $messageBody
     *
     * @return void
     */
    public function sendMessage($queueConfigName, $messageBody);

    /**
     * Receives a message from the queue
     *
     * @param string $queueConfigName
     * @param int    $waitTimeSeconds The number of seconds to wait for if it's supported by the queue backend.
     *
     * @return null|QueueMessageDo The message or NULL if none is available.
     */
    public function receiveMessage($queueConfigName, $waitTimeSeconds = 0);

    /**
     * Deletes a message from the queue
     *
     * @param string      $queueConfigName
     * @param string|null $deleteId
     *
     * @return void
     */
    public function deleteMessage($queueConfigName, $deleteId);
}
