<?php
/**
 * Pop Up Archive API SDK written in PHP (>=5.2)
 *
 * @category  File
 * @copyright 2014 Thomas Crenshaw <thomas@circadigital.biz>
 * @license
 * @link      http://github.com/thomascrenshaw/pua-api-php52
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @package   Popuparchive_Services
 */
require_once 'Popuparchive/Exception.php';
require_once 'Popuparchive/Version.php';

/**
 * Pop Up Archive API SDK
 *
 * @category  Services
 * @author    Thomas Crenshaw <thomas@circadigital.biz>
 * @copyright Thomas Crenshaw <thomas@circadigital.biz>
 * @license   GNU AFFERO GENERAL PUBLIC LICENSE <http://www.gnu.org/licenses/agpl.html>
 * @link      http://github.com/popuparchive/pua-api-php52
 *
 * PSR1 - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
 * PSR2 - https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
 */
class Popuparchive_Services {

    /**
     * Custom cURL option
     *
     * @var integer
     *
     * @access public
     */
    const CURLOPT_OAUTH_TOKEN = 1337;

    /**
     * Access token returned by the service provider after a successful authentication
     *
     * @var string
     *
     * @access private
     */
    private $_accessToken;

    /**
     * Version of the API to use
     *
     * @var integer
     *
     * @access private
     * @static
     */
    private static $_apiVersion = 1;

    /**
     * OAuth client id
     *
     * @var string
     *
     * @access private
     */
    private $_clientId;

    /**
     * OAuth client secret
     *
     * @var string
     *
     * @access private
     */
    private $_clientSecret;

    /**
     * Default cURL options
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_curlDefaultOptions = array(
        CURLOPT_HEADER => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => ''
    );

    /**
     * cURL options
     *
     * @var array
     *
     * @access private
     */
    private $_curlOptions;

    /**
     * API domain
     *
     * @todo add development domain when it is launched
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_domain = 'www.popuparchive.com';

    /**
     * OAuth paths
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_paths = array(
        'authorize' => 'oauth/authorize',
        'access_token' => 'oauth/token',
    );

    /**
     * HTTP response body from the last request
     *
     * @var string
     *
     * @access private
     */
    private $_lastHttpResponseBody;

    /**
     * HTTP response code from the last request
     *
     * @var integer
     *
     * @access private
     */
    private $_lastHttpResponseCode;

    /**
     * HTTP response headers from last request
     *
     * @var array
     *
     * @access private
     */
    private $_lastHttpResponseHeaders;

    /**
     * OAuth redirect URI
     *
     * @var string
     *
     * @access private
     */
    private $_redirectUri;

    /**
     * API response format MIME type
     *
     * @var string
     *
     * @access private
     */
    private $_requestFormat;

    /**
     * Available response formats
     *
     * @var array
     *
     * @access private
     * @static
     */
    private static $_responseFormats = array(
        '*' => '*/*',
        'json' => 'application/json'
    );

    /**
     * HTTP user agent
     *
     * @var string
     *
     * @access private
     * @static
     */
    private static $_userAgent = 'SDK-PHP52-Popuparchive';

    /**
     * Class constructor
     *
     *
     * @return void
     * @throws Popuparchive_Services_Missing_Client_Id_Exception
     *
     * @access public
     * @param string  $clientId     OAuth client id
     * @param string  $clientSecret OAuth client secret
     * @param string  $redirectUri  (optional) OAuth redirect URI
     */
    public function __construct($clientId, $clientSecret, $redirectUri = null) {
        if (empty($clientId)) {
            throw new Popuparchive_Services_Missing_Client_Id_Exception();
        }
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
        $this->_responseFormat = self::$_responseFormats['json'];
        $this->_curlOptions = self::$_curlDefaultOptions;
        $this->_curlOptions[CURLOPT_USERAGENT] .= $this->getUserAgent();
    }


    /**
     * Get authorization URL
     *
     * The authorize URL returns the code that is needed to retrieve the access token. Once the code has been returned,
     * a request is made for the access token.
     *
     *
     *
     * @access public
     * @see Popuparchive::buildUrl()
     * @param array   $params (optional) Optional query string parameters
     * @return string
     */
    public function getAuthorizeUrl($params = array()) {
        $defaultParams = array(
            'client_id' => $this->_clientId,
            'redirect_uri' => $this->_redirectUri,
            'response_type' => 'code'
        );
        $params = array_merge($defaultParams, $params);

        return $this->buildUrl(self::$_paths['authorize'], $params, false);
    }


