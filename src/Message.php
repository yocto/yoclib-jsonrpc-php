<?php
namespace YOCLIB\JSONRPC;

use JsonException;

abstract class Message{

    private object $value;

    /**
     * @param object $value
     */
    private function __construct(object $value){
        $this->value = $value;
    }

    public function isRequest(): bool{
        return property_exists($this->value,'method') || property_exists($this->value,'params');
    }

    public function isNotification($strictId=true): bool{
        return $this->isRequest() && (!property_exists($this->value,'id') || !($strictId?($this->value->id!==null):($this->value->id)));
    }

    public function isResponse(): bool{
        return property_exists($this->value,'result') || property_exists($this->value,'error');
    }

    /**
     * @return object
     */
    public function toObject(): object{
        return $this->value;
    }

    /**
     * @param $id
     * @param string $method
     * @param array $params
     * @return RequestMessage
     */
    public static function createRequestMessageV1($id,string $method,array $params=[]): RequestMessage{
        return new RequestMessage((object) [
            'id' => $id,
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * @param $id
     * @param string $method
     * @param array $params
     * @return NotificationMessage
     */
    public static function createNotificationMessageV1(string $method,array $params=[]): NotificationMessage{
        return new NotificationMessage((object) [
            'id' => null,
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * @param $id
     * @param $result
     * @param object|string|null $error
     * @return RequestMessage
     * @throws JSONRPCException
     */
    public static function createResponseMessageV1($id,$result=null,$error=null): RequestMessage{
        if(!is_null($result) && !is_null($error)){
            throw new JSONRPCException('[V1] Only one property "result" or "error" can be non null.');
        }
        if(!is_object($error) && !is_string($error) && !is_null($error)){
            throw new JSONRPCException('[V1] The "error" property in request MUST be an string, object or null.');
        }
        return new RequestMessage((object) [
            'id' => $id,
            'result' => $result,
            'error' => $error,
        ]);
    }

    /**
     * @param $object
     * @return bool
     */
    public static function isBatch($object): bool{
        return is_array($object);
    }

    /**
     * @param $object
     * @return false|string
     * @throws JSONRPCException
     */
    public static function encodeJSON($object){
        try{
            return json_encode($object,JSON_THROW_ON_ERROR);
        }catch(JsonException $e){
            throw new JSONRPCException('Failed to encode JSON.');
        }
    }

    /**
     * @param string $json
     * @return mixed
     * @throws JSONRPCException
     */
    public static function decodeJSON(string $json){
        try{
            return json_decode($json,false,512,JSON_THROW_ON_ERROR);
        }catch(JsonException $e){
            throw new JSONRPCException('Failed to decode JSON.');
        }
    }

    /**
     * @param $object
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    public static function parseObject($object,bool $strictId=true){
        if(is_object($object)){
            return self::handleMessage($object,$strictId);
        }
        throw new JSONRPCException('A message MUST be a JSON object.');
    }

    /**
     * @param $message
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    private static function handleMessage($message,bool $strictId=true){
        if(property_exists($message,'jsonrpc')){
            if($message->jsonrpc==='2.0'){
                return self::handleMessageV2($message,$strictId);
            }
            throw new JSONRPCException('Unknown version "'.($message->jsonrpc).'".');
        }else{
            return self::handleMessageV1($message,$strictId);
        }
    }

    /**
     * @param $message
     * @param bool $strictId
     * @return null
     * @throws JSONRPCException
     */
    private static function handleMessageV2($message,bool $strictId=true){
        if(self::isRequestMessage($message)){
            return null;
        }elseif(self::isResponseMessage($message)){
            return null;
        }else{
            throw new JSONRPCException('[V2] Unknown message type.');
        }
    }


    private static function isRequestMessage($message): bool{
        return property_exists($message,'method') || property_exists($message,'params');
    }

    private static function isResponseMessage($message): bool{
        return property_exists($message,'result') || property_exists($message,'error');
    }

    /**
     * @param object $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateMethodPropertyV1(object $message){
        if(!property_exists($message,'method')){
            throw new JSONRPCException('[V1] Missing "method" property in request.');
        }
        if(!is_string($message->method)){
            throw new JSONRPCException('[V1] The "method" property in request MUST be a string.');
        }
    }

    /**
     * @param $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateParamsPropertyV1($message){
        if(!property_exists($message,'params')){
            throw new JSONRPCException('[V1] Missing "params" property in request.');
        }
        if(!is_array($message['params'])){
            throw new JSONRPCException('[V1] The "params" property in request MUST be an array.');
        }
    }

    /**
     * @param $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateResultPropertyV1($message){
        if(!property_exists($message,'result')){
            throw new JSONRPCException('[V1] Missing "result" property in request.');
        }
    }

    /**
     * @param $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateErrorPropertyV1($message){
        if(!property_exists($message,'error')){
            throw new JSONRPCException('[V1] Missing "error" property in request.');
        }
        if(!is_object($message->error) && !is_string($message->error) && !is_null($message->error)){
            throw new JSONRPCException('[V1] The "error" property in request MUST be an string, object or null.');
        }
    }

    /**
     * @param $message
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    private static function handleMessageV1($message,bool $strictId=true){
        if(self::isRequestMessage($message)){
            self::validateMethodPropertyV1($message);
            self::validateParamsPropertyV1($message);

            if(property_exists($message,'id') && $strictId?($message->id!==null):($message->id)){
                return new RequestMessage($message);
            }else{
                return new NotificationMessage($message);
            }
        }elseif(self::isResponseMessage($message)){
            self::validateResultPropertyV1($message);
            self::validateErrorPropertyV1($message);
            if(!is_null($message->result) && !is_null($message->error)){
                throw new JSONRPCException('[V1] Only one property "result" or "error" can be non null.');
            }

            if(property_exists($message,'id')){
                return new ResponseMessage($message);
            }else{
                throw new JSONRPCException('[V1] Missing "id" property in response.');
            }
        }else{
            throw new JSONRPCException('[V1] Unknown message type.');
        }
    }

}