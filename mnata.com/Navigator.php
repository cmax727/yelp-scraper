<?php
/**
* this class is created by sasya8080
* 
* @since 2012-03-08
* @author sasya8080
* @version 1.0
*/

class Engine_Navigator {
	
	
	public $_chanel;
	protected $_cookie;
	protected $_cookiejar;
	
	public function __construct(){
		$cfg = Zend_Registry::get("app_settings");
		#$this->_cookie = $cfg['loginsession']['COOKIEPATH'].mt_rand(1, 1000)."_cookie.txt";
		#$this->_cookiejar = $cfg['loginsession']['COOKIEPATH'].mt_rand(1, 1000)."_cookies.txt";
		$this->_cookie = $cfg['loginsession']['COOKIEPATH'] . "_cookie.txt";
		$this->_cookiejar = $cfg['loginsession']['COOKIEPATH'] ."_cookies.txt";
		
		#$ourFileHandle = fopen($this->_cookie, 'w') or die("can't open file");
		#fclose($ourFileHandle);
		
		#$ourFileHandle = fopen($this->_cookiejar, 'w') or die("can't open file");
		#fclose($ourFileHandle);
        
        $this->proxies = include "proxylist.php";
	}
	
	public function __destruct(){
		$this->closeChanel($this->_chanel);
		#unlink($this->_cookie);
		#unlink($this->_cookiejar);
	}
	/**
	* get login-session chanel
	* 
	* @param mixed $loginFields : user login fields ie. 'USERNAME=sasya&PWD=password';
	* @param mixed $homePage	: home page
	* @param mixed $loginPage	: login page
	* @return resource
	*/
	function loginTo($loginFields, $homePage, $loginPage = ""){
		@curl_close($this->_chanel);
		
		if ( empty($loginPage)){
			$loginPage = $homePage;
		}
			
		$chanel = curl_init(); 
		//Set options for curl session 
#User-Agent: Mozilla/5.0 (Windows NT 6.1; rv:10.0.2) Gecko/20100101 Firefox/10.0.2
#User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11
		$options = array(CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.79 Safari/535.11', 
				 CURLOPT_SSL_VERIFYPEER => FALSE, 
				 CURLOPT_SSL_VERIFYHOST => 2, 
				 
				 CURLOPT_RETURNTRANSFER => TRUE, 
				 CURLOPT_COOKIEFILE => $this->_cookie,  
				 CURLOPT_COOKIEJAR => $this->_cookiejar, 
				 CURLOPT_HEADER => TRUE, // get the header 
				 CURLOPT_NOBODY =>1,  // and *only* get the header  
						
				 ); 
		//curl_setopt($chanel, CURLOPT_REFERER, "http://www.google.com");
		//Hit home page for session cookie 
		$options[CURLOPT_URL] = $homePage; 
		curl_setopt_array($chanel, $options);
		
		curl_exec($chanel); 

		//Login 
		$options[CURLOPT_URL] = $loginPage; 
		$options[CURLOPT_POST] = TRUE; 
		$options[CURLOPT_POSTFIELDS] = $loginFields;// 
		$options[CURLOPT_FOLLOWLOCATION] = FALSE; 
		curl_setopt_array($chanel, $options); 
//		curl_setopt($chanel, CURLOPT_HEADER, TRUE);
//		curl_setopt($chanel, CURLOPT_NOBODY, TRUE);
		
		curl_exec($chanel); 
		
		curl_setopt($chanel,CURLOPT_HEADER , 0);
		curl_setopt($chanel,CURLOPT_RETURNTRANSFER , TRUE);
		curl_setopt($chanel, CURLOPT_NOBODY, FALSE);
		curl_setopt($chanel, CURLOPT_FOLLOWLOCATION, TRUE); 
		curl_setopt($chanel, CURLOPT_TRANSFERTEXT, TRUE); 
		
		return $this->_chanel = $chanel;
	}
	/**
	* put your comment there...
	* 
	*/
	public function getChanel(){
		return $this->_chanel;
	}
	
