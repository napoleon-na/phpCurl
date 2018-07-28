<?php
/**
 * Wrapper curl_exec() for useing the original more easier. 
 * (POST or GET)
 */
class CurlConnect {
    
    /**
     * keys of result that $this->exec() returns
     */
    const CH_RETRIEVE_KEY_RESPONSE = 'response';
    const CH_RETRIEVE_KEY_INFO     = 'info';
    const CH_RETRIEVE_KEY_ERROR    = 'error';
    
    /**
     * cURL handle
     * 
     * @var cURL handle
     */
    private $ch;
    
    /**
     * tmp file for cookie
     * 
     * @var string
     */
    // private $tmpFile;

    /**
     * curl options
     * 
     * @var array
     */
    private $options;
    
    /**
     * constructor
     * 
     * @param  array $options curl options (optional)
     * @return void
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }
    
    /**
     * destructor
     * 
     * @return void
     */
    public function __destruct() {
        // unlink($this->tmpfile);
        curl_close($this->ch);
        unset($this->ch);
    }

    /**
     * Get result using POST method.
     * 
     * @param  string $url        URL connect to
     * @param  array  $postFields post parameters
     * @return array
     */
    public function viaPost($url, $postFields)
    {
        $options[CURLOPT_CUSTOMREQUEST] = 'POST';
        $options[CURLOPT_POSTFIELDS]    = http_build_query($postFields);

        return $this->exec($url, $options);
    }

    /**
     * Get result using GET method.
     * 
     * @param  string $url URL connect to
     * @return array
     */
    public function viaGet($url)
    {
        $options[CURLOPT_CUSTOMREQUEST] = 'GET';
        return $this->exec($url, $options);
    }

    /**
     * Execute and get the result.
     * @todo: it would be easy to use Guzzle instead cURL.
     * 
     * @param  string $url     URL connect to
     * @param  array  $options parameters to connect
     * @return array
     */
    private function exec($url, $options)
    {
        if (!isset($this->ch)) {
            $this->ch = curl_init();
        }
        // @todo: uncomment if cookie was needed.
        // if (!isset($this->tmpfile)) {
        //     $this->tmpfile = tempnam(sys_get_temp_dir(), 'tmp');
        // }
        curl_reset($this->ch);

        $options[CURLOPT_URL]            = $url;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_TIMEOUT]        = 60;
        // $options[CURLOPT_COOKIEFILE]     = $this->tmpfile;
        // $options[CURLOPT_COOKIEJAR]      = $this->tmpfile;
        curl_setopt_array($this->ch, ($this->options + $options));

        $response = curl_exec($this->ch);
        $info     = curl_getinfo($this->ch);
        $error    = curl_error($this->ch);

        if ($error) {
            throw new \Exception($error);
        }

        $result = [
            self::CH_RETRIEVE_KEY_RESPONSE  => $response,
            self::CH_RETRIEVE_KEY_INFO      => $info,
            self::CH_RETRIEVE_KEY_ERROR     => $error,
        ];

        return $result;
    }
}

// How to use.
// try {
//     $options = [
//         CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:61.0) Gecko/20100101 Firefox/61.0'
//     ];
//     $curlConnect = new CurlConnect($options);
//     $result      = $curlConnect->viaGet('https://api.github.com/');
//     $json        = json_decode($result[CurlConnect::CH_RETRIEVE_KEY_RESPONSE], true);
//     pretty_print_r($json);
// } catch (Exception $e) {
//     pretty_print_r($e);
// }
//
// function pretty_print_r($var) {
//     echo '<pre>';
//     print_r($var);
//     echo '</pre>';
// }