<?php

/**
 * District5 Validator Group Library
 *
 * @author      District5 <hello@district5.co.uk>
 * @copyright   District5 <hello@district5.co.uk>
 * @link        https://www.district5.co.uk
 *
 *  MIT LICENSE
 *
 *   Permission is hereby granted, free of charge, to any person obtaining
 *   a copy of this software and associated documentation files (the
 *   "Software"), to deal in the Software without restriction, including
 *   without limitation the rights to use, copy, modify, merge, publish,
 *   distribute, sublicense, and/or sell copies of the Software, and to
 *   permit persons to whom the Software is furnished to do so, subject to
 *   the following conditions:
 *
 *   The above copyright notice and this permission notice shall be
 *   included in all copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 *   EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *   MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 *   NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 *   LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 *   OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 *   WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace District5\ValidatorGroup;

use District5\Filter\FilterInterface;
use District5\Validator\ValidatorInterface;
use District5\ValidatorGroup\DTO\GroupItem;
use District5\ValidatorGroup\DTO\GroupValues;
use District5\ValidatorGroup\Handler\HandlerInterface;

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
    protected array $fields = [];

    /**
     * @var bool
     */
    protected bool $_validated = false;

    /**
     * @var string|null
     */
    protected ?string $_lastErrorMessage = null;

    /**
     * @var int
     */
    protected int $minimumRequiredFields = 0;
    
    /**
     * Adds a field.
     *
     * @param string $fieldId The id of the field
     * @param array $validators Validators (Optional)
     * @param array $filters Filters (Optional)
     * @param bool $required Flag indicating whether this field is required
     *
     * @throws \InvalidArgumentException
     */
    protected function addField(string $fieldId, array $validators = [], array $filters = [], bool $required = true): void
    {
        if (array_key_exists($fieldId, $this->_fields)) {
            throw new \InvalidArgumentException('Unable to add field, it is already declared inside this group');
        }
        
//        $this->_fields[$fieldId] = array(
//            'r' => $required,
//            'v' => $validators,
//            'f' => $filters,
//            's' => false
//        );
        $this->fields[$fieldId] = new GroupItem(
            $fieldId,
            $required,
            $validators,
            $filters,
            false
        );
    }

    /**
     * Adds a password field.
     *
     * @param string $fieldId The id of the field
     * @param array $validators Validators (Optional)
     * @param array $filters Filters (Optional)
     * @param bool $required Flag indicating whether this field is required
     *
     * @throws \InvalidArgumentException
     */
    protected function addPasswordField(string $fieldId, array $validators = [], array $filters = [], bool $required = true): void
    {
        if (array_key_exists($fieldId, $this->_fields)) {
            throw new \InvalidArgumentException('Unable to add field, it is already declared inside this group');
        }

//        $this->_fields[$fieldId] = array(
//            'r' => $required,
//            'v' => $validators,
//            'f' => $filters,
//            's' => true
//        );
        $this->fields[$fieldId] = new GroupItem(
            $fieldId,
            $required,
            $validators,
            $filters,
            true
        );
    }

    /**
     * Gets the last error that occurred in this validation group
     *
     * @return string|null The last error message
     */
    public function getLastErrorMessage(): ?string
    {
    	return $this->_lastErrorMessage;
    }

    /**
     * Allows manual setting of last error message for validation that happens outside this group, but requires that the
     * rest of a function that depends on this can run as normal
     *
     * @param string $errorMessage
     */
    public function setLastErrorMessage(string $errorMessage): void
    {
        $this->_lastErrorMessage = $errorMessage;
    }

    /**
     * Gets a processed value given the field id.
     *
     * @param string $fieldId The field id
     * @param mixed $defaultValue The default value to return if no field was found with the given id
     *            
     * @return mixed The value for the specified field, the $defaultValue if no field found, null if no field found and
     * no $defaultValue specified
     */
    public function getValue(string $fieldId, mixed $defaultValue = null): mixed
    {
        if (isset($this->fields[$fieldId])) {

            /** @var GroupItem $field */
            $field = $this->fields[$fieldId];
            $values = $field->getValues();

            if (null !== $values) {
                // some values have been stored
                if (null !== $filteredValue = $values->getFilteredValue()) {
                    // a filtered value has been set
                    return $filteredValue;
                }

                if (null !== $originalValue = $values->getOriginalValue()) {
                    // an original value has been set
                    return $originalValue;
                }
            }
        }
        
        return $defaultValue;
    }

    /**
     * Checks if the data provided by the given handler is valid against the requirements of this validation group.
     *
     * @param HandlerInterface $handler
     * @param bool $debug
     *
     * @return bool
     */
    public function isValid(HandlerInterface $handler, bool $debug = false): bool
    {
        $foundRequiredField = false;
        $numberOfProvidedValues = 0;

        foreach ($this->fields as $fieldId => $fieldGroup) {
            /** @var GroupItem $fieldGroup */

            $filters = $fieldGroup->getFilters();
            $validators = $fieldGroup->getValidators();
            $required = $fieldGroup->getRequired();

            $fieldHasValue = $handler->hasValue($fieldId);

            if (true === $required && false === $fieldHasValue) {
                // required field but it is not set
                // TODO: fix this
                $this->_lastErrorMessage = 'Missing required field "' . $fieldId . '"';
                return false;
            }

            if (false === $fieldHasValue) {
                // optional field and doesn't have a value
                continue;
            }

            $foundRequiredField = true;

            $fieldValue = $handler->getValue($fieldId);

            $filteredValue = $fieldValue;

            /* @var $filter FilterInterface */
            foreach ($filters as $filter) {
                $filteredValue = $filter->filter($filteredValue);
            }

            $values = new GroupValues($fieldValue, $filteredValue);
            $fieldGroup->setValues($values);
//            $this->_fields[$fieldId]['ov'] = $fieldValue;
//            $this->_fields[$fieldId]['fv'] = $filteredValue;

//            $toValidate = $filteredValue;

            /* @var $validator ValidatorInterface */
            foreach ($validators as $validator) {

                if (!$validator->isValid($filteredValue)) {

                    // TODO: fix this
                    $this->_lastErrorMessage = (null == $validator->getLastErrorMessage() || '' == $validator->getLastErrorMessage()) ? 'The field "' . $fieldId . '" has been set but fails validation' : $validator->getLastErrorMessage();
                    if ($debug === true) {
                        $this->_lastErrorMessage .= ' ::debug:: ' . (string)$filteredValue;
                    }

                    return false;
                }
            }

            $numberOfProvidedValues++;
        }

        if (false ===$foundRequiredField && $numberOfProvidedValues < $this->minimumRequiredFields) {
            $this->_lastErrorMessage = 'At least ' . $this->minimumRequiredFields . ' field(s) are required';
            return false;
        }

        $postHookValid = $this->_doPostHookIsValid();
        if (false === $postHookValid) {
            return false;
        }

        return true;
    }

    /**
     * This function should be overridden if you need to perform work on the validation fields after basic validation has
     * completed, but before the validation group is accessible to outside classes
     */
    protected function _doPostHookIsValid(): bool
    {
        // Nothing to do here, will be called and if utilising class overrides it will be used
        return true;
    }

    /**
     * Modifies an existing field
     *
     * @param string $fieldId The existing field id
     * @param array|null $validators The validators to replace with (Optional)
     * @param array|null $filters The filters to replace with (Optional)
     * @param bool|null $required A flag indicating whether the field is required (Optional)
     * @param array|null $requiredOtherFields Id's of fields that this field requires (Optional)
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyField(string $fieldId, ?array $validators = null, ?array $filters = null, ?bool $required = null, ?array $requiredOtherFields = null): void
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('Unable to modify field "' . $fieldId . '", it has not been previously declared in this group');
        }

        /** @var GroupItem $currentField */
        $currentField = $this->fields[$fieldId];

        if (null !== $validators) {
            $currentField->setValidators($validators);
        }

        if (null !== $filters) {
            $currentField->setFilters($filters);
        }

        if (null !== $required) {
            $currentField->setRequired($required);
        }

        if (null !== $requiredOtherFields) {
            $currentField->setRequiredOtherFields($requiredOtherFields);
        }

        $this->fields[$fieldId] = $currentField;
    }

    /**
     * Modifies an existing field to make it optional (regardless of whether it was already
     * optional or not)
     *
     * @param string $fieldId The existing field id
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyFieldMakeOptional(string $fieldId): void
    {
        $this->modifyFieldRequired($fieldId, false);
    }

    /**
     * Modifies an existing field to make it mandatory (regardless of whether it was already
     * mandatory or not)
     *
     * @param string $fieldId The existing field id
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function modifyFieldMakeMandatory(string $fieldId): void
    {
        $this->modifyFieldRequired($fieldId, true);
    }

    /**
     * @param string $fieldId
     * @param bool $required
     *
     * @return void
     */
    private function modifyFieldRequired(string $fieldId, bool $required): void
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('Unable to modify field "' . $fieldId . '", it has not been previously declared in this group');
        }

        /** @var GroupItem $currentField */
        $currentField = $this->fields[$fieldId];

        $currentField->setRequired($required);

        $this->fields[$fieldId] = $currentField;
    }

    /**
     * Appends a filter to the list of filters
     *
     * @param string $fieldId The existing field id
     * @param FilterInterface $filter The filter to append to the list of filters
     *
     * @throws \InvalidArgumentException If an existing field with id fieldId has not been previously set
     */
    public function appendFilterToFilters(string $fieldId, FilterInterface $filter): void
    {
        if (!isset($this->fields[$fieldId])) {
            throw new \InvalidArgumentException('Unable to append filter to field "' . $fieldId . '", it has not been previously declared in this group');
        }

        /** @var GroupItem $currentField */
        $currentField = $this->fields[$fieldId];
        $currentFilters = $currentField->getFilters();

        $currentFilters[] = $filter;
        $currentField->setFilters($currentFilters);

        $this->fields[$fieldId] = $currentField;
    }

    /**
     * Presents all eligible fields as string ready to echo into a script tag, as an array of objects
     *
     * @return string
     */
    public function asJS()
    {
        $toReturn = [];

        foreach ($this->fields as $fieldName => $fieldGroup) {
            /** @var GroupItem $fieldGroup */
            if (true === $fieldGroup->getSecure()) {
                continue;
            }

            $fieldValues = $fieldGroup->getValues();
            if (null === $fieldValues) {
                continue;
            }

            $toReturn[] = array($fieldName => $fieldValues->getFilteredValue());
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

        $this->minimumRequiredFields = $x;

        return $this;
    }
}