    /**
     * Get access token URL
     *
     * This method uses HTTP headers and POST to retreive the access token. As of the 1.0 release, Pop Up Archive
     * API only requires a POST URL and does not require that headers are sent. Because of this, the getAccessTokenUrl method
     * which includes the request() method has NOT been tested. The only access token method that has been tested is the
     * simpleAccessTokenRequest() method.
     *
     *
     *
     * @access public
     * @see Popuparchive::buildUrl()
     * @param array   $params (optional) Optional query string parameters
     * @return string URL fully formed URL used to retreive the access token.
     */
    public function getAccessTokenUrl($params = array()) {

        return $this->buildUrl(self::$_paths['access_token'], $params, false);
    }


    /**
     * Retrieve access token using just HTTP POST
     *
     * It is possible to retreive a Pop Up Archive access token through a simple
     * POST request. This eliminates the need for headers and cURL. The more
     * "complicated" methods for retreiving RESTful data have been added to
     * help "future proof" this SDK although there will still be some tweaking needed
     * to make it all work together if the PUA API is changed.
     *
     *
     *
     * @access public
     * @see Popuparchive::postRequestAccessToken()
     * @param string  $code (optional) Optional OAuth code returned from the service provider
     * @return mixed
     */
    public function simpleAccessTokenRequest($code = null) {
        $tokenUrl = "https://".self::$_domain.'/'.
            self::$_paths['access_token'].
            '?client_id='.$this->_clientId.
            '&client_secret='.$this->_clientSecret.
            '&redirect_uri='.$this->_redirectUri.
            '&code='.$code.
            '&grant_type=authorization_code';
        return $this->postRequestAccessToken($tokenUrl);
    }


    /**
     * Get access token
     *
     *
     * @access public
     * @return mixed
     */
    public function getAccessToken() {
        return $this->_authCode;
    }


    /**
     * Get API version
     *
     *
     * @access public
     * @return integer
     */
    public function getApiVersion() {
        return self::$_apiVersion;
    }


    /**
     * Get the corresponding MIME type for a given file extension
     *
     *
     * @throws Popuparchive_Services_Unsupported_Audio_Format_Exception
     *
     * @access public
     * @param string  $extension Given extension
     * @return string
     */
    public function getAudioMimeType($extension) {
        if (array_key_exists($extension, self::$_audioMimeTypes)) {
            return self::$_audioMimeTypes[$extension];
        } else {
            throw new Popuparchive_Services_Unsupported_Audio_Format_Exception();
        }
    }


    /**
     * Get cURL options
     *
     *
     *
     * @access public
     * @param string  $key (optional) Optional options key
     * @return mixed
     */
    public function getCurlOptions($key = null) {
        if ($key) {
            return (array_key_exists($key, $this->_curlOptions))
                ? $this->_curlOptions[$key]
                : false;
        } else {
            return $this->_curlOptions;
        }
    }


    /**
     * Get HTTP response header
     *
     *
     *
     * @access public
     * @param string  $header Name of the header
     * @return mixed
     */
    public function getHttpHeader($header) {
        if (is_array($this->_lastHttpResponseHeaders)
            && array_key_exists($header, $this->_lastHttpResponseHeaders)
        ) {
            return $this->_lastHttpResponseHeaders[$header];
        } else {
            return false;
        }
    }


    /**
     * Get redirect URI
     *
     *
     * @access public
     * @return string
     */
    public function getRedirectUri() {
        return $this->_redirectUri;
    }


    /**
     * Get response format
     *
     *
     * @access public
     * @return string
     */
    public function getResponseFormat() {
        return $this->_responseFormat;
    }


    /**
     * Set access token
     *
     *
     *
     * @access public
     * @param string  $accessToken Access token
     * @return object
     */
    public function setAccessToken($accessToken) {
        $this->_accessToken = $accessToken;

        return $this;
    }


    /**
     * Set cURL options
     *
     * The method accepts arguments in two ways.
     *
     * You could pass two arguments when adding a single option.
     * <code>
     * $popuparchive->setCurlOptions(CURLOPT_SSL_VERIFYHOST, 0);
     * </code>
     *
     * You could also pass an associative array when adding multiple options.
     * <code>
     * $popuparchive->setCurlOptions(array(
     *     CURLOPT_SSL_VERIFYHOST => 0,
     *    CURLOPT_SSL_VERIFYPEER => 0
     * ));
     * </code>
     *
     *
     * @access public
     * @return object
     */
    public function setCurlOptions() {
        $args = func_get_args();

        $options = (is_array($args[0]))
            ? $args[0]
            : array($args[0] => $args[1]);

        foreach ($options as $key => $val) {
            $this->_curlOptions[$key] = $val;
        }
        return $this;
    }


