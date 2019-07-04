<?php

/**
 * District5 - Validator Group
 *
 * @copyright District5
 *
 * @author District5 Digital
 * @link https://www.district5.co.uk
 *
 * @license This software and associated documentation (the "Software") may not be
 * used, copied, modified, distributed, published or licensed to any 3rd party
 * without the written permission of District5 or its author.
 *
 * The above copyright notice and this permission notice shall be included in
 * all licensed copies of the Software.
 */
namespace District5\Validator;

/**
 * Group
 *
 * A group for grouping data validators and filters, allowing them to be run on data object(s)
 *
 * @author Mark Morgan <mark.morgan@district5.co.uk>
 */
abstract class Group
{

    /**
     * @var array
     */
    protected $_fields = array();

    /**
     * @var bool
     */
    protected $_validated = false;

    /**
     * @var string
     */
    protected $_lastErrorMessage = '';

    /**
     * @var int
     */
    protected $_minimumRequiredFields = 0;
    
    /**
     * Adds a field
     *
     * @param string $fieldId The id of the field
     * @param array $validators Validators (Optional)
     * @param array $filters Filters (Optional)
     * @param bool $required Flag indicating whether this field is required
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException
     */
    protected function addField($fieldId, $validators = array(), $filters = array(), $required = true)
    {
        if (array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to add field, it is already declared inside this group');
        }
        
        $this->_fields[$fieldId] = array(
            'r' => $required,
            'v' => $validators,
            'f' => $filters,
            's' => false,
            'is_file' => false
        );
        
        return $this;
    }

    /**
     * Adds a field
     *
     * @param string $fieldId The id of the field
     * @param array $validators Validators (Optional)
     * @param array $filters Filters (Optional)
     * @param bool $required Flag indicating whether this field is required
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException
     */
    protected function addPasswordField($fieldId, $validators = array(), $filters = array(), $required = true)
    {
        if (array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to add field, it is already declared inside this group');
        }

        $this->_fields[$fieldId] = array(
            'r' => $required,
            'v' => $validators,
            'f' => $filters,
            's' => true,
            'is_file' => false
        );

        return $this;
    }

    /**
     * Adds a field that supports a file upload
     *
     * @param $fieldId
     * @param array $validators
     * @param array $filters
     * @param bool $required
     *
     * @@return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException
     */
    protected function addFileUploadField($fieldId, $validators = array(), $filters = array(), $required = true)
    {
        if (array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to add field, it is already declared inside this group');
        }

        $this->_fields[$fieldId] = array(
            'r' => $required,
            'v' => $validators,
            'f' => $filters,
            's' => false,
            'is_file' => true
        );

        return $this;
    }

    /**
     * Gets the last error that occurred in this validation group
     *
     * @return string The last error message
     */
    public function getLastErrorMessage()
    {
    	return $this->_lastErrorMessage;
    }

    /**
     * Allows manual setting of last error message for validation that happens outside this group, but requires that the
     * rest of a function that depends on this can run as normal
     *
     * @param string $errorMessage
     */
    public function setLastErrorMessage($errorMessage)
    {
        $this->_lastErrorMessage = $errorMessage;
    }

    /**
     * Gets a processed value given the field id
     *
     * @param string $fieldId The field id
     * @param mixed $defaultValue The default value to return if no field was found with the given id
     *            
     * @return mixed The value for the specified field, the $defaultValue if no field found, null if no field found and
     * no $defaultValue specified
     */
    public function getValue($fieldId, $defaultValue = null)
    {
        if (array_key_exists($fieldId, $this->_fields) && isset($this->_fields[$fieldId]))
        {
            $field = $this->_fields[$fieldId];
            
            if (array_key_exists('fv', $field))
                return $field['fv'];
            
            if (array_key_exists('ov', $field))
                return $field['ov'];
        }
        
        return $defaultValue;
    }

    /**
     * Checks whether this json is valid against the population data given
     *
     * @param array $data The data to validate against, in a json_decode format (stdobject)
     *
     * @param bool $debug
     * @return bool True if the data is valid, false otherwise
     */
    public function isValidJSON($data, $debug = false)
    {
        $dataHandler  = new \District5\Utility\AnonymousClass();
        $dataHandler->dataSource = $data;
        $dataHandler->hasValue = function($name) use($dataHandler)
        {
            return isset($dataHandler->dataSource->$name);
        };
        $dataHandler->getValue = function($name) use($dataHandler)
        {
            return $dataHandler->dataSource->$name;
        };

        return $this->_doIsValid($dataHandler, $debug);
    }

