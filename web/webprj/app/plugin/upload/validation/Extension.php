<?php
/**
 * Upload
 */
namespace Plugin\Upload\Validation;

/**
 * Validate File Extension
 * @author  MiaoGang
 * @package Upload
 */
class Extension extends Base
{
    /**
     * Array of cceptable file extensions without leading dots
     * @var array
     */
    protected $allowedExtensions;

    /**
     * Error message
     * @var string
     */
    protected $message = 'Invalid file extension. Must be one of: %s';

    /**
     * Constructor
     *
     * @param string|array $allowedExtensions Allowed file extensions
     * @example new \Upload\Validation\Extension(array('png','jpg','gif'))
     * @example new \Upload\Validation\Extension('png')
     */
    public function __construct($allowedExtensions)
    {
        if (is_string($allowedExtensions)) {
            $allowedExtensions = array($allowedExtensions);
        }

        array_filter($allowedExtensions, function ($val) {
            return strtolower($val);
        });

        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * Validate
     * @param  \Upload\File $file
     * @return bool
     */
    public function validate(\Upload\File $file)
    {
        $fileExtension = strtolower($file->getExtension());
        $isValid = true;

        if (!in_array($fileExtension, $this->allowedExtensions)) {
            $this->setMessage(sprintf($this->message, implode(', ', $this->allowedExtensions)));
            $isValid = false;
        }

        return $isValid;
    }
}
