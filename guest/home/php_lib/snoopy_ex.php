<?
/*
	$snp = new SnoopyEx();
	$htmldata1 = $snp->get_url("http://search.naver.com/search.naver?where=nexearch&query=shoes&sm=top_hty&fbm=1&ie=utf8");
	$myCookie = $snp->get_cookie();
	
	$snp2 = new SnoopyEx(true, $myCookie);
	$htmldata2 = $snp2->get_url("http://search.naver.com/search.naver?where=nexearch&query=shoes&sm=top_hty&fbm=1&ie=utf8");	
	
	
	$snp3 = new SnoopyEx(true);
	
	$snp3->set_Referer("http://www.referersite.com");
	
	$snp3->get_post_url("http://search.naver.com/search.naver", array("where"=>"nexearch", "query"=>"shoes", "sm"=>"top_hty", "fbm"=>1, "ie"=>"utf8"));
	var_dump($snp3->get_cookie());
	
*/
include dirname(__FILE__) . "/snoopy/Snoopy.class.php";
class SnoopyEx extends Snoopy
{
	private $_agent = null;
	private $_bUpdateCookieUpdate = false;
	
	//---------- set req header function -----------------------
	public function set_enableRedirect($bEnable) {$this->maxredirs = ($bEnable ? 5 : 0);}
	public function set_enableGzip($bEnable) {$this->use_gzip = $bEnable;}
	
	public function set_Referer($val) {$this->referer = $val;}
	public function set_UserAgent($val) {$this->agent = $val;}
	public function set_HttpVersion($val) {$this->_httpversion = $val;}
	public function set_Accept($val) {$this->accept = $val;}
	public function set_Connection($val) {$this->rawheaders['Connection'] = $val;}
	public function set_AcceptLanguage($val) {$this->rawheaders['Accept-Language'] = $val;}
	public function set_CacheControl($val) {$this->rawheaders['Cache-Control'] = $val;}
	public function set_ContentType($val) {$this->_submit_type = $val;}		// "multipart/form-data" 또는 "application/x-www-form-urlencoded" 형식이여야 함 ("; charset=UTF-8" 추가 가능)
	public function set_headerOrder($array){$this->_headerorder = $array;}	// array('host', 'referer', 'useragent', 'accept', 'acceptencoding', 'cookie', 'contenttype', 'contentlength');
	
	public function set_Header($key, $val) 
	{
		$lower_key = strtolower($key);
		if ($lower_key == "referer") 	return $this->set_Referer($val);
		if ($lower_key == "user-agent") return $this->set_UserAgent($val);
		if ($lower_key == "accept") 	return $this->set_Accept($val);
		if ($lower_key == "connection") return $this->set_Connection($val);
		if ($lower_key == "accept-language") return $this->set_AcceptLanguage($val);
		if ($lower_key == "cache-control") 	return $this->set_CacheControl($val);
		if ($lower_key == "content-type") 	return $this->set_ContentType($val);
		$this->rawheaders[$key] = $val;
	}
	
	//---------- set req header function -----------------------
	public function SnoopyEx($bUpdateCookieUpdate = false, $arInitCookie = null) 
	{
		$this->_bUpdateCookieUpdate = $bUpdateCookieUpdate;
		$this->_agent = sprintf("Mozilla/5.0 (Windows NT %d.1; rv:%d.0) Gecko/20100101 Firefox/%d.0", rand(5,6), rand(1,27), rand(1,27));
		if ($arInitCookie) $this->set_cookie($arInitCookie);
	}
	
	public function get_url($url, $referer = null, $agent = null, $httpversion = "HTTP/1.0", $rawheaders = null) 
	{
		$this->set_UserAgent($agent !== null ? $agent : $this->_agent);
		$this->set_Referer($referer !== null ? $referer : "");
		$this->set_HttpVersion($httpversion);
		
		$this->set_Accept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->set_AcceptLanguage('ko-kr,ko;q=0.8,en-us;q=0.5,en;q=0.3');
		$this->set_Connection('close');
		
		if ($rawheaders) foreach($rawheaders as $key => $val) {$this->set_Header($key, $val);}
		
		//echo "<br>  request summary ----------------------------- <br>";
		//echo "referer : " . $this->referer	. "<br>";
		//echo "user-agent: " . $this->_agent	. "<br>";
		//echo "referer : " . $this->referer	. "<br>";
		//echo "<br> ---------------------------------------------- <br>";
				
		$this->fetch($url);
		if ($this->_bUpdateCookieUpdate) $this->setcookies();
		return $this->results;;		
	}	
	
	public function get_post_url($url, $post_param, $referer = null, $agent = null, $httpversion = "HTTP/1.0", $rawheaders = null) 
	{
		$this->set_UserAgent($agent !== null ? $agent : $this->_agent);
		$this->set_Referer($referer !== null ? $referer : "");
		$this->set_HttpVersion($httpversion);

		$this->set_Accept('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
		$this->set_AcceptLanguage('ko-kr,ko;q=0.8,en-us;q=0.5,en;q=0.3');
		// $this->set_Pragma('no-cache');
		$this->set_Connection('close');

		if ($rawheaders) foreach($rawheaders as $key => $val) {$this->set_Header($key, $val);}

		$this->httpmethod = "POST";
		// var_dump($post_param);		
		$this->submit($url, $post_param);
		
		$this->_content_type = null;	// content type은 항상 사용하고 Clear (휘발성)
		if ($this->_bUpdateCookieUpdate) $this->setcookies();
		return $this->results;;		
	}	
		
	public function get_cookie()
	{
		return $this->cookies;
	}
	
	public function set_cookie_string($strCookie)
	{
		$this->cookies['*'] = $this->cookies['*'] . trim($strCookie, " ;\t\n\r\0\x0B") . "; ";
	}
	
	public function set_cookie($arCookie)
	{
		$this->cookies = $arCookie;
	}
	
	public function add_cookie($key, $value)
	{
		$this->cookies[$key] = $value;
	}
}

?>