    /**
     * Checks whether this form is valid against the population data given
     *
     * @param \Slim\Http\Request $request
     * @param bool $debug
     *
     * @return bool
     */
    public function isValidSlimPostOrPutRequest($request, $debug = false)
    {
        if (!($request->isPost() || $request->isPut()))
            return false;

        $dataHandler  = new \District5\Utility\AnonymousClass();
        $dataHandler->dataSource = $request;
        $dataHandler->hasValue = function($name) use($dataHandler)
        {
            $param = $dataHandler->dataSource->post($name);
            return (null !== $param && $param != "");
        };
        $dataHandler->getValue = function($name) use($dataHandler)
        {
            return $dataHandler->dataSource->post($name);
        };

        return $this->_doIsValid($dataHandler, $debug);
    }

    /**
     * @param \District5\Utility\AnonymousClass $dataHandler
     * @param bool $debug
     * @return bool
     */
    protected function _doIsValid($dataHandler, $debug = false)
    {
        $foundRequiredField = false;
        $numberOfProvidedValues = 0;

        foreach ($this->_fields as $fieldId => $value)
        {
            $filters = $value['f'];
            $validators = $value['v'];
            $required = $value['r'];
            $isFile = $value['is_file'];

            if ($isFile === false)
            {
                $fieldHasValue = $dataHandler->hasValue($fieldId);
            }
            else
            {
                $fieldHasValue = array_key_exists($fieldId, $_FILES);
//                $fileMeta = $_FILES[$fieldId];
//                if (is_array($fileMeta['error']) === true)
//                {
//                    throw new \Exception('Multiple files per field is not currently supported');
//                }
            }

            if ($required === true && $fieldHasValue === false)
            {
                // required field but it is not set
                $this->_lastErrorMessage = 'Missing required field "' . $fieldId . '"';
                return false;
            }

            if ($required === true)
            {
                $foundRequiredField = true;
            }

            if ($fieldHasValue === true)
            {
                // store original value and filtered value
                if ($isFile === false)
                {
                    $fieldValue = $dataHandler->getValue($fieldId);
                }
                else
                {
                    $fieldValue = new \District5\FileUpload\DTO\FormSingleFileUpload($fieldId);
                }

                $this->_fields[$fieldId]['ov'] = $fieldValue;
                $filteredValue = $fieldValue;

                /* @var $filter \District5\Filter\I */
                foreach ($filters as $filter)
                {
                    $filteredValue = $filter->filter($filteredValue);
                }

                $this->_fields[$fieldId]['fv'] = $filteredValue;

                $toValidate = $filteredValue;

                /* @var $validator \District5\Validator\I */
                foreach ($validators as $validator)
                {
                    if (! $validator->isValid($toValidate))
                    {
                        $this->_lastErrorMessage = (null == $validator->getLastErrorMessage() || '' == $validator->getLastErrorMessage()) ? 'The field "' . $fieldId . '" has been set but fails validation' : $validator->getLastErrorMessage();
                        if ($debug === true)
                        {
                            $this->_lastErrorMessage .= ' ::debug:: ' . (string)$toValidate;
                        }

                        return false;
                    }
                }

                $numberOfProvidedValues++;
            }
        }

        if ($foundRequiredField === false && $numberOfProvidedValues < $this->_minimumRequiredFields)
        {
            $this->_lastErrorMessage = 'At least ' . $this->_minimumRequiredFields . ' field(s) are required';
            return false;
        }

        $postHookValid = $this->_doPostHookIsValid();
        if ($postHookValid === false)
        {
            return false;
        }

        return true;
    }

    /**
     * This function should be overridden if you need to perform work on the validation fields after basic validation has
     * completed, but before the validation group is accessible to outside classes
     */
    protected function _doPostHookIsValid()
    {
        // Nothing to do here, will be called and if utilising class overrides it will be used
        return true;
    }

