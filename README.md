## CURL请求类库

> PHP实现的CURL请求类库，不要跟我说有啥Guzzle之类的，情景是想要个简单的类库，能实现基本的get、post就行，为啥要装个那么重的玩意。。按需自取吧。


### 用法：

```php
<?php

// GET http://www.baidu.com?id=1
$result = Curl::get('http://www.baidu.com', ['id' => 1], ['Header: xxx', 'Header1: 111']);

// GET http://www.baidu.com?id=1&name=2
$result = Curl::get('http://www.baidu.com?id=1', ['name' => 2], ['Header: xxx', 'Header1: 111']);

// POST http://www.baidu.com   id=1
$result = Curl::post('http://www.baidu.com', ['id' => 1], ['Header: xxx', 'Header1: 111']);

```