    /**
     * Set redirect URI
     *
     * The Pop Up Archive OAuth handler (Doorkeeper) does not allow query parameters
     * in the Redirect URI that is stored with the client application. It does however
     * allow query paramters to be pass through to Pop Up Archive at runtime. When using
     * this method in your client application, be sure to concatenate your applications
     * redirect url (e.g http://mydomain.com/mydirectory/mypage) with any query parameters
     * (e.g. ?return=myreturnvariable) in the application
     *
     *
     *
     * @access public
     * @param string  $redirectUri Redirect URI
     * @return object
     */
    public function setRedirectUri($redirectUri) {
        $this->_redirectUri = $redirectUri;

        return $this;
    }


    /**
     * Set response format
     *
     * 2 response formats are set as part of a private static variable (array)
     *  set at the beginning of this SDK. If more are added in the future
     * (e.g. XML or some new format) just add to the static variable.
     *
     *
     * @throws Popuparchive_Services_Unsupported_Response_Format_Exception
     *
     * @access public
     * @param string  $format Response format
     * @return object
     */
    public function setResponseFormat($format) {
        if (array_key_exists($format, self::$_responseFormats)) {
            $this->_responseFormat = self::$_responseFormats[$format];
        } else {
            throw new Popuparchive_Services_Unsupported_Response_Format_Exception();
        }

        return $this;
    }


    /**
     * Send a GET HTTP request
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param string  $path        Request path
     * @param array   $params      (optional) Optional query string parameters
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    public function get($path, $params = array(), $curlOptions = array()) {
        $url = $this->buildUrl($path, $params);
        return $this->request($url, $curlOptions);
    }


    /**
     * Send a POST HTTP request
     *
     *
     *
     * @access public
     * @see Popuparchive::_request()
     * @param string  $path        Request path
     * @param array   $postData    (optional) Optional post data
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    public function post($path, $postData = array(), $curlOptions = array()) {
        $url = $this->buildUrl($path);
        $options = array(CURLOPT_POST => true, CURLOPT_POSTFIELDS => $postData);
        $options += $curlOptions;
        //echo $url;
        return $this->request($url, $options);
    }


    /**
     * Send a PUT HTTP request
     *
     *
     *
     * @access public
     * @see Popuparchive::_request()
     * @param string  $path        Request path
     * @param array   $postData    Optional post data
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    public function put($path, $postData, $curlOptions = array()) {
        $url = $this->_buildUrl($path);
        $options = array(
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $postData
        );
        $options += $curlOptions;

        echo $url;

        return $this->_request($url, $options);
    }


    /**
     * Send a DELETE HTTP request
     *
     *
     *
     * @access public
     * @see Popuparchive::_request()
     * @param string  $path        Request path
     * @param array   $params      (optional) Optional query string parameters
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    public function delete($path, $params = array(), $curlOptions = array()) {
        $url = $this->_buildUrl($path, $params);
        $options = array(CURLOPT_CUSTOMREQUEST => 'DELETE');
        $options += $curlOptions;

        return $this->_request($url, $options);
    }


    // START SPECIFIC ENDPOINT WRAPPERS

    /**
     *
     *
     * @todo create a single function with switch statement based on the passed in param
     *
     *
     * switch $type
     * case 'public_collections'
     * 'collections/public'
     * break;
     * case 'user_collections'
     * 'collections'
     * break;
     * case 'id_collections'
     * 'collections/'.$collectionId,
     * break;
     * case 'id_items'
     * 'collections/'.$collectionId.'/items/'.$itemId,
     *
     *
     */

    /**
     * Get publically available collections
     *
     * @todo ensure that pagination works on this endpoint once it is implemented
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param unknown $params      (optional)
     * @param unknown $curlOptions (optional)
     * @return JSON
     */
    public function getPublicCollections($params = array(), $curlOptions = array()) {
        $url = $this->buildUrl(
            'collections/public',
            $params
        );

        return $this->request($url, $curlOptions);
    }


