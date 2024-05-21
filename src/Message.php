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


    /**
     * @return string|null
     */
    public function getJSONRPC(): ?string{
        return $this->hasJSONRPC()?$this->value->jsonrpc:null;
    }

    /**
     * @param bool $strictId
     * @return mixed|null
     */
    public function getId(bool $strictId=true){
        return $this->hasId($strictId)?$this->value->id:null;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string{
        return $this->hasMethod()?$this->value->method:null;
    }

    /**
     * @return array|object|null
     */
    public function getParams(){
        return $this->hasParams()?$this->value->params:null;
    }

    /**
     * @return mixed|null
     */
    public function getResult(){
        return $this->hasResult()?$this->value->result:null;
    }

    /**
     * @return object|string|null
     */
    public function getError(){
        return $this->hasError()?$this->value->error:null;
    }

    /**
     * @return int|null
     */
    public function getErrorCode(): ?int{
        return $this->getError()->code ?? null;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string{
        return $this->getError()->message ?? null;
    }

    /**
     * @return mixed|null
     */
    public function getErrorData(){
        return $this->getError()->data ?? null;
    }

    /**
     * @return bool
     */
    public function hasJSONRPC(): bool{
        return property_exists($this->value,'jsonrpc') && $this->value->jsonrpc!==null;
    }

    /**
     * @param bool $strictId
     * @return bool
     */
    public function hasId(bool $strictId=true): bool{
        return property_exists($this->value,'id') && ($strictId?($this->value->id!==null):($this->value->id));
    }

    /**
     * @return bool
     */
    public function hasMethod(): bool{
        return property_exists($this->value,'method') && $this->value->method!==null;
    }

    /**
     * @return bool
     */
    public function hasParams(): bool{
        return property_exists($this->value,'params') && $this->value->params!==null;
    }

    /**
     * @return bool
     */
    public function hasResult(): bool{
        return property_exists($this->value,'result') && $this->value->result!==null;
    }

    /**
     * @return bool
     */
    public function hasError(): bool{
        return property_exists($this->value,'error') && $this->value->error!==null;
    }

    /**
     * @return bool
     */
    public function isRequest(): bool{
        return property_exists($this->value,'method') || property_exists($this->value,'params');
    }

    /**
     * @param bool $strictId
     * @return bool
     */
    public function isNotification(bool $strictId=true): bool{
        return $this->isRequest() && (!property_exists($this->value,'id') || !($strictId?($this->value->id!==null):($this->value->id)));
    }

    /**
     * @return bool
     */
    public function isResponse(): bool{
        return property_exists($this->value,'result') || property_exists($this->value,'error');
    }

    public function isVersion2(): bool{
        return $this->getJSONRPC()==='2.0';
    }

    /**
     * @return object
     */
    public function toObject(): object{
        return $this->value;
    }

    /**
     * @param mixed $id
     * @param string $method
     * @param object|array|null $params
     * @param bool $version2
     * @return RequestMessage
     * @throws JSONRPCException
     */
    public static function createRequest($id, string $method,$params=null,bool $version2=true): RequestMessage{
        if($version2){
            return self::createRequestMessageV2($id,$method,$params);
        }
        return self::createRequestMessageV1($id,$method,$params ?? []);
    }

    /**
     * @param mixed $id
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
     * @param string|numeric|null $id
     * @param string $method
     * @param object|array|null $params
     * @return RequestMessage
     * @throws JSONRPCException
     */
    public static function createRequestMessageV2($id,string $method,$params=null): RequestMessage{
        $arr = [
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
        ];

        if(is_object($params) || is_array($params)){
            $arr['params'] = $params;
        }elseif(!is_null($params)){
            throw new JSONRPCException('[V2] The "params" property in request MUST be an object, array or null.');
        }
        return new RequestMessage((object) $arr);
    }

    /**
     * @param string $method
     * @param object|array|null $params
     * @param bool $version2
     * @return NotificationMessage
     * @throws JSONRPCException
     */
    public static function createNotification(string $method,$params=null,bool $version2=true): NotificationMessage{
        if($version2){
            return self::createNotificationMessageV2($method,$params);
        }
        return self::createNotificationMessageV1($method,$params ?? []);
    }

    /**
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
     * @param string $method
     * @param object|array|null $params
     * @return NotificationMessage
     * @throws JSONRPCException
     */
    public static function createNotificationMessageV2(string $method,$params=null): NotificationMessage{
        $arr = [
            'jsonrpc' => '2.0',
            'method' => $method,
        ];

        if(is_object($params) || is_array($params)){
            $arr['params'] = $params;
        }elseif(!is_null($params)){
            throw new JSONRPCException('[V2] The "params" property in request MUST be an object, array or null.');
        }
        return new NotificationMessage((object) $arr);
    }

    /**
     * @param mixed $id
     * @param mixed $result
     * @param object|string|null $error
     * @param bool $version2
     * @return ResponseMessage
     * @throws JSONRPCException
     */
    public static function createResponse($id,$result,$error,bool $version2=true): ResponseMessage{
        if($version2){
            return self::createResponseMessageV2($id,$result,$error);
        }
        return self::createResponseMessageV1($id,$result,$error);
    }

    /**
     * @param mixed $id
     * @param mixed $result
     * @param object|string|null $error
     * @return ResponseMessage
     * @throws JSONRPCException
     */
    public static function createResponseMessageV1($id,$result=null,$error=null): ResponseMessage{
        if(!is_null($result) && !is_null($error)){
            throw new JSONRPCException('[V1] Only one property "result" or "error" can be non null.');
        }
        if(!is_object($error) && !is_string($error) && !is_null($error)){
            throw new JSONRPCException('[V1] The "error" property in request MUST be an string, object or null.');
        }
        return new ResponseMessage((object) [
            'id' => $id,
            'result' => $result,
            'error' => $error,
        ]);
    }

    /**
     * @param string|numeric|null $id
     * @param mixed $result
     * @param object|null $error
     * @return ResponseMessage
     * @throws JSONRPCException
     */
    public static function createResponseMessageV2($id,$result=null,?object $error=null): ResponseMessage{
        if(!is_null($result) && !is_null($error)){
            throw new JSONRPCException('[V2] Only one property "result" or "error" can be non null.');
        }
        $arr = [
            'jsonrpc' => '2.0',
            'id' => $id,
        ];
        if(!is_null($error)){
            if(!property_exists($error,'code')){
                throw new JSONRPCException('[V2] The error object MUST have a "code" property.');
            }
            if(!property_exists($error,'message')){
                throw new JSONRPCException('[V2] The error object MUST have a "message" property.');
            }
            if(!is_int($error->code)){
                throw new JSONRPCException('[V2] The "code" property of the error object MUST be an integer.');
            }
            if(!is_string($error->message)){
                throw new JSONRPCException('[V2] The "message" property of the error object MUST be a string.');
            }
            $arr['error'] = $error;
        }else{
            $arr['result'] = $result;
        }
        return new ResponseMessage((object) $arr);
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
     * @param mixed $object
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
     * @param object $message
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    private static function handleMessage(object $message,bool $strictId=true){
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
     * @param object $message
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    private static function handleMessageV2(object $message,bool $strictId=true){
        if(self::isRequestMessage($message)){
            self::validateMethodProperty($message);
            if(property_exists($message,'params') && !is_array($message->params) && !is_object($message->params)){
                throw new JSONRPCException('[V2] The "params" property MUST be an array or object if present.');
            }
            if(property_exists($message,'id') && !is_string($message->id) && !is_numeric($message->id) && !is_null($message->id)){
                throw new JSONRPCException('[V2] The "params" property MUST be an string, number or null if present.');
            }
            if(property_exists($message,'id') && ($strictId?($message->id!==null):($message->id))){
                return new RequestMessage($message);
            }else{
                return new NotificationMessage($message);
            }
        }elseif(self::isResponseMessage($message)){
            if(property_exists($message,'result') && property_exists($message,'error')){
                throw new JSONRPCException('[V2] Only one property "result" or "error" can be present.');
            }
            if(property_exists($message,'error')){
                if(!property_exists($message->error,'code')){
                    throw new JSONRPCException('[V2] The error object MUST have a "code" property.');
                }
                if(!property_exists($message->error,'message')){
                    throw new JSONRPCException('[V2] The error object MUST have a "message" property.');
                }
                if(!is_int($message->error->code)){
                    throw new JSONRPCException('[V2] The "code" property of the error object MUST be an integer.');
                }
                if(!is_string($message->error->message)){
                    throw new JSONRPCException('[V2] The "message" property of the error object MUST be a string.');
                }
            }
            if(property_exists($message,'id')){
                return new ResponseMessage($message);
            }else{
                throw new JSONRPCException('[V2] Missing "id" property in response.');
            }
        }else{
            throw new JSONRPCException('[V2] Unknown message type.');
        }
    }


    /**
     * @param object $message
     * @return bool
     */
    private static function isRequestMessage(object $message): bool{
        return property_exists($message,'method') || property_exists($message,'params');
    }

    /**
     * @param object $message
     * @return bool
     */
    private static function isResponseMessage(object $message): bool{
        return property_exists($message,'result') || property_exists($message,'error');
    }

    /**
     * @param object $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateMethodProperty(object $message){
        if(!property_exists($message,'method')){
            throw new JSONRPCException('Missing "method" property in request.');
        }
        if(!is_string($message->method)){
            throw new JSONRPCException('The "method" property in request MUST be a string.');
        }
    }

    /**
     * @param object $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateParamsPropertyV1(object $message){
        if(!property_exists($message,'params')){
            throw new JSONRPCException('[V1] Missing "params" property in request.');
        }
        if(!is_array($message->params)){
            throw new JSONRPCException('[V1] The "params" property in request MUST be an array.');
        }
    }

    /**
     * @param object $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateResultPropertyV1(object $message){
        if(!property_exists($message,'result')){
            throw new JSONRPCException('[V1] Missing "result" property in request.');
        }
    }

    /**
     * @param object $message
     * @return void
     * @throws JSONRPCException
     */
    private static function validateErrorPropertyV1(object $message){
        if(!property_exists($message,'error')){
            throw new JSONRPCException('[V1] Missing "error" property in request.');
        }
        if(!is_object($message->error) && !is_string($message->error) && !is_null($message->error)){
            throw new JSONRPCException('[V1] The "error" property in request MUST be an string, object or null.');
        }
    }

    /**
     * @param object $message
     * @param bool $strictId
     * @return Message
     * @throws JSONRPCException
     */
    private static function handleMessageV1(object $message,bool $strictId=true){
        if(self::isRequestMessage($message)){
            self::validateMethodProperty($message);
            self::validateParamsPropertyV1($message);

            if(property_exists($message,'id') && ($strictId?($message->id!==null):($message->id))){
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