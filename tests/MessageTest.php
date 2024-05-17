<?php
namespace YOCLIB\JSONRPC\Tests;

use PHPUnit\Framework\TestCase;

use YOCLIB\JSONRPC\JSONRPCException;
use YOCLIB\JSONRPC\Message;
use YOCLIB\JSONRPC\NotificationMessage;
use YOCLIB\JSONRPC\RequestMessage;
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

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testEncodeJSONString(){
        $this->assertEquals('"abc"',Message::encodeJSON('abc'));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testEncodeJSONObject(){
        $this->assertEquals('{}',Message::encodeJSON((object) []));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testEncodeJSONArray(){
        $this->assertEquals('[]',Message::encodeJSON([]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testEncodeJSONResource(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('Failed to encode JSON.');

        Message::encodeJSON(tmpfile());
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
        $this->expectExceptionMessage('The "method" property in request MUST be a string.');

        Message::parseObject((object) ['method'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithMethodString(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Missing "params" property in request.');

        Message::parseObject((object) ['method'=>'abc']);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithParams(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('Missing "method" property in request.');

        Message::parseObject((object) ['params'=>null]);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithMethodStringAndParams(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] The "params" property in request MUST be an array.');

        Message::parseObject((object) ['method'=>'abc','params'=>null]);
    }


    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithMethodStringAndParamsArray(){
        $this->assertInstanceOf(NotificationMessage::class,Message::parseObject((object) ['method'=>'abc','params'=>[]]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithIdNullAndMethodStringAndParamsArray(){
        $this->assertInstanceOf(NotificationMessage::class,Message::parseObject((object) ['id'=>null,'method'=>'abc','params'=>[]]));
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testParseRequestV1WithIdFalsyAndMethodStringAndParamsArray(){
        $this->assertInstanceOf(RequestMessage::class,Message::parseObject((object) ['id'=>false,'method'=>'abc','params'=>[]]));
        $this->assertInstanceOf(NotificationMessage::class,Message::parseObject((object) ['id'=>false,'method'=>'abc','params'=>[]],false));
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
    public function testParseResponseV1WithResultNullAndErrorNumber(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] The "error" property in request MUST be an string, object or null.');

        Message::parseObject((object) ['result'=>null,'error'=>12.34]);
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
        $this->assertInstanceOf(ResponseMessage::class,Message::parseObject((object) ['id'=>false,'result'=>'abc','error'=>null],false));
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
    public function testGetters(){
        $this->assertEquals(123,Message::createRequestMessageV1(123,'myMethod')->getId());
        $this->assertEquals('myMethod',Message::createRequestMessageV1(123,'myMethod')->getMethod());
        $this->assertEquals([],Message::createRequestMessageV1(123,'myMethod')->getParams());

        $this->assertNull(Message::createNotificationMessageV1('myMethod')->getId());
        $this->assertEquals('myMethod',Message::createNotificationMessageV1('myMethod')->getMethod());
        $this->assertEquals([],Message::createNotificationMessageV1('myMethod')->getParams());

        $this->assertEquals(123,Message::createResponseMessageV1(123,'myResult')->getId());
        $this->assertEquals('myResult',Message::createResponseMessageV1(123,'myResult')->getResult());
        $this->assertNull(Message::createResponseMessageV1(123,'myResult')->getError());

        $this->assertEquals(456,Message::createResponseMessageV1(123,null,(object) ['code'=>456,'message'=>'Some error text','data'=>true])->getErrorCode());
        $this->assertEquals('Some error text',Message::createResponseMessageV1(123,null,(object) ['code'=>456,'message'=>'Some error text','data'=>true])->getErrorMessage());
        $this->assertTrue(Message::createResponseMessageV1(123,null,(object) ['code'=>456,'message'=>'Some error text','data'=>true])->getErrorData());
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testIsRequest(){
        $this->assertTrue(Message::createRequestMessageV1(123,'myMethod')->isRequest());
        $this->assertTrue(Message::createNotificationMessageV1('myMethod')->isRequest());
        $this->assertFalse(Message::createResponseMessageV1(123,'myResult')->isRequest());
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testIsNotification(){
        $this->assertFalse(Message::createRequestMessageV1(123,'myMethod')->isNotification());
        $this->assertTrue(Message::createNotificationMessageV1('myMethod')->isNotification());
        $this->assertFalse(Message::createResponseMessageV1(123,'myResult')->isNotification());
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testIsResponse(){
        $this->assertFalse(Message::createRequestMessageV1(123,'myMethod')->isResponse());
        $this->assertFalse(Message::createNotificationMessageV1('myMethod')->isResponse());
        $this->assertTrue(Message::createResponseMessageV1(123,'myResult')->isResponse());
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testIsVersion2(){
        $this->assertFalse(Message::createRequestMessageV1(123,'myMethod')->isVersion2());
        $this->assertTrue(Message::createRequestMessageV2(123,'myMethod')->isVersion2());

        $this->assertFalse(Message::createNotificationMessageV1('myMethod')->isVersion2());
        $this->assertTrue(Message::createNotificationMessageV2('myMethod')->isVersion2());

        $this->assertFalse(Message::createResponseMessageV1(123,'myMethod')->isVersion2());
        $this->assertTrue(Message::createResponseMessageV2(123,'myMethod')->isVersion2());
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testCreateRequestMessageV2WithIdAndMethodAndParamsFalse(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V2] The "params" property in request MUST be an object, array or null.');

        Message::createRequestMessageV2(123,'abc',false);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testCreateResponseMessageV1WithResultAndError(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] Only one property "result" or "error" can be non null.');

        Message::createResponseMessageV1(123,'abc','def');
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testCreateResponseMessageV1WithErrorFalse(){
        $this->expectException(JSONRPCException::class);
        $this->expectExceptionMessage('[V1] The "error" property in request MUST be an string, object or null.');

        Message::createResponseMessageV1(123,null,false);
    }

    /**
     * @return void
     * @throws JSONRPCException
     */
    public function testCreateRequestOrNotificationV2(){
        $this->assertInstanceOf(RequestMessage::class,Message::createRequestMessageV2(123,'myMethod',[]));
        $this->assertInstanceOf(RequestMessage::class,Message::createRequestMessageV2(123,'myMethod',(object) []));

        $this->assertInstanceOf(NotificationMessage::class,Message::createNotificationMessageV2('myMethod',[]));
        $this->assertInstanceOf(NotificationMessage::class,Message::createNotificationMessageV2('myMethod',(object) []));
    }

    public function testToObject(){
        $this->assertEquals((object) ['id'=>123,'method'=>'getMethod','params'=>['param1','param2']],Message::createRequestMessageV1(123,'getMethod',['param1','param2'])->toObject());
    }

//    /**
//     * @return void
//     * @throws JSONRPCException
//     */
//    public function testMessages(){
//        $this->assertEquals((object) ["id"=>123,"method"=>"myMethod","params"=>[]],Message::createRequestMessageV1(123,'myMethod')->toObject());
//        $this->assertEquals((object) ["id"=>123,"method"=>"myMethod","params"=>["a",1,false,12.34]],Message::createRequestMessageV1(123,'myMethod',['a',1,false,12.34])->toObject());
//        $this->assertEquals((object) ["id"=>null,"method"=>"myMethod","params"=>[]],Message::createNotificationMessageV1('myMethod')->toObject());
//        $this->assertEquals((object) ["id"=>null,"method"=>"myMethod","params"=>["b",0,true,34.12]],Message::createNotificationMessageV1('myMethod',['b',0,true,34.12])->toObject());
//        $this->assertEquals((object) ["id"=>123,"result"=>"myResult","error"=>null],Message::createResponseMessageV1(123,'myResult')->toObject());
//        $this->assertEquals((object) ["id"=>123,"result"=>null,"error"=>"myError"],Message::createResponseMessageV1(123,null,'myError')->toObject());
//    }

}