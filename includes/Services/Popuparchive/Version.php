<?php
/**
 * Version.php contains the Popuparchive Services Version class
 *
 * @category  File
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      http://github.com/popuparchive/pua-api-php52
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @package   Popuparchive_Services
 */

/**
 * Popuparchive package version
 *
 * @category  Services
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      http://github.com/popuparchive/pua-api-php52
 */
class Popuparchive_Services_Version {
    const MAJOR = 1;
    const MINOR = 0;
    const PATCH = 0;

    /**
     * Magic to string method
     *
     *
     * @access public
     * @return string
     */
    public function __toString() {
        return implode('.', array(self::MAJOR, self::MINOR, self::PATCH));
    }


}
