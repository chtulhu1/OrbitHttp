OrbitHttp
=========

Simple object oriented cURL wrapper. Has many useful things.

<b>Client init</b>
```php
$client = OrbitHttp\Client::open();
```

<b>Requests</b>
```php
$response_obj = $client->get('https://domain.com');
$response_obj = $client->post('https://domain.com/login', array('login' => 'admin', 'passw' => 'test'));
```

<b>Сlient configuration</b>
```php
$client->setBrowser('Firefox/...');
$client->setReferer('https://domain.com');
$client->setConnectTimeout(20);
$client->setTimeout(10);
$client->setProxy('111.111.111.111:8080', CURLPROXY_SOCKS5, 'login:pass');
$client->setHeaders(array('Accept-Language: ru-RU,ru;q=0.8', 'Content-Type: text/html; charset=utf-8'));
$client->setCurlOpt(CURLOPT_AUTOREFERER, true);
```

<b>Working with cookies</b>
You can create multiple cookie sessions. for this purposes use OrbitHttp\CookieSession.

Create first session
```php
OrbitHttp\CookieSession::open('session_id_name', $full_path_to_dir_or_file);
//binding this session to the client
$client->setCookies('session_id_name');
```

Or shorter
```php
$client->setCookies('session_id_name', $full_path_to_dir_or_file);
```

Sessions toggling
```php
OrbitHttp\CookieSession::open('session_id_name')->useSession('other_session_id');
// or
$client->setCookies('other_session_id', $full_path_to_dir_or_file);
```

<b>Processing a response object</b>
```php
$htmlDom = $response_obj->dom();
$xpath = $response_obj->xpath();
$decoded_json_string = $response_obj->obj();
$convert_encoding = $response_obj->iconv('cp1251', 'utf-8');
$response_code = $response_obj->info('http_code');
```
 and more...


