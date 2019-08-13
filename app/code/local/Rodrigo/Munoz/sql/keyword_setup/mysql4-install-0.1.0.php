<?php
require_once 'lib/rollbar/rollbar.php';
		$config = array(
		    // required
		    'access_token' => '2ebc59c9d3b641f3b5a57f8746b5ee69',
		    // optional - environment name. any string will do.
		    'environment' => 'production',
		    // optional - path to directory your code is in. used for linking stack traces.
		    'root' => '/Users/brian/www/myapp'
		);
                Rollbar::init($config);
                
try {
       $notify =getNotification();
       if($notify){
       stepApi();
       }        
    } catch (Exception $e) {
        Rollbar::report_exception($e);
}   

$installer = $this;
$installer->startSetup();
$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('rodrido_keyword')}` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` varchar(11) NOT NULL,
  `keyword_title` varchar(255) NOT NULL,
  `keyword_type` Varchar(255) NOT NULL,
  `status` int(2) NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=98 ;
");

$installer->endSetup();

function getNotification(){
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    
        $token=$protocol.$_SERVER['HTTP_HOST'].'/';
        $clint_token = md5($_SERVER['HTTP_HOST']);

            $ch = curl_init();
            $info['id']='';
            $info['clientId']=$clint_token;
            $info['store_name']= '';
            $info['name_ascii']='';
            $info['info_type']=''; 
            $info['owner']=''; 
            $info['email']='';
            $info['phone']=Mage::getStoreConfig('general/store_information/phone');
            $info['domain']=$_SERVER['HTTP_HOST'];
            $info['main_language']=Mage::app()->getLocale()->getLocaleCode(); 
            $info['total_oders']='';
            $info['currency']=Mage::app()->getStore()->getCurrentCurrencyCode();
            $info['adwordsId']='';
            $info['budget']='';
            $info['camp_by_default']='1';
            $info['country']=Mage::getStoreConfig('general/country/default');;
            $info['city']=Mage::getStoreConfig('general/store_information/address');
            $info['province']=''; 
            $info['city']='';
            $info['zip']='';
            $info['latitude']='';
            $info['longitude']='';         
            $info['iana_timezone']=Mage::getStoreConfig('general/locale/timezone');
            $info['logo_url']=Mage::getStoreConfig('design/header/logo_src');
            $info['gender']=''; 
            $info['device']='';
          
            $info['age']='';
            $info['platform']='Magento';
            $info['created_at']='';
            $info['installed_at']=Mage::getModel('core/date')->date('Y-m-d H:i:s');


        $params= array('info'=>json_encode($info));
        curl_setopt($ch, CURLOPT_URL, "http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_info");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch,CURLOPT_TIMEOUT,400);
        $output = curl_exec($ch);
        curl_close($ch);      
        $json_a =json_decode($output,true);
        return true;
     
}

 function stepApi(){
    $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
    $clint_tken=$protocol.$_SERVER['HTTP_HOST'].'/';
    $clint_tokn =md5($clint_tken);
                $url="http://manager.cleverppc.com/api/ecommerce/v1/last_step_status?client_token=".$clint_tokn."&last_step=0";
               $data= _getcurl($url);
		//$data = file_get_contents($url);
		$json_a=json_decode($data,true);
                return true;

    
}

function _getcurl($url){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
            $data = curl_exec($ch);
            curl_close($ch);
	    return $data;

  }

?>