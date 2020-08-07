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
namespace District5\FileUpload\DTO;

use \District5\Exception\UploadFileMetaNotFoundException;

/**
 * FormSingleFileUpload
 *
 * A DTO for a form file upload
 *
 * @author Mark Morgan
 *
 * @see http://php.net/manual/en/features.file-upload.post-method.php
 */
class FormSingleFileUpload
{

    protected $_errorCode = null;
    protected $_fileSizeInfo = null;
    protected $_meta = null;
    protected $_mimeTypeHint = null;
    protected $_originalFileName = null;
    protected $_pathInfo = null;
    protected $_tempFileName = null;
    protected $_sizeBytes = null;

    /**
     * FormSingleFileUpload constructor.
     * @param $fieldName
     * @throws UploadFileMetaNotFoundException
     */
    public function __construct($fieldName)
    {
        if (!array_key_exists($fieldName, $_FILES))
        {
            throw new UploadFileMetaNotFoundException('Unable to find meta for file with field name "' . $fieldName . '"');
        }

        $meta = $_FILES[$fieldName];

        $this->_meta = $meta;
        $this->_errorCode = $meta['error'];
        $this->_mimeTypeHint = $meta['type'];
        $this->_originalFileName = $meta['name'];
        $this->_sizeBytes = $meta['size'];
        $this->_tempFileName = $meta['tmp_name'];
    }

    /**
     * Gets the image dimensions
     *
     * @return array width = [0], height = [1]
     */
    public function getDimensions()
    {
        if ($this->_fileSizeInfo === null)
        {
            $this->_fileSizeInfo = @getimagesize($this->_tempFileName);
        }

        return $this->_fileSizeInfo;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->_errorCode;
    }

    /**
     * Gets the extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->getPathInfo()['extension'];
    }

    /**
     * Gets the image height
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->getDimensions()[1];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->_meta;
    }

    /**
     * @return string
     */
    public function getMimeTypeHint()
    {
        return $this->_mimeTypeHint;
    }

    /**
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->_originalFileName;
    }

    /**
     * Gets the path info
     *
     * @return array
     */
    public function getPathInfo()
    {
        if ($this->_pathInfo === null)
        {
//            $this->_pathInfo = pathinfo($this->_tempFileName);
            $this->_pathInfo = pathinfo($this->getOriginalFileName());
        }

        return $this->_pathInfo;
    }
    /**
     * @return int
     */
    public function getSizeBytes()
    {
        return $this->_sizeBytes;
    }

    /**
     * @return string
     */
    public function getTempFileName()
    {
        return $this->_tempFileName;
    }

    /**
     * Gets the image width
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->getDimensions()[0];
    }
}
