<?php

namespace WBF\includes;

class GoogleFontsRetriever{
    const api_url = 'https://www.googleapis.com/webfonts/v1/webfonts';
	/**
	 * @var string|null
	 */
    private $api_key;
	/**
	 * @var string|null
	 */
	public $last_error ;
	/**
	 * @var string
	 */
    public $cache_file_name = 'wbf_font_cache.php';
	/**
	 * @var \stdClass|null
	 */
	public $cached_fonts;

	/**
	 * GoogleFontsRetriever constructor.
	 *
	 * @param string|null $api_key
	 * @param string|null $cache_file_name
	 *
	 * @throws GoogleFontsRetrieverException
	 * @throws \Exception
	 */
	public function __construct($api_key = null,$cache_file_name = null){
        if(isset($api_key)){
            $this->api_key = $api_key;
        }
        if(isset($cache_file_name)){
        	$this->cache_file_name = $cache_file_name;
        }
        if($this->can_download_webfonts()){
        	$fonts_json = $this->download_webfonts();
	        if($fonts_json !== false){
		        $this->write_font_cache_file($fonts_json);
	        }
        }else{
	        $fonts = $this->read_font_cache_file();
	        if($fonts instanceof \stdClass){
	        	$this->cached_fonts = $fonts;
	        }
        }
    }

	/**
	 * @return bool
	 */
    public function can_download_webfonts(){
		return isset( $_GET['wbf_update_font_cache'], $this->api_key );
    }

	/**
	 * @return \stdClass
	 */
    public function get_webfonts(){
    	$currentFonts = $this->cached_fonts;
    	if($currentFonts === null){
		    $currentFonts = new \stdClass();
		    $currentFonts->items = array();
	    }
	    return $currentFonts;
    }

	/**
	 * @param string $familyName
	 *
	 * @return bool
	 */
	public function get_properties_of($familyName){
		if(!isset($this->cached_fonts)){
			return false;
		}
		foreach($this->cached_fonts->items as $font){
			if( $font->family === $familyName){
				return $font;
			}
		}
		return false;
	}

	/**
	 * @return \stdClass|bool
	 */
	public function read_font_cache_file(){
		try{
			$cache_file = WBF()->get_working_directory() . '/gfont_font_cache/' . $this->cache_file_name;
			if(!\is_file($cache_file) || !\is_readable($cache_file)){
				$cache_file = WBF()->get_path() . 'cache/' . $this->cache_file_name;
			}
			if(\is_file($cache_file) && \is_readable($cache_file)){
				require_once $cache_file;
				global $fonts;
				if(isset($fonts)){
					$fonts_json = $fonts;
					$fonts_parsed = json_decode($fonts_json);
					if($fonts_parsed instanceof \stdClass){
						return $fonts_parsed;
					}
				}
			}
		}catch (\Exception $e){
			return false;
		}
		return false;
	}

	/**
	 * @param string $fonts_json
	 *
	 * @throws \Exception
	 */
    public function write_font_cache_file($fonts_json = '{}'){
		if(!\is_string($fonts_json)){
			$fonts_json = '{}';
		}

		//Try decoding
	    $fontsParsed = json_decode($fonts_json);
		if(!$fontsParsed instanceof \stdClass){
			$fonts_json = '{}';
		}

		//File initialization
        $cache_file_content = '<?php $fonts = \''.$fonts_json.'\'; ?>';
        wp_mkdir_p(WBF()->get_working_directory().'/gfont_font_cache');
	    $cache_file = WBF()->get_working_directory().'/gfont_font_cache/'.$this->cache_file_name;

	    //File writing
	    $fhandle = fopen($cache_file,'w');
        if(fwrite($fhandle, $cache_file_content) === FALSE) {
            $this->last_error = new GoogleFontsRetrieverException("Unable to write the font cache file, located at: $cache_file",'file_write_failed');
        }
        fclose($fhandle);
    }

	/**
	 * Download webfonts json from google
	 *
	 * @return bool|string
	 * @throws GoogleFontsRetrieverException
	 */
    public function download_webfonts(){
	    $fonts_json = false;
	    if(!\function_exists('\wp_remote_get')){
		    return $fonts_json;
	    }

	    $url = \add_query_arg([
		    'key' => $this->api_key
	    ],self::api_url);

	    $response = \wp_remote_get($url, array('sslverify' => false));

	    if(\is_wp_error($response)){
	    	$e = new GoogleFontsRetrieverException(__( 'Unable to connect to Google API' ), 'connection_failed' );
		    $this->last_error = $e;
		    throw $e;
	    }

	    if(isset($response['body']) && $response['body']){
		    if(\strpos($response['body'], 'error') === false){
			    $fonts_json = $response['body'];
		    }else{
			    $error = \json_decode($response['body']);
			    $e = new GoogleFontsRetrieverException(\sprintf(__('Google API Notice: %s. %s', 'wbf'), $error->error->code, $error->error->message), 'limit_reached' );
			    $this->last_error = $e;
			    throw $e;
		    }
	    }

	    return $fonts_json;
    }
}

class GoogleFontsRetrieverException extends \Exception{
    var $type;

    public function __construct($message, $type, $code = 0){
        parent::__construct($message, $code);
        $this->type = $type;
    }
}