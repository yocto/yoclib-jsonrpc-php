<?php
namespace YOCLIB\JSONRPC\Tests;

use PHPUnit\Framework\TestCase;

use YOCLIB\JSONRPC\JSONRPCException;
use YOCLIB\JSONRPC\Message;

class MessageTest extends TestCase{

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testMessages(){
        $this->assertEquals('{"id":123,"method":"myMethod","params":[]}',Message::createRequestMessageV1(123,'myMethod')->toJSON());
        $this->assertEquals('{"id":null,"method":"myMethod","params":[]}',Message::createNotificationMessageV1('myMethod')->toJSON());
        $this->assertEquals('{"id":123,"result":"myResult","error":null}',Message::createResponseMessageV1(123,'myResult')->toJSON());
        $this->assertEquals('{"id":123,"result":null,"error":"myError"}',Message::createResponseMessageV1(123,null,'myError')->toJSON());
    }

}