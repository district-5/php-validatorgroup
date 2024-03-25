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
namespace District5\ValidatorGroup\DTO;

class GroupItem
{
    private string $fieldId;
    private bool $required;
    private array $validators;
    private array $filters;
    private array $requiredOtherFields;
    private bool $secure;

    private GroupValues $values;

    public function __construct(string $fieldId, bool $required, array $validators, array $filters, bool $secure)
    {
        $this->fieldId = $fieldId;
        $this->required = $required;
        $this->validators = $validators;
        $this->filters = $filters;
        $this->secure = $secure;

        $this->requiredOtherFields = [];
    }

    /**
     * @return string
     */
    public function getFieldId(): string
    {
        return $this->fieldId;
    }

    /**
     * @return bool
     */
    public function getRequired(): bool
    {
        return $this->required;
    }

    /**
     * @return array
     */
    public function getRequiredOtherFields(): array
    {
        return $this->requiredOtherFields;
    }

    /**
     * @return array
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return bool
     */
    public function getSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return GroupValues|null
     */
    public function getValues(): ?GroupValues
    {
        return $this->values;
    }

    /**
     * @param bool $required
     * @return void
     */
    public function setRequired(bool $required): void
    {
        $this->required = $required;
    }

    /**
     * @param array $requiredOtherFields
     * @return void
     */
    public function setRequiredOtherFields(array $requiredOtherFields): void
    {
        $this->requiredOtherFields = $requiredOtherFields;
    }

    /**
     * @param array $validators
     * @return void
     */
    public function setValidators(array $validators): void
    {
        $this->validators = $validators;
    }

    /**
     * @param array $filters
     * @return void
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    /**
     * @param GroupValues $values
     * @return void
     */
    public function setValues(GroupValues $values): void
    {
        $this->values = $values;
    }
}