	/**
	* get unathorized chanel
	*    to make post then use theses fields : 	CURLOPT_POSTFIELDS = field=value&field=value, 
	* 											CURLOPT_POST = true
	* 
	* @param mixed $page
	* @param mixed $opt
	* @return resource
	*/
	public function loginEmpty($opt = array()){
		@curl_close($this->_chanel);   
		$this->_chanel = $chanel = curl_init(); 
		
		//Set default options for curl session 
		$options = array(
                 //CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'], 
                 //CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; rv:11.0) Gecko/20100101 Firefox/11.0', 
                 //CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13',
                 //CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1096.1 Safari/536.6',
                 //CURLOPT_SSL_VERIFYPEER => FALSE, 
				 CURLOPT_SSL_VERIFYHOST => 2, 
				 CURLOPT_RETURNTRANSFER => TRUE, 
				 CURLOPT_COOKIEFILE => dirname(__FILE__). '/cookies.txt', 
				 CURLOPT_COOKIEJAR => dirname(__FILE__). '/cookies.txt',
				 CURLOPT_HEADER => 0, // get the header 
				 //CURLOPT_NOBODY =>0,  // and *only* get the body
				 ); 

		//override options
		
		curl_setopt_array($chanel, $opt);
		//Hit home page for session cookie 
		curl_setopt_array($chanel, $options);
	
		curl_setopt($chanel,CURLOPT_HEADER , 0);
		curl_setopt($chanel,CURLOPT_RETURNTRANSFER , TRUE);
		curl_setopt($chanel, CURLOPT_NOBODY, FALSE);
		curl_setopt($chanel, CURLOPT_FOLLOWLOCATION, TRUE); 
	//	curl_setopt($chanel, CURLOPT_TRANSFERTEXT, TRUE); 
		
	}
	
	public function closeChanel(){
		curl_close($this->_chanel);
	}
	
	/**
	* put your comment there...
	* 
	* @param mixed $options
	*/
	public function setOption($options = array()){
		curl_setopt_array($this->_chanel, $options)	;
	}
	
var $proxies = array(
    ) ;
    
