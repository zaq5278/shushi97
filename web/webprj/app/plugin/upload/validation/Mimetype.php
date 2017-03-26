<?php
/**
 * Upload
 */
namespace Plugin\Upload\Validation;

/**
 * Validate Upload Media Type
 *
 * This class validates an upload's media type (e.g. "image/png").
 *
 * @author  MiaoGang
 * @package Upload
 */
class Mimetype extends Base
{
    /**
     * Valid media types
     * @var array
     */
    protected $mimetypes;

    /**
     * Error message
     * @var string
     */
    protected $message = 'Invalid mimetype';

    /**
     * Constructor
     * @param array $mimetypes Array of valid mimetypes
     */
    public function __construct($mimetypes)
    {
        if (!is_array($mimetypes)) {
            $mimetypes = array($mimetypes);
        }
        $this->mimetypes = $mimetypes;
    }

    /**
     * Validate
     * @param  \Upload\File $file
     * @return bool
     */
    public function validate(\Upload\File $file)
    {
        return in_array($file->getMimetype(), $this->mimetypes);
    }
}
