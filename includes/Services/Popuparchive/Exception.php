<?php
/**
 * Exception.php contains the exception classes for the for the Pop Up Archive SDK (PHP >=5.2)
 *
 * @category  File
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      http://github.com/popuparchive/pua-api-php52
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @package   Popuparchive_Services
 */

/**
 * Invalid HTTP response code exception class
 *
 * @category  Services
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Invalid_Http_Response_Code_Exception extends Exception
{

    /**
     * HTTP response body.
     *
     * @access protected
     *
     * @var string
     */
    protected $httpBody;

    /**
     * HTTP response code.
     *
     * @access protected
     *
     * @var integer
     */
    protected $httpCode;

    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The requested URL responded with HTTP code %d.';


    /**
     * Constructor.
     *
     *
     * @return void
     * @param string  $message  (optional) Message that is displayed to the developer on Exception
     * @param string  $code     (optional) Exception code to display (default=0)
     * @param string  $httpBody (optional) HTTP Body (optional)
     * @param integer $httpCode (optional) HTTP Code (default=0)
     */
    function __construct($message = null, $code = 0, $httpBody = null, $httpCode = 0) {
        $this->httpBody = $httpBody;
        $this->httpCode = $httpCode;
        $message = sprintf($this->message, $httpCode);

        parent::__construct($message, $code);
    }


    /**
     * Get HTTP response body.
     *
     * @access public
     *
     * @return mixed
     */
    public function getHttpBody() {
        return $this->httpBody;
    }


    /**
     * Get HTTP response code.
     *
     * @access public
     *
     * @return mixed
     */
    public function getHttpCode() {
        return $this->httpCode;
    }


}


/**
 * Popuparchive unsupported response format exception.
 *
 * @category  Services
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Unsupported_Response_Format_Exception extends Exception
{

    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The given response format is unsupported.';

}


/**
 * Popuparchive unsupported audio format exception.
 *
 * @category  Services
 * @package   Popuparchive_Services
 * @author    Thomas Crenshaw <thomascrenshaw@gmail.com>
 * @copyright 2014 Pop Up Archive <info@popuparchive.org>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Unsupported_Audio_Format_Exception extends Exception
{

    /**
     * Default message.
     *
     * @access protected
     *
     * @var string
     */
    protected $message = 'The given audio format is unsupported.';

}