    var $curPrx = 0;            
    public function setNextProxy(){
        $pCnt = count($this->proxies);
        
        $this->setProxy($this->proxies[$this->curPrx]);
        
        $this->curPrx = ($this->curPrx+1) % $pCnt;
        
        if ( $this->proxies[$this->curPrx] == "exit"){
            echo "No available proxies. All are exhausted.";
            exit();
        }
    }
    public function setProxy($proxyURL, $user = null, $pwd = null){
        if ( !empty($proxyURL)){
            curl_setopt($this->_chanel , CURLOPT_PROXY, $proxyURL);
            $proxyAuth = "$user:$pwd";
            if ( $user){
                curl_setopt($this->_chanel, CURLOPT_PROXYUSERPWD, $proxyauth);
            }
        }else{
            curl_setopt($this->_chanel, CURLOPT_PROXY, null);
        }
    }
	/**
	* put your comment there...
	* 
	* @param mixed $url
	* @param mixed $option
	*/
    var $dtCnt = 0;
	public function getData($url, $fields = array(), $method = "get"){
        $da = Engine_Api::_()->getDbtable("users")->getAdapter();    
        $row = $da->query("select * from m4_store where id = 'stop'")->fetch();
        if ( $row['vl'] == '0') exit();
        
        /*
        $row = $da->query("select * from m4_store where id = 'pcnt'")->fetch();
        if ( $row['vl'] >= 4800) {
            $da->query("update m4_store set vl='0' where id = 'pcnt'");
            $this->setNextProxy();
            return $this->getData($url, $fields, $method);
        }else{
            $c = $row['vl'] +1;
            $da->query("update m4_store set vl='{$c}' where id = 'pcnt'");
        }
        */
         
        if ( $this->dtCnt == 5000){
            $this->dtCnt = 0; 
            //$this->setNextProxy();
        }
        $this->dtCnt += 1;
         
		$fieldStr = "";
		foreach ( $fields as $key=>$value){
			$fieldStr  .= "&$key=$value";
		}
		$fieldStr = substr($fieldStr, 1) ;
		
		$options = array(
			CURLOPT_URL => $url,
		);
		
        if ( !empty($fields)){
		    if ( $method == "post"){
			    $options[CURLOPT_POST] = 1;
			    //$options[CURLOPT_GET] = 0;
			    $options[CURLOPT_POSTFIELDS] = $fieldStr;
			    //$options[CURLOPT_GETFIELDS] = "";
		    }else{
			    $options[CURLOPT_POST] = 0;
			    //$options[CURLOPT_GET] = 1;
			    //$options[CURLOPT_POSTFIELDS] = "";
			    $options[CURLOPT_POSTFIELDS] = $fieldStr;
		    }
        }
	//	curl_setopt($this->_chanel, CURLOPT_WRITEFUNCTION, 0);
        curl_setopt($this->_chanel, CURLOPT_RETURNTRANSFER , TRUE);
        
		curl_setopt_array($this->_chanel, $options);
		
		$data = curl_exec($this->_chanel);
        
        if ( strpos($data, "403 Forbidden",0) == true || $data == false){
            $this->setNextProxy();
            return $this->getData($url, $fields , $method );
        }
            
        
		return $data; 
		
		preg_match("#String.fromCharCode\((\d+)\)\+String.fromCharCode\((\d+)\)#", $data, $match);
		if ( !empty($match)){
			$prid = chr($match[1]).chr($match[2]);
			curl_setopt($this->_chanel, CURLOPT_COOKIE, "PRLST=$prid");
		}

		

		//fwrite($file, $data);
		//fwrite($file, urldecode($data));
		//fclose($file);
		return $data;
				
	}
    
    
    var $_buffersize = 1024000;
    var $_buffer = "";
    private function handledata($ch, $data){
        $len = strlen($data);
        $GLOBALS["dataread"] += $len; 
        $this->_buffer .=$data;
        if(strlen($this->_buffer) > $this->_buffersize) { 
                return 0; 
        } 
         
        return $len;
    }
	/**
    * put your comment there...
    * 
    * @param mixed $url
    * @param mixed $fields
    * @param mixed $method
    * @param mixed $size KB default = 100KB
    * @return mixed
    */
    public function getDataPartial($url, $size=100,  $method = "get", $fields = array()){
        $this->_buffer = "";
        $this->_buffersize = $size * 1024;
        $this->_buffer="";
        
        $fieldStr = "";
        foreach ( $fields as $key=>$value){
            $fieldStr  .= "&$key=$value";
        }
        $fieldStr = substr($fieldStr, 1) ;
        
        $options = array(
            CURLOPT_URL => $url,
        );
        
         if ( !empty($fields)){
            if ( $method == "post"){
                $options[CURLOPT_POST] = 1;
                //$options[CURLOPT_GET] = 0;
                $options[CURLOPT_POSTFIELDS] = $fieldStr;
                //$options[CURLOPT_GETFIELDS] = "";
            }else{
                $options[CURLOPT_POST] = 0;
                //$options[CURLOPT_GET] = 1;
                //$options[CURLOPT_POSTFIELDS] = "";
                $options[CURLOPT_POSTFIELDS] = $fieldStr;
            }
        }
        
        curl_setopt_array($this->_chanel, $options);
        curl_setopt($this->_chanel, CURLOPT_WRITEFUNCTION, array($this, "handledata"));
        curl_setopt($chanel,CURLOPT_RETURNTRANSFER , FALSE);
         
         
        $data = curl_exec($this->_chanel);
        return $this->_buffer; 
        
        preg_match("#String.fromCharCode\((\d+)\)\+String.fromCharCode\((\d+)\)#", $data, $match);
        if ( !empty($match)){
            $prid = chr($match[1]).chr($match[2]);
            curl_setopt($this->_chanel, CURLOPT_COOKIE, "PRLST=$prid");
        }

        
        //fwrite($file, $data);
        //fwrite($file, urldecode($data));
        //fclose($file);
        return $data;
                
    }
    
	public function getData3($url){
		$chanel = curl_init();
		$opts = array(
			CURLOPT_URL => $url,
            CURLOPT_FOLLOWLOCATION=>1,
			CURLOPT_TRANSFERTEXT => TRUE,
			CURLOPT_RETURNTRANSFER =>TRUE,
			);
		curl_setopt_array($chanel, $opts);
        curl_setopt($chanel, CURLOPT_WRITEFUNCTION, 0);
		$data = curl_exec($chanel);
		
		return $data;
	}
    
    
	
  }
?>
