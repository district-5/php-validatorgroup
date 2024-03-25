# PHP ValidatorGroup
Validator group for validating form and json body data.

## Usage

### Validation Group
Create a class and extend `\District5\ValidatorGroup\Group`, adding fields to be validated against.
```php
/**
 * Creates a new instance of AuthValidationGroup
 */
public function __construct()
{
    $stringTrim = new StringTrim();

    $this->addField('username', [new StringLength(['min' => 4, 'max' => 254])], [$stringTrim], true);
    $this->addField('password', [new StringLength(['min' => 6, 'max' => 64])], [$stringTrim], true);
    $this->addField('device_id', [new StringLength(['min' => 6, 'max' => 64])], [], true);
}
```

### Validating
This can then be used in a route, with a handler, for example with the following code:
```php
$request = $app->request();

$vg = new \Project\Validate\Group\AuthAuthValidationGroup();
```

The validation group can be used to validate against any custom data with the correct handler; the library as a default ships with a JSON handler:
```php
$handler = new \District5\ValidatorGroup\Handler\JSON($request->getBody());
$valid = $vg->isValid($handler);
```

### Custom Handlers
You can create your own handlers by implementing the `\District5\ValidationGroup\Handler\HandlerInterface` and passing this into the `ValidationGroup`.

### Errors
If validation failed, the validation group will give you some hint as to why it failed by calling:
```php
$vg->getLastErrorMessage();
```