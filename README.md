# yocLib - JSON-RPC (PHP)

This yocLibrary enables your project to encode and decode JSON-RPC messages in PHP.

## Status

[![PHP Composer](https://github.com/yocto/yoclib-jsonrpc-php/actions/workflows/php.yml/badge.svg)](https://github.com/yocto/yoclib-jsonrpc-php/actions/workflows/php.yml)
[![codecov](https://codecov.io/gh/yocto/yoclib-jsonrpc-php/graph/badge.svg?token=CVJJGTZJ1X)](https://codecov.io/gh/yocto/yoclib-jsonrpc-php)

## Installation

`composer require yocto/yoclib-jsonrpc`

## Use

### Serialization

```php
use YOCLIB\JSONRPC\JSONRPCException;
use YOCLIB\JSONRPC\Message;

$message = Message::createRequestMessageV1(123,'getInfo',['payments']); // Create request (version 1.0)
$message = Message::createNotificationMessageV1('notificationEvent',['payed']); // Create notification (version 1.0)
$message = Message::createResponseMessageV1(123,['payments'=>[]]); // Create response (version 1.0)

$object = $message->toObject();

try{
    $json = Message::encodeJSON($object);
}catch(JSONRPCException $e){
    //Handle encoding exception
}
```

### Deserialization

```php
use YOCLIB\JSONRPC\JSONRPCException;
use YOCLIB\JSONRPC\Message;

$json = file_get_contents('php://input'); // Get request body

try{
    $object = Message::decodeJSON($json);
}catch(JSONRPCException $e){
    //Handle decoding exception
}

if(Message::isBatch($object)){
    foreach($object AS $element){
        try{
            $message = Message::parse($element);
        }catch(JSONRPCException $e){
            //Handle message exception
        }
    }
}else{
    try{
        $message = Message::parse($object);
    }catch(JSONRPCException $e){
        //Handle message exception
    }
}
```