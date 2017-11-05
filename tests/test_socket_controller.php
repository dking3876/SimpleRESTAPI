<?php
namespace Test\testing;

class test_socket_controller{
    /**
     * @var ClientConnection[]
     */
    public static $s_clients = [];
    public static $receivedEvents = [];
    public function __construct(){
        print "instantiated the class";
    }

    public function testTick(\SimpleRESTAPI\WebSocket $socket, $clientId){
        if(isset(static::$s_clients[$clientId])){
            //do stuff for the client on their tick

            //read received events and transmit to the client
            // print static::$s_clients[$clientId]->received."\n";
        }
        foreach(static::$s_clients as $client){
            // var_dump($client);
            // $socket->wsSend($client->ID, "right back at you number " . $client->ID);
        }
    }
    public function testOpen(\SimpleRESTAPI\WebSocket $socket, $clientId){
        static::$s_clients[$clientId] = new \Test\testing\ClientConnection($clientId);
    }
    public function testMessage(\SimpleRESTAPI\WebSocket $socket, $clientId, $data, $dataLength){
    //    static::$s_clients[$clientId]->received = $data;
        print $data."/n";
        print $dataLength."/n";
        // var_dump($opcode);
        $data = json_decode( $data, true );
        foreach(static::$s_clients as $client){
            if($client->ID == $clientId){
                continue;
            }
            $socket->wsSend($client->ID, $data['message']);
        }
    }
    public function testClose(\SimpleRESTAPI\WebSocket $socket, $clientId, $status){
        print "goodbye \n";
        unset( static::$s_clients[$clientId]);
    }
}

class ClientConnection{
    public $ID;

    public $events;

    public $register;

    public $received;

    public $transmit;
    public function __construct($ID){
        $this->ID = $ID;
    }
}