    /**
     * Get a user's collections
     *
     *
     * @todo investigate why this is not working in this scenario it has something to do with the headers
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param array   $params      (optional) Optional query string parameters
     * @param array   $curlOptions (optional) Optional cURL options
     * @return JSON
     */
    public function getUserCollections($params = array(), $curlOptions = array()) {
        $url = $this->buildURL(
            'collections',
            $params
        );
        echo $url;
        return $this->request($url, $curlOptions);
    }


    /**
     * Get a Pop Up Archive collection by its ID
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param string  $collectionId Request path
     * @param array   $params       (optional) Optional query string parameters
     * @param array   $curlOptions  (optional) Optional cURL options
     * @return JSON
     */
    public function getCollectionById($collectionId, $params = array(), $curlOptions = array()) {
        $url = $this->buildURL(
            'collections/'.$collectionId,
            $params
        );
        //echo $url;
        return $this->request($url, $curlOptions);
    }


    /**
     * Get a Pop Up Archive collection by its ID
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param array   $collectionIds array of collection ids to get
     * @param array   $params        (optional) Optional query string parameters
     * @param array   $curlOptions   (optional) Optional cURL options
     * @return JSON
     */
    public function getCollectionsByIds($collectionIds, $params = array(), $curlOptions = array()) {
        $collections = explode(',', $collectionIds);

        for ($i=0;$i<count($collections);$i++) {
            //pull out the collection ids and make individual calls
        }
        $url = $this->buildURL(
            'collections/'.$collectionId,
            $params
        );
        //echo $url;
        return $this->request($url, $curlOptions);
    }


    /**
     * Get a Pop Up Archive item by its ID and associated Collection ID
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param string  $collectionId Request path
     * @param string  $itemId       Request path
     * @param array   $params       (optional) Optional query string parameters
     * @param array   $curlOptions  (optional) Optional cURL options
     * @return mixed
     */
    public function getItemById($collectionId, $itemId, $params = array(), $curlOptions = array()) {
        $url = $this->buildURL(
            'collections/'.$collectionId.'/items/'.$itemId,
            $params
        );
        //echo $url;
        return $this->request($url, $curlOptions);
    }


    /**
     * Retreive a the audio items associated with a Pop Up Archive collection
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param string  $collectionId Request path
     * @param array   $params       (optional) Optional query string parameters
     * @param array   $curlOptions  (optional) Optional cURL options
     * @return mixed
     */
    public function getItemsByCollectionId($collectionId, $params = array(), $curlOptions = array()) {
        $url = $this->buildURL(
            'search?query=collection_id:'.$collectionId,
            $params
        );
        echo $url;
        return $this->request($url, $curlOptions);
    }


    /**
     * Search Pop Up Archive, with search set narrowed down by a filter
     *
     *
     *
     * @access public
     * @see Popuparchive::request()
     * @param string  $filterKey   filter type - only filter currently available is 'collection_id'
     * @param string  $filterValue filter value
     * @param array   $params      (optional) Optional query string parameters; format -- array('query'=>'chicago OR american','page'=>"1");
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    public function searchByFilter($filterKey, $filterValue, $params = array(), $curlOptions = array()) {
        $filterArray = array('filters['.$filterKey.']' => $filterValue);

        // @params array|null should contain all query parameters needed for request
        $query = array_merge($filterArray, $params);
        $url = $this->buildURL(
            'search',
            $query
        );
        //echo $url;
        ///print_r($curlOptions);
        //echo $this->request($url, $curlOptions);
        //$url = "search?page=2&filters[collection_id]=514";
        return $this->request($url, $curlOptions);
    }


    /**
     * Fetch url via oembed service.
     *
     * @param string  $url
     * @return object $oembed
     */
    public function getOembed($url) {
        $oembed_url = 'https://' . self::$_domain . '/oembed';
        $resp = $this->get($oembed_url, array('url'=>$url, 'format'=>'json'));
        //error_log('oembed resp:' . var_export($resp, true));
        return json_decode($resp);
    }


    /**
     * Construct default HTTP request headers
     *
     *
     *
     * @access protected
     * @param boolean $includeAccessToken (optional) Include access token
     * @return array $headers
     */
    protected function buildDefaultHeaders($includeAccessToken = true) {
        $headers = array();

        if ($this->_responseFormat) {
            array_push($headers, 'Accept: ' . $this->_responseFormat);
        }

        if ($includeAccessToken && $this->_accessToken) {
            array_push($headers, 'Authorization: Bearer ' . $this->_accessToken);
        }

        return $headers;
    }


