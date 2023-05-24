# PHP FakeYou

A library to interact with FakeYou APIs

## Usage
Install
```
composer require shreejalmaharjan-27/php-fakeyou
```

Import library
```php
use Shreejalmaharjan27\PhpFakeyou\FakeYou;
```

Create Object
```php
$api = new FakeYou();
```

(Optional) Login to your FakeYou account
```php
$api->login('joe@example.com', '12345678');
```

Audio Generation
```php
/**
 * @param string The message to be converted to audio
 * @param string The model to be used for the audio
 */
$audio = $api->audio('Hello world', 'TM:fxq6hnfc3rht');
```

Check If Audio has been generated
```php
/**
 * @param string Job Token
 * @param string Type of Check (lipsync/audio)
 */
$check = $api->check($audio['inference_job_token'], 'tts');

var_dump($check);
```

LipSync Generation
```php
$video = $api->lipsync('https://example.com/audio.wav')
```

Check if video has been generated
```php
/**
 * @param string Job Token
 * @param string Type of Check (lipsync/audio)
 */
$check = $api->check($video['inference_job_token'], 'lipsync');

var_dump($check);
```
