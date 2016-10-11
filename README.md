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
Here's how we can get the object out of json:
```php
use moose\Mapper;
use moose\metadata\AnnotationMetadataProvider;
use Doctrine\Common\Annotations\AnnotationReader;
use function moose\default_coercers;

$reader = new AnnotationReader();
$mapper = new Mapper(new AnnotationMetadataProvider($reader), default_coercers());

$result = $mapper->map(Message::class, $json);
```

There are also decorators for `MetadataProvider` that allow for proper caching of
 metadata but that's [another story](#caching-metadata).

This library comes with a number of types already, here's a list of them:
 * `ArrayField([T: TypeRef])` (if you don't want homogeneous array you can omit the `T` parameter)
 * `DateField(format)`
 * `MapField([K: TypeRef, V: TypeRef]|[V: TypeRef])`
 * `ObjectField(classname: string)`
 * `BoolField()`
 * `FloatField()`
 * `IntField()`
 * `StringField()`

`TypeRef` stands for another (nested) annotation of `Field` type, e.g.
 `ArrayField(T=IntField())` (there is no limit on nesting levels)
 
By the way, types that expect only one parameter (that is `ArrayField`,
 `DateField`, `MapField(V)` and `ObjectField`) can be instantiated like so:
 `ArrayField(IntField())` (no name needed for the first parameter)

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
