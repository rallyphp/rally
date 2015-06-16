<?php
namespace Rally;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class App implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo $msg, "\n";
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        echo "Connection ({$conn->resourceId}) has disconnected.\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();

        echo "An error has occurred: {$e->getMessage()}\n";
    }
}
