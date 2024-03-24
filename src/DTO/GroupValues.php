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

class GroupValues
{
    private mixed $originalValue;
    private mixed $filteredValue;

    public function __construct(mixed $originalValue = null, mixed $filteredValue = null)
    {
        if (null !== $originalValue) {
            $this->originalValue = $originalValue;
        }

        if (null !== $filteredValue) {
            $this->filteredValue = $filteredValue;
        }
    }

    /**
     * @return mixed
     */
    public function getOriginalValue(): mixed
    {
        return $this->originalValue;
    }

    /**
     * @return mixed
     */
    public function getFilteredValue(): mixed
    {
        return $this->filteredValue;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setOriginalValue(mixed $value): void
    {
        $this->originalValue = $value;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setFilteredValue(mixed $value): void
    {
        $this->filteredValue = $value;
    }
}
