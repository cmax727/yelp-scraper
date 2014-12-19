<?php
class User_ScrapeController extends User_Library_Controller_Action_Abstract
{
    function callAction(){

        for ($idx = 0; $idx < 100; $idx ++){
            $ss = "http://www.yelp.com/search/snippet?attrs=&cflt=shopping&find_desc=&find_loc=New+Town,+MA&mapsize=small&rpp=40&sortby=best_match&start=960";
            $ch = curl_init($ss);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS,10);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS,10);
            $d = curl_exec($ch);
        }
        echo $d;
        exit();
        
    }
    private function getListUrl($loc, $cflt="", $start=""){
        $url = "http://www.yelp.com/search?rpp=40&find_loc={$loc}&cflt={$cflt}";
        if ( $start != ""){
            $url .= "&start={$start}";
        }
        return $url;
    }
    var $cfltList = array(
            'active',
        'arts',
        'auto',
            'beautysvc',
        'education',
        'eventservices',
            'financialservices',
        'food',
        'health',
            'homeservices',
        'hotelstravel',
        'localflavor',
            'localservices',
        'massmedia',
        'nightlife',
            'pets',
        'professional',
        'publicservicesgovt',
            'realestate',
        'religiousorgs',
        'restaurants',
            'shopping',
            );
    
    
	public function yelpAction(){
        
        $siteUrl = "http://www.yelp.com";
        $nav = new Engine_Navigator();
        $nav->loginEmpty(); 
        
        curl_setopt($nav->_chanel, CURLOPT_CONNECTTIMEOUT,20);
        curl_setopt($nav->_chanel, CURLOPT_TIMEOUT,20);
                           
        set_time_limit ( 0);
        
        $da = Engine_Api::_()->getDbtable("users")->getAdapter(); 
       
       // return;
       
        while(true){
            $row = $da->query("select * from m4_cities where rd = 0 order by id limit 1")
                ->fetch();
            
            sleep(2);
            // set intermidate
            $da->query("update m4_cities set rd = 3 where id = {$row['id']}");
            
            $loc = urlencode("{$row['city']}, {$row['state']}");
        
            $locUrl = $this->getListUrl($loc, "", "");
            $dataA = $nav->getData($locUrl);
            
            preg_match("#pager_total\">(\d+?)<#", $dataA, $matches);
            $pgTotal = (int)$matches[1];
            
            if ( $pgTotal < 1){// search : not found 
                $da->query("update m4_cities set rd = 8 where id = {$row['id']}");   
                continue;
            }
            
            $ccs = array(null,);
            if ( $pgTotal > 1000){
                $ccs = $this->cfltList;
            }
            
            foreach ($ccs as $cflt){
                $url = $this->getListUrl($loc, $cflt, 0);
                
                $data = $dataA;
                if ( $cflt == null){
                    
                }else{
                    $data = $nav->getData($url);    
                }
                
               // $data = $nav->getDataPartial($url, 100);
                preg_match("#pager_total\">(\d+?)<#", $data, $matches);
                
                $pgTotal = (int)$matches[1];
                
                if ( $pgTotal <= 0 ) continue; //not found
                
                for ( $page = 0; $page < $pgTotal; $page +=40){
                    $url = $this->getListUrl($loc, $cflt, $page);
                    
                    if ( $page != 0){
                        $data = $nav->getData($url);
                    }
                    //preg_match("#yelp.hub.publishMultiple(.*?);</script>#", $data, $matches);
                    //$jsonData = $matches[1];
                    
                    preg_match_all('#<a id="bizTitleLink.*?" href="(.*?)"#', $data, $matches );
                    
                    foreach ( $matches[1] as $url){
                        $vExpr = "('$url', '$cflt', $pgTotal, '$loc')";
                        $sql = "insert into m4_urls(url, cflt, total, city) values $vExpr";
                        try{
                            $da->query($sql);
                        }
                        catch(Exception $ee){
                            $c = 100;
                        }
                    }
                    $vExpr = substr($vExpr, 1);
                    
                    //foreach ( $matches[1] as $url){
                        //$sql = "insert into m4_urls(url, cflt, total) values ('$url', '$cflt', $pgTotal)";
                        
                        
                    //}
                    
                    //process data{
                    //}
                }
            }
            
            // mark city to complete 
            $da->query("update m4_cities set rd = 1 where id = {$row['id']}");   
        }
    }
    
    public function yelp22Action(){ 
       //return;
        
        $siteUrl = "http://www.yelp.com";
        $nav = new Engine_Navigator();
        $nav->loginEmpty(); 
        
        curl_setopt($nav->_chanel, CURLOPT_CONNECTTIMEOUT,11);
        curl_setopt($nav->_chanel, CURLOPT_TIMEOUT,11);

        
        set_time_limit ( 0);
        
        $da = Engine_Api::_()->getDbtable("users")->getAdapter(); 
         
        while(true){
			$rows = $da->query("select url from m4_urls where rd = 0 order by id limit 50")
            ->fetchAll();
            $urls = array();
            
            
            if ( empty($urls)) {
                echo "No urls to scrape";
                exit();
            }
            
            $da->beginTransaction();                                                                
            $usql = $da->quoteInto("update m4_urls set rd=3 where url in(?)", $urls );
            $da->query($usql, $urls);
            //$da->commit();
		 $possiblePrx = array();
         foreach($urls as $url){
			if ( ! $url) continue ;
            
            $data = $nav->getData($siteUrl.$url);
            preg_match('#<meta property="og:url" content="http:..www.yelp.com.biz.(.*?)"#', $data, $matches)       ;
            $bizId = $matches[1];
            
            if ( !empty($bizId))
            
//            $data = $nav->getDataPartial($siteUrl.$row['url'], 170);
            $data = str_replace(array("\r", "\n"), "", $data);
            //$curl_info = curl_getinfo($nav->_chanel);
            /*preg_match('#<meta property="og:url" content="http:..www.yelp.com.biz.(.*?)"#', $data, $matches)       ;
            $bizId = $matches[1];
            
            preg_match('#og:title" content="(.*?)"#', $data, $matches);
            $name =  $matches[1];
            
            preg_match('#og:image" content="(.*?)"#', $data, $matches);
            $img_url =  $matches[1];
            $snippet_img_url = $img_url . "/60s.jpg";
            //preg_match('##', $data, $matches);
            $url =  $siteUrl.$row['url'];
            
            preg_match('#id="bizPhone".*?>(.*?)<#', $data, $matches);
            $phone =  $matches[1];
            
            preg_match('#class="review-count".*?class="count">(.*?)<#', $data, $matches);
            $review_cnt =  $matches[1];
            
            preg_match('#id="cat_display">.*?>(.*?)<#', $data, $matches);
            $cat =  $matches[1];
            
            preg_match('#og:latitude" content="(.*?)"#', $data, $matches);
            $latitude =  $matches[1];
            
            preg_match('#og:longitude" content="(.*?)"#', $data, $matches);
            $longitude =  $matches[1];
            
            preg_match('#class="street-address">(.*?)<#', $data, $matches);
            $addr =  $matches[1];
            
            preg_match('#setTargeting."city", "(.*?)"#', $data, $matches);
            $city =  $matches[1];
            
            preg_match('#class="region">(.*?)<#', $data, $matches);
            $state_code =  $matches[1];
                        
            preg_match('#setTargeting."zipcode", "(.*?)"#', $data, $matches);
            $post_code =  $matches[1];
            
            preg_match('#setTargeting."country", "(.*?)"#', $data, $matches);
            $country_code =  $matches[1];
            
            preg_match('#setTargeting."attrrating", "(.*?)"#', $data, $matches);
            $rating =  $matches[1];
            */
            
            //-------
            $url =  $siteUrl.$row['url'];
            $matches = "";
            preg_match('#"yelp.init.bizDetails.page", (.*?)\);<\/script>#', $data, $matches);
            //$jsonData =  "\$str=".$matches[1];
            $jsonData =  $matches[1];
            $jsonData = json_decode($jsonData, true, 10);
            

            $bizId = $jsonData['bizId'];
            
            $name = $jsonData['bizSafe']['name'];
            
            $img_url = $jsonData['bizSafe']['photos'][0]['id'];
            
            $url = $jsonData['bizSafe']['url'];
            
            $snippet_img_url = $img_url . "/60s.jpg";
            
            $phone =  $jsonData['bizSafe']['phone'];   
            
            $review_cnt =  $jsonData['bizSafe']['review_count'];
            
            $cat =  implode(",",$jsonData['bizSafe']['category_yelp_ids']);
            preg_match('#id="cat_display">.*?>(.*?)<#', $data, $matches);
            $cat =  $matches[1];
            
            $latitude =  $jsonData['bizSafe']['latitude'];

            $longitude =  $jsonData['bizSafe']['longitude'];

            $addr =  $jsonData['bizSafe']['address1'];

            $city =  $jsonData['bizSafe']['city'];

            $state_code =  $jsonData['bizSafe']['state'];
                        
            $post_code =  $jsonData['bizSafe']['zip'];
            
            $country_code =  $jsonData['bizSafe']['country'];
            
            $rating = $jsonData['bizSafe']['rating'];
            
            $neighbor =  $jsonData['bizSafe']['neighborhoods'];
            
            $sql = "insert into m4_yelp (
  biz_id, name, image_url, url, phone,
  review_count, categories, latitude, longitude, address, 
  city, state_code, postal_code, country_code, rating, neighborhoods ) 
  values (?,?,?,?,?,
    ?,?,?,?,?,
    ?,?,?,?,?, ?);
  ";
            if ( empty($bizId))
            {
                continue;
            }
            
            try{
                $da->query($sql, array(    $bizId, $name, $img_url, $url, $phone,
      $review_cnt, $cat, $latitude, $longitude, $addr,
      $city, $state_code, $post_code, $country_code, $rating, $neighbor
  ));
            
            $usql = "update m4_urls set rd=1 where url = ?";
            $da->query($usql, $url);
            }
            catch(Exception $ee){
                $c = 100;
                echo $ee->getMessage();
               // exit;
            }
         }                
        }
    }	
    
	public function citysearchAction(){
        set_time_limit ( 0);
       	$nav = new Engine_Navigator();
        $nav->loginEmpty();
        $siteUrl = "http://sanfrancisco.citysearch.com";
		$url = "http://sanfrancisco.citysearch.com/browse/social-media-directory?page=";
        //$data =$nav->getData3($url);
        
        $table = Engine_Api::_()->getDbtable("dirs");
        $sth = $table->getAdapter()->prepare("insert into {$table->info('name')}(name,phone_num, website, twitter_user, 
             fb_url, address, city, state, zip, category, search_url) values (:name,:phone_num, :website, :twitter_user, 
             :fb_url, :address, :city, :state, :zip, :category, :search_url)");    
        $sqlData = array();
        try{    
        for ($idx = 0; $idx < 148; $idx++){
            $data = $nav->getData($url."$idx");
            $data = preg_replace("#\r|\n#", "", $data);
            // extract each item link
            $ptn1 = '#"listingName".*?href="(.*?)"#';
            preg_match_all($ptn1, $data, $matchs);
            
                $search_url= $url."$idx";  
                
            foreach ( $matchs[1] as $pageUrl){
                $sqlData = array();
                $sqlData['search_url']  = $search_url;
                
                $pageUrl = $siteUrl . $pageUrl;
                $pgData = $nav->getData($pageUrl);
                
                preg_match("#\"fn org\">(.*?)<\/h1>#", $pgData, $mc);
                $sqlData['name'] = $mc[1];
                
                preg_match("#class=\"tel\">(.*?)<#", $pgData, $mc);
                $sqlData['phone_num'] = $mc[1];
                
                preg_match("#class=\"webaddress\">.*?>(.*?)<#", $pgData, $mc);
                $sqlData['website']= $mc[1];
                
                preg_match("#id=\"coreTwitterLink\".*?href=\"(.*?)\"#", $pgData, $mc);
                $sqlData['twitter_user']= $mc[1];
                
                preg_match("#id=\"coreFacebookLink\".*?href=\"(.*?)\"#", $pgData, $mc);
                $sqlData['fb_url']= $mc[1];
                
                preg_match("#class=\"street-address\">(.*?)<#", $pgData, $mc);
                $sqlData['address']= $mc[1];
                
                preg_match("#class=\"locality\">(.*?)<#", $pgData, $mc);
                $sqlData['city']= $mc[1]; 
                
                preg_match("#class=\"region\">(.*?)<#", $pgData, $mc);
                $sqlData['state']= $mc[1]; 
                
                preg_match("#class=\"postal-code\">(.*?)<#", $pgData, $mc);
                $sqlData['zip']= $mc[1]; 
                
                preg_match("#class=\"categoryTitle\">.*?(<p>.*?<\/p>)#", $pgData, $mc);
                $sqlData['category'] = "";    
                try{
                    $xml = new SimpleXMLElement($mc[1]);
                    foreach ($xml->children() as $nd) {
                        $sqlData['category'] .= (string)$nd."," ;
                    }
                }catch(Exception $ee){}

                $sth->execute($sqlData);
                
            }   
        }
        
        }catch(Exception $ee){
            $c = 00;
        }
		
	}
	
	public function index3232Action(){
		$nav = new Engine_Navigator();
		$nav->loginTo("member_cd=w710679&pwd=S32325568&mode=login",
				"https://www.iauc.co.jp/pv/top1.html",
				"https://www.iauc.co.jp/auction/servlet/authenticate");
		
		$data = $nav->getData("https://www.iauc.co.jp/pv/top1.html");
		
		#cho $data;
	
	}
	

	public function index222Action()
	{
		$nav = new Engine_Navigator();
		$nav->loginTo("email=sasya8080@gmail.com&password=cls.dir",
				"https://www.manta.com",
				"https://www.manta.com/member/login/");
		
		$data = $nav->getData("http://www.manta.com/c/mm5xg94/matrix-video-productions");
		
		//echo $data;
		
		#$this->storeCompanyInfo($data);
		#$this->getCategoryBreadCrumb($data);
		
		$this->scrapeCategory($nav,"http://www.manta.com/mb", null );
	} 
	
	/**
	* put your comment there...
	* 
	* @param mixed $data
	*/
	public function storeCompanyInfo($data){
		
		
		$cat = Engine_Api::_()->getDbtable("category")->findByName($cat_name);
		$state = Engine_Api::_()->getDbtable("state")->findByName($st_name);
		$city = Engine_Api::_()->getDbtable("city")->findByName($cty_name);
		
	}
	
	private function getCategoryBreadCrumb($data){
		
		$rFlg = preg_match('#id="breadcrumbs"([^`]*?)<\/ul>#m', $data, $ct_uls );
		
		if ($rFlg == 0)
			return array();
			
		$ct_uls = $ct_uls[1]; 
		preg_match_all("#><span>([^`]*?)<\/span>#", $ct_uls ,$match  );
		
		unset($match[0]);
		return $match;
		
	}
	/**
	* put your comment there...
	* 
	*/
	function navigateCompany(){
		
	}
	
	/**
	* put your comment there...
	* 
	*/
	public function scrapeCategory($nav, $url, $parent = null){
		$data = $nav->getData($url);
		$ptn = '#(class="all_companies"[^`]*?-- ad holder --)#';
		
		preg_match($ptn, $data, $c);
		$c = $c[1];
		
		$ptn_2 = '#href="(.*?)".*?>(.*?)<\/a> <em>(.*?)<#';
		preg_match_all($ptn_2, $c, $match);
		
		$catTable = Engine_Api::_()->getDbtable("category");
		
		$cat_url = $match[1];
		$cat_name = $match[2];
		$cat_cnt = $match[3];
		#$parent_id = empty($parent)?0:$parent->id;
		
		if ( empty($cat_url) && !empty($parent)){
			$parent->isfinal = 1;
			$parent->save();
			return;
		}
		
		for($idx = 0; $idx < count($cat_url); $idx++){
			$c_url = $cat_url[$idx];
			$c_name = html_entity_decode($cat_name[$idx]);
			$c_cp_cnt = preg_replace("#[^\d]#", "", $cat_cnt[$idx]);
			
			$cat_data = array(
				#"name" => $c_name,
				"cp_cnt" => $c_cp_cnt,
				"code" => "",
				"url_name" => "",
			#	"super_id" => $parent_id,
			);
			
			$cat = $catTable->addCategory($c_name, $cat_data , $parent);
			
			// crawl sub category
			$this->scrapeCategory($nav, $c_url, $cat);
		}
		
		
	}
		
}