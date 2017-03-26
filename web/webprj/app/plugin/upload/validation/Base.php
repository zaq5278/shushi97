<?php
/**
 * Upload
 */
namespace Plugin\Upload\Validation;

/**
 * Upload Validation Base
 */
abstract class Base
{
    /**
     * The error message for this validation
     * @var string
     */
    protected $message;

    /**
     * Set error message
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Get error message
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Validate file
     * @param  $file
     * @return bool True if file is valid, false if file is not valid
     */
    abstract public function validate($file);
}
