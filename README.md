Moose
=====

<image src="/moose64.png" align="left" />

L[oose] object [M]apper - it maps data on objects, failing only gracefully.
 Instead of throwing an exception when some piece of data isn't right it will
 just return to you a stack of collected errors and a partial object.

This is useful for consuming 3d-party APIs or building your own
 where you need to report all invalid pieces of data.

 1. [Install](#installing)
 2. [Use](#how-to-use-it)
 3. [Extend](#add-new-data-types)

___

### Installing

You can add this library to your project with `composer require foorg/moose`.

### How to use it


The most convenient way of using it is with `AnnotationMetadataProvider`.
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

This library comes with a number of predefined types, here's a list of them:
 * `ArrayField([T: TypeRef])` (if you don't want homogeneous array you can omit the `T` parameter)
 * `DateField(format)`
 * `MapField([K: TypeRef, V: TypeRef]|[V: TypeRef])`
 * `ObjectField(classname: string)`
 * `BoolField()`
 * `FloatField()`
 * `IntField()`
 * `StringField()`
 * `TaggedUnionField(tag: string, map: { tag: string => typeOrClassname: string|TypeRef })`

`TypeRef` stands for another (nested) annotation of `Field` type, e.g.
 `ArrayField(T=IntField())` (there is no limit on nesting levels)
 
By the way, types that expect only one parameter (that is `ArrayField`,
 `DateField`, `MapField(V)` and `ObjectField`) can be instantiated like so:
 `ArrayField(IntField())` (no name needed for the first parameter)

`TaggedUnion` allows you to have a field that can contain any of the
 listed types. Here's how to make it work:
```php
class Event
{
    /**
     * @StringField()
     **/
    public $name;

    /**
     * @TaggedUnionField("type", {
     *   "payment" => "PaymentObject",
     *   "withdrawal" => "WithdrawalObject",
     *   "unknown" => MapField()
     * })
     **/
    public $payload;
}
```

This configuration will be able map data of the form:
```
{
  "name": "any name",
  "payload": {"type": "payment", "payment": "object", "fields": "etc"}
}

or

{
  "name": "any name",
  "payload": {"type": "unknown", "anything": "can", "go": "here"}
}
```

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

### Add new data types

If you want to add your own data type, perhaps to replace and extend some of the
 existing or to add a new one, you can do this pretty easily and here's how.

Let's think about a hypothetical situation where we have a json API and there's an
 endpoint where in incoming data there's a `ids` field and it is a list of IDs
 separated with comma, e.g. `ids=1,2,3,4`. As you might guess our existing `ArrayField`
 type would expect it to be `array` but it is in fact a string.

However, we could create our own type that would handle this case gracefully. Note though
 that extending `ArrayField` would probably be better in this case but it wouldn't show
 all the steps of creating a new type.

We'll start from creating our own annotation class:

```php
use moose\annotation\Field;
use moose\annotation\exception\InvalidTypeException;
use function moose\type;

/**
 * @Annotation
 * @Target({"PROPERTY", "ANNOTATION"})
 */
class CommaArrayField extends Field
{
    public $T;

    public $separator;

    public function __construct(array $options)
    {
        if (isset($options["value"])) {
            $options["T"] = $options["value"]; // "value" is always the unnamed first argument of an annotation, if any
        }
        if ( ! isset($options["T"]) || ! $options["T"] instanceof Field) {
            throw new InvalidTypeException(self::class, "T", Field::class, type($options["T"]));
        }
        if ( ! isset($options["separator"])) {
            throw new InvalidTypeException(self::class, "separator", "string", "null");
        }

        parent::__construct($options);
    }

    public function getArgs()
    {
        return [$this->T, $this->separator]; // this will be placed in $metadata->args
    }

    public function getTypeName(): string
    {
        return "comma_array";
    }
}
```

Now we will need to create the mapper itself, but we call it coercer because Moose not only
 maps data but also tries to coerce types to the right ones.

```php
use moose\coercer\TypeCoercer;
use moose\Context;
use moose\ConversionResult;
use moose\error\TypeError;
use moose\metadata\TypeMetadata;
use function moose\type;

class CommaArrayCoercer implements TypeCoercer
{
    public function coerce($value, TypeMetadata $metadata, Context $ctx): ConversionResult
    {
        if ( ! \is_string($value)) {
            return ConversionResult::error(new TypeError("string", type($value)));
        }
        if (\strlen($value) === 0) {
            return ConversionResult::value([]);
        }

        $value = explode($metadata->args[1], $value);

        $errors = [];
        $type = $metadata->args[0]; /** @var TypeMetadata $type */
        $mapped = [];

        foreach ($value as $idx => $v) {
            $result = $ctx->coerce($v, $type);
            if ($result->getErrors()) {
                $errors[] = $result->errorsAtIdx($idx);

                // if some of our values couldn't be coerced completely, we can't say that this array
                // is correct so we bail out and return only errors
                if ($result->getValue() === null) {
                    return ConversionResult::errors(array_merge(...$errors));
                }
            }

            $mapped[] = $result->getValue();
        }

        $errors = $errors ? array_merge(...$errors) : [];

        return ConversionResult::errors($errors, $mapped);
    }
}
```

And now we need to add this type to the coercers that we pass to the `moose\Mapper`:
```php
$coercers = default_coercers() + [
    // "comma_array" = CommaArrayField::getTypeName()
    "comma_array" => new CommaArrayField()
];
$mapper = new Mapper(new AnnotationMetadataProvider($reader), $coercers);
```

Here's how this new annotation can be used in classes:
```php
class IncrementImpressions
{
    /**
     * @CommaArrayField(@IntField(), separator=",")
     **/
    private $ids;

    ...
}
```