    /**
     * Construct a URL
     *
     *
     *
     * @access protected
     * @param string  $path   Relative or absolute URI
     * @param array   $params (optional) Optional query string parameters
     * @return string $url
     */
    protected function buildUrl($path, $params = array()) {
        if (preg_match('/^https?\:\/\//', $path)) {
            $url = $path;
        } else {
            $url = 'https://';
            $url .= self::$_domain;
            $url .='/';
            $url .= (!preg_match('/oauth/', $path)) ? 'api/' : '';
            $url .= $path;
        }

        $url .= (count($params)) ? '?' . http_build_query($params) : '';
        //echo $url;

        return $url;
    }


    /**
     *
     *
     * @param unknown $code
     * @return unknown
     */
    protected function postRequestAccessToken($code) {
        $response = json_decode(
            $this->post($code),
            true
        );
        if (array_key_exists('access_token', $response)) {
            $this->_accessToken = $response['access_token'];

            return $response;
        } else {
            return $response;
        }
    }


    /**
     * Get HTTP user agent
     *
     *
     * @access protected
     * @return string
     */
    protected function getUserAgent() {
        return self::$_userAgent . '/' . new Popuparchive_Services_Version;
    }


    /**
     * Parse HTTP headers
     *
     *
     *
     * @access protected
     * @param string  $headers HTTP headers
     * @return array $parsedHeaders
     */
    protected function parseHttpHeaders($headers) {
        $headers = explode("\n", trim($headers));
        $parsedHeaders = array();

        foreach ($headers as $header) {
            if (!preg_match('/\:\s/', $header)) {
                continue;
            }

            list($key, $val) = explode(': ', $header, 2);
            $key = str_replace('-', '_', strtolower($key));
            $val = trim($val);

            $parsedHeaders[$key] = $val;
        }

        return $parsedHeaders;
    }


    /**
     * Validate HTTP response code
     *
     *
     *
     * @access protected
     * @param integer $code HTTP code
     * @return boolean
     */
    protected function validResponseCode($code) {
        return (bool) preg_match('/^20[0-9]{1}$/', $code);
    }


    /**
     * Performs the actual HTTP request using cURL
     *
     *
     * @throws Popuparchive_Services_Invalid_Http_Response_Code_Exception
     *
     * @access protected
     * @param string  $url         Absolute URL to request
     * @param array   $curlOptions (optional) Optional cURL options
     * @return mixed
     */
    protected function request($url, $curlOptions = array()) {
        $curlinit = curl_init($url);
        $options = $this->_curlOptions;
        $options += $curlOptions;
        // print_r($options);
        if (array_key_exists(self::CURLOPT_OAUTH_TOKEN, $options)) {
            $includeAccessToken = $options[self::CURLOPT_OAUTH_TOKEN];
            unset($options[self::CURLOPT_OAUTH_TOKEN]);
        } else {
            $includeAccessToken = true;
        }

        if (array_key_exists(CURLOPT_HTTPHEADER, $options)) {
            $options[CURLOPT_HTTPHEADER] = array_merge(
                $this->buildDefaultHeaders(),
                $curlOptions[CURLOPT_HTTPHEADER]
            );
        } else {
            $options[CURLOPT_HTTPHEADER] = $this->buildDefaultHeaders(
                $includeAccessToken
            );
        }
        /* begin - remove libcurl SSL verficication */
        $options[CURLOPT_SSL_VERIFYHOST] = 0;
        $options[CURLOPT_SSL_VERIFYPEER] = 0;
        /* end - remove libcurl SSL verification */

        curl_setopt_array($curlinit, $options);

        $data = curl_exec($curlinit);
        $info = curl_getinfo($curlinit);
        curl_close($curlinit);

        if (array_key_exists(CURLOPT_HEADER, $options) && $options[CURLOPT_HEADER]) {
            $this->_lastHttpResponseHeaders = $this->parseHttpHeaders(
                substr($data, 0, $info['header_size'])
            );
            $this->_lastHttpResponseBody = substr($data, $info['header_size']);
        } else {
            $this->_lastHttpResponseHeaders = array();
            $this->_lastHttpResponseBody = $data;
        }

        $this->_lastHttpResponseCode = $info['http_code'];

        if ($this->validResponseCode($this->_lastHttpResponseCode)) {
            return $this->_lastHttpResponseBody;
        } else {
            throw new Popuparchive_Services_Invalid_Http_Response_Code_Exception(
                null,
                0,
                $this->_lastHttpResponseBody,
                $this->_lastHttpResponseCode
            );
        }
    }


}
