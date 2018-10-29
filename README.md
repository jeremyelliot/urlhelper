# UrlHelper

UrlHelper is a wrapper class for parsing URLs. It provides a way to:
- get particular parts of the URL
- extract parts of the URL into a new valid URL.

UrlHelpers are immutable. When a UrlHelper is cast to string it will give the same string that was passed into the
constructor.

The `get()` method is used for building a URL from the parts you have. The `getContextPart()` method will
return the part of the url to which a relative URL would be appended by a web browser.

## Examples

```php
<?php

$fullUrl = new UrlHelper('https://boss:pass123@example.com/foo/bar.php?q=any&lang=en#baz');
$partialUrl = new UrlHelper('/foo/baz?param=value');

// this string is the pattern to get the full URL
$completeUrlPattern = 'scheme.user.pass.host.port.dir.file.ext.query.fragment';

echo $fullUrl->get($completeUrlPattern);
// --> https://boss:pass123@example.com/foo/bar.php?q=any&lang=en#baz
echo $partialUrl->get($completeUrlPattern);
// --> /foo/baz?param=value


echo $fullUrl->get('base');
// --> https://boss:pass123@example.com
echo $partialUrl->get('base');
// --> (empty UrlHelper, casts to empty string)


echo $fullUrl->get('user.host.dir');
// --> //boss@example.com/foo/
echo $partialUrl->get('user.host.dir');
// --> /foo/



echo $fullUrl->getContextPart();
// --> https://boss:pass123@example.com/foo/

echo $fullUrl->get('query');
// --> ?q=any&lang=en

```
