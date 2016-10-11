Moose
=====

<image src="/moose64.png" align="left" />

L[oose] object [M]apper - it maps data on objects, failing only gracefully.
 Instead of throwing an exception when some piece of data isn't right it will
 just return to you a stack of collected errors and a partial object.

This is useful for consuming 3d-party APIs or building your own
 where you need to report all invalid pieces of data.

### Using it

You can add this library to your project with `composer require foorg/moose`.

The most convenient way of using it is via `AnnotationMetadataProvider`.
Suppose we have this kind of API format for sending email messages:
```json
{
    "recipient": {"fullname": "Leonard Cohen", "email": "leo@example.com"},
    "subject": "Subject text",
    "body": "message text"
}
```
We can map this on objects if we define them with appropriate annotations:

```php
class Message
{
    /**
     * @ObjectField("Recipient")
     **/
    private $recipient;

    /**
     * @StringField()
     **/
    private $subject;

    /**
     * @StringField()
     **/
    private $body;
}

class Recipient
{
    /**
     * @StringField()
     **/
    private $fullname;

    /**
     * @StringField()
     **/
    private $email;
}
```
Here's how the mapping will be done in our case:
```php
use moose\Mapper;
use moose\metadata\AnnotationMetadataProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use function moose\default_coercers;

$reader = new AnnotationReader();
$mapper = new Mapper(new AnnotationMetadataProvider($reader), default_coercers());

$result = $mapper->map(Message::class, $json);
```

There are also decorators for MetadataProvider that allow for proper caching of
 metadata but that's [another story](caching-metadata).

#### Caching metadata

There are two independent metadata providers: `AnnotationMetadataProvider` and
 `CacheMetadataProvider`. In order to cache metadata you need to wire them together
 either with `ProdCacheMetadataProvider` or with `InvalidatingAnnotationMetadataProvider`.
 They have different behavior in a way that `ProdCacheMetadataProvider` will always use
 metadata from cache if it's present there (otherwise it will create it and store in cache),
 whereas `InvalidatingAnnotationMetadataProvider` will always check if cache needs to be
 warmed up first. It's natural to use the former in production and the latter in development
 mode, although you could use invalidating provider for both of them as `stat()` syscalls
 are not that expensive.