    /**
     * Modifies an existing field
     *
     * @param string $fieldId The existing field id
     * @param array $validators The validators to replace with (Optional)
     * @param array $filters The filters to replace with (Optional)
     * @param bool $required A flag indicating whether the field is required (Optional)
     * @param array $requiresOtherFields Id's of fields that this field requires (Optional)
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyField($fieldId, $validators = null, $filters = null, $required = null, $requiresOtherFields = null)
    {
        if (!array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to modify field "' . $fieldId . '", it has not been previously declared in this group');
        }

        $currentField = $this->_fields[$fieldId];

        if (null !== $validators && is_array($validators))
            $currentField['v'] = $validators;

        if (null !== $filters && is_array($filters))
            $currentField['f'] = $filters;

        if (null !== $required && is_bool($required))
            $currentField['r'] = $required;

        if (null !== $requiresOtherFields && is_array($requiresOtherFields))
            $currentField['rof'] = $requiresOtherFields;

        $this->_fields[$fieldId] = $currentField;

        return $this;
    }

    /**
     * Modifies an existing field to make it optional (regardless of whether it was already
     * optional or not)
     *
     * @param string $fieldId The existing field id
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyFieldMakeOptional($fieldId)
    {
        if (!array_key_exists($fieldId, $this->_fields))
            throw new \InvalidArgumentException('Unable to modify field "' . $fieldId . '", it has not been previously declared in this group');

        $currentField = $this->_fields[$fieldId];
        $currentField['r'] = false;

        $this->_fields[$fieldId] = $currentField;

        return $this;
    }

    /**
     * Modifies an existing field to make it mandatory (regardless of whether it was already
     * mandatory or not)
     *
     * @param string $fieldId The existing field id
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyFieldMakeMandatory($fieldId)
    {
        if (!array_key_exists($fieldId, $this->_fields))
            throw new \InvalidArgumentException('Unable to modify field "' . $fieldId . '", it has not been previously declared in this group');

        $currentField = $this->_fields[$fieldId];
        $currentField['r'] = true;

        $this->_fields[$fieldId] = $currentField;

        return $this;
    }

    /**
     * Appends a filter to the list of filters
     *
     * @param string $fieldId The existing field id
     * @param \District5\Filter\I $filter The filter to append to the list of filters
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function appendFilterToFilters($fieldId, $filter)
    {
        if (!array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to append filter to field "' . $fieldId . '", it has not been previously declared in this group');
        }

        $currentField = $this->_fields[$fieldId];

        if (array_key_exists('f', $currentField))
        {
            $filters = $currentField['f'];
        }
        else
        {
            $filters = [];
        }

        $filters[] = $filter;
        $currentField['f'] = $filters;

        $this->_fields[$fieldId] = $currentField;

        return $this;
    }

    /**
     * Presents all eligible fields as string ready to echo into a script tag, as an array of objects
     *
     * @return string
     */
    public function asJS()
    {
        $toReturn = array();

        foreach ($this->_fields as $fieldName => $fieldValue)
        {
            $isFile = $fieldValue['is_file'];
            if ($isFile === true)
            {
                continue;
            }

            $isSecure = $fieldValue['s'];
            if ($isSecure === true)
            {
                continue;
            }

            if (!array_key_exists('fv', $fieldValue))
            {
                continue;
            }

            $filteredValue = $fieldValue['fv'];
            if ($filteredValue === null)
            {
                continue;
            }

            $toReturn[] = array($fieldName => $filteredValue);
        }

        return json_encode((array)$toReturn);
    }

    /**
     * Prepends a filter to the list of filters
     *
     * @param string $fieldId The existing field id
     * @param \District5\Filter\I $filter The filter to prepend to the list of filters
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function prependFilterToFilters($fieldId, $filter)
    {
        if (!array_key_exists($fieldId, $this->_fields))
        {
            throw new \InvalidArgumentException('Unable to prepend filter to field "' . $fieldId . '", it has not been previously declared in this group');
        }

        $currentField = $this->_fields[$fieldId];

        if (array_key_exists('f', $currentField))
        {
            $filters = $currentField['f'];
        }
        else
        {
            $filters = [];
        }

        array_unshift($filters, $filter);
        $currentField['f'] = $filters;

        $this->_fields[$fieldId] = $currentField;

        return $this;
    }

    /**
     * Removes an existing field
     *
     * @param string $fieldId The existing field id
     *
     * @return \District5\Validator\Group Provides a fluent interface
     */
    public function removeField($fieldId)
    {
        if (!array_key_exists($fieldId, $this->_fields))
            throw new \InvalidArgumentException('Unable to remove field "' . $fieldId . '", it has not been previously declared in this group');

        unset($this->_fields[$fieldId]);

        return $this;
    }

    /**
     * Sets the minimum number of required fields.
     *
     * @param int $x The number of fields to require
     *
     * @return \District5\Validator\Group Provides a fluent interface
     *
     * @throws \InvalidArgumentException If x is not numeric
     */
    public function requiresAtLeastXFields($x)
    {
        if (!is_numeric($x))
            throw new \InvalidArgumentException('requiresAtLeastXFields needs a numeric value');

        $this->_minimumRequiredFields = $x;

        return $this;
    }
}