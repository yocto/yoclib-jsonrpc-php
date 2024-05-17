<?php
namespace YOCLIB\JSONRPC\Tests;

use PHPUnit\Framework\TestCase;

use YOCLIB\JSONRPC\JSONRPCException;
use YOCLIB\JSONRPC\Message;
use YOCLIB\JSONRPC\ResponseMessage;

class MessageTest extends TestCase{

    public function testDecodeEmptyJSON(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('Failed to decode JSON.');

        Message::decodeJSON('');
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testDecodeJSONString(){
        $this->assertEquals('abc',Message::decodeJSON('"abc"'));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testDecodeJSONObject(){
        $this->assertEquals((object) [],Message::decodeJSON('{}'));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testDecodeJSONArray(){
        $this->assertEquals([],Message::decodeJSON('[]'));
    }

    public function testIsBatch(){
        $this->assertTrue(Message::isBatch([]));

        $this->assertFalse(Message::isBatch('abc'));
        $this->assertFalse(Message::isBatch(true));
        $this->assertFalse(Message::isBatch(false));
        $this->assertFalse(Message::isBatch(123));
        $this->assertFalse(Message::isBatch(123.456));
        $this->assertFalse(Message::isBatch((object) []));
        $this->assertFalse(Message::isBatch(null));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectString(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject('abc');
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectTrue(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject(true);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectFalse(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject(false);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectInteger(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject(123);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectFloat(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject(123.456);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseObjectArray(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('A message MUST be a JSON object.');

        Message::parseObject([]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseEmptyObject(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Unknown message type.');

        Message::parseObject((object) []);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithMethod(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] The "method" property in request MUST be a string.');

        Message::parseObject((object) ['method'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithParams(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Missing "method" property in request.');

        Message::parseObject((object) ['params'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithResult(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Missing "error" property in request.');

        Message::parseObject((object) ['result'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithError(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Missing "result" property in request.');

        Message::parseObject((object) ['error'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithResultAndError(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Only one property "result" or "error" can be non null.');

        Message::parseObject((object) ['result'=>'abc','error'=>'def']);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithResultAndNullError(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Missing "id" property in response.');

        Message::parseObject((object) ['result'=>'abc','error'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithIdAndResultAndNullError(){
        $this->assertInstanceOf(ResponseMessage::class,Message::parseObject((object) ['id'=>123,'result'=>'abc','error'=>null]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithIdNullAndResultAndNullError(){
        $this->assertInstanceOf(ResponseMessage::class,Message::parseObject((object) ['id'=>null,'result'=>'abc','error'=>null]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseResponseV1WithIdFalsyAndResultAndNullError(){
        $this->assertInstanceOf(ResponseMessage::class,Message::parseObject((object) ['id'=>false,'result'=>'abc','error'=>null]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseVersion2(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V2] Unknown message type.');

        Message::parseObject((object) [
            'jsonrpc' => '2.0',
        ]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseUnknownVersion(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('Unknown version "1.5".');

        Message::parseObject((object) [
            'jsonrpc' => '1.5',
        ]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testMessages(){
        $this->assertEquals((object) ["id"=>123,"method"=>"myMethod","params"=>[]],Message::createRequestMessageV1(123,'myMethod')->toObject());
        $this->assertEquals((object) ["id"=>123,"method"=>"myMethod","params"=>["a",1,false,12.34]],Message::createRequestMessageV1(123,'myMethod',['a',1,false,12.34])->toObject());
        $this->assertEquals((object) ["id"=>null,"method"=>"myMethod","params"=>[]],Message::createNotificationMessageV1('myMethod')->toObject());
        $this->assertEquals((object) ["id"=>null,"method"=>"myMethod","params"=>["b",0,true,34.12]],Message::createNotificationMessageV1('myMethod',['b',0,true,34.12])->toObject());
        $this->assertEquals((object) ["id"=>123,"result"=>"myResult","error"=>null],Message::createResponseMessageV1(123,'myResult')->toObject());
        $this->assertEquals((object) ["id"=>123,"result"=>null,"error"=>"myError"],Message::createResponseMessageV1(123,null,'myError')->toObject());
    }

}