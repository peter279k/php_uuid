# php_uuid
PHP UUID generator (RFC 4122 compliant).

Based on [r-lyeh/sole](https://github.com/r-lyeh/sole).

```php
use UUID\UUID;
$uuid = new UUID($mac);
echo($uuid->v1);
```
