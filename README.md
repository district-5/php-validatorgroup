# PHP ValidatorGroup
Validator group for validating form and json body data.

## Usage

### Validation Group
Create a class and extend `\District5\Validator\Group`, adding fields to be validated against.
```php
/**
 * Creates a new instance of AuthValidationGroup
 */
public function __construct()
{
    $stringTrim = new StringTrim();

    $this->addField('username', array(new StringLength(array('min' => 4, 'max' => 254))), array($stringTrim), true);
    $this->addField('password', array(new StringLength(array('min' => 6, 'max' => 64))), array($stringTrim), true);
    $this->addField('device_id', array(new StringLength(array('min' => 6, 'max' => 64))), array(), true);
}
```

### Validating
This can then be used in a route for example with the following code:
```php
$request = $app->request();

$vg = new \Project\Validate\Group\AuthAuthValidationGroup();
```

The validation group can be used to validate against JSON data or a Slim framework request for form data:
```php
$valid = $vg->isValidJSON(json_decode($request->getBody()));

$valid = $vg->isValidSlimPostOrPutRequest($request);
```

### Errors
If validation failed, the validation group will give you some hint as to why it failed by calling:
```php
$vg->getLastErrorMessage();
```