<?php
/**
 * LICENSE: $license_text$
 *
 * @author    Rodrigo 
 * @copyright Copyright (c) 2016  Rodrigo
 * @license   $license$
 * @version   $Id$
 */
/**
 * index controller to aouth google adwords api
 */
class Rodrigo_Munoz_Adminhtml_IndexController extends Mage_Adminhtml_Controller_Action
{
	
	protected function _construct()
	{	error_reporting(0);
		require_once 'lib/rollbar/rollbar.php';
		$config = array(
		    // required
		    'access_token' => '2ebc59c9d3b641f3b5a57f8746b5ee69',
		    // optional - environment name. any string will do.
		    'environment' => 'production',
		    // optional - path to directory your code is in. used for linking stack traces.
		    'root' => __DIR__
		);
		Rollbar::init($config);	
	}
	
	const check_dasboard_ready = 'http://manager.cleverppc.com/api/ecommerce/v1/check_dasboard_ready';
	const last_step_status = 'http://manager.cleverppc.com/api/ecommerce/v1/last_step_status';
	const save_magento_client_user = "http://manager.cleverppc.com/api/magento/v1/save_magento_client_user";
	const get_shop_stats_data="http://manager.cleverppc.com/api/ecommerce/v1/get_shop_stats_data";
	const create_ecommerce_info ="http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_info";
	const check_adwords_user_status ="http://manager.cleverppc.com/api/magento/v1/check_adwords_user_status";
	const change_account_status ="http://manager.cleverppc.com/api/magento/v1/change_account_status.json";
	const update_device_info ='http://manager.cleverppc.com/api/ecommerce/v1/update_device_info';
	const update_age_info ='http://manager.cleverppc.com/api/ecommerce/v1/update_age_info';
	const update_gender_info ='http://manager.cleverppc.com/api/ecommerce/v1/update_gender_info';
    
	 public function indexAction(){

		
		try {
			 $info=$this->CreateEcommerceInfo();
		    } catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e, array("my" => "extra", "data" => 42));
		}
		try {
			$data = $this->_getcurl($this->getDashboardApi());
			$json_a=json_decode($data,true);
			$messaage= $json_a['message'];
			if($this->dashboardStatus()=='complete'){
			   $dUrl=self::check_dasboard_ready.'?client_token='.$this->_getClient_id();
			 $data=  $this->_getcurl($dUrl);
			   $json_a=json_decode($data,true);
			   
			if($json_a['dashboard_ready']=='false'){
			//$this->_redirect('*/*/dashboard',$json_a);
			$this->_redirect('munoz/adminhtml_excelsheet/index');
			}
			    else{
					$this->_redirect('*/*/dashboard',$json_a);
			   }
			}
		
			else{
				$this->loadLayout();
				$this->renderLayout();
			}
		
		}
		catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e, array("my" => "extra", "data" => 42));		    
		}
	     
    }

    public function getFormAction(){
	
		$step = $this->getRequest()->getParam('step');
		if($step){
		     $step = $this->getRequest()->getParam('step');
		}
		else{
		     $step=1;
		}
	
		$url=self::last_step_status."?client_token=".$this->_getClient_id()."&last_step=".$step;
		try {
		$data =$this->_getcurl($url);
		$json_a=json_decode($data,true);
		
		} catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e, array("my" => "extra", "data" => 42));
		}
		$this->loadLayout();
		$this->renderLayout();
	
		
    }

    public function _getcurl($url){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_FOLLOWLOCATION,true);
            $data = curl_exec($ch);
            curl_close($ch);
	    return $data;

  }
    
    public function categorytreeAction(){
        $data = $this->getRequest()->getParams();
        $category_model = Mage::getModel("catalog/category");
        $category = $category_model->load($data["CID"]);
        $children = $category->getChildren();
        $all = explode(",",$children);$result_tree = "";$ml = $data["ML"]+20;$count = 1;$total = count($all);
        $plus = 0;
        
        foreach($all as $each){
            $count++;
            $_category = $category_model->load($each);
            if(count($category_model->getResource()->getAllChildren($category_model->load($each)))-1 > 0){
                $result[$plus]['counting']=1;           
            }else{
                $result[$plus]['counting']=0;
            }
            $result[$plus]['id']= $_category['entity_id'];
            $result[$plus]['name']= $_category->getName();

            $categories = explode(",",$data["CATS"]);
            if($data["CATS"] && in_array($_category["entity_id"],$categories)){
                $result[$plus]['check']= 1;
            }else{
                $result[$plus]['check']= 0;
            }
            $plus++;
        }
        echo json_encode($result);
    }
    
        public function SetCategoryAction(){
	$Black_list  = array("","Default Category","home page","homepage","Frontpage","Brands","All","New products","New release","New Arrivals","sale","sales","On sale","New Arrival","All products","Products","Man","Men","Woman","Accessories","VIP");
	 Mage::getSingleton('core/session')->unsCatids();
        $cate=array();
        $data = $this->getRequest()->getParams();

            $all_cat = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addIsActiveFilter()
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('position', 'desc');

	if($data['Cat']){
		$preId=$data['Cat'];
		$all_cat->addIdFilter($data['Cat']);
        }
	 
         if($data['unselct']){
		//echo "unselect";
		 $all= Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addIsActiveFilter()
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToSort('position', 'desc');
		foreach($all as $select_cat){
		if(!in_array($select_cat->getName(), $Black_list)){
		array_push($cate,$select_cat->getId());
		}
		}
		$final_cat = array_diff($cate, $data['unselct']);
		$all_cat->addIdFilter($final_cat);
		$preId=$final_cat;
	
         }

	//echo $all_cat->getSize();
	
        foreach ($all_cat as $_category) { 
             $html .= "<div class='add-key-word'>";
             $html .= "<input class='key_save_button add-key-word'  value='Save' type='button'> </button>";
               
             $html .= "<input class='add-key-word' value='".$_category->getName()."' type='text' name='addkeyword_".$_category->getId()."'>";
             $html .= "<i onclick ='RemoveTextBoxKey(this)' class='fa fa-trash-o fa-2x' aria-hidden='true'></i>";
             $html .=  " </div>";
               
         }
	Mage::getSingleton('core/session')->setCatids($preId);
	echo $html; 
	}
	
	
	
	public function getCurlresponseAction(){
        
	$data = $this->getRequest()->getParams();
	//$postData = $data['session_id'];
   
	try {
		$url=self::check_adwords_user_status."?client_token=".$this->_getClient_id();
		$data = $this->_getcurl($url);
		//$data = file_get_contents($url);
		$json_a=json_decode($data,true);
                echo  $messaage= $json_a['message'];
		if($messaage=='success'){
			$session_id =$this->_getClient_id();
			$this->SetResponse($messaage,$session_id);
		}	
	} catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e);
		}
	return $messaage= $json_a['message'];
   
	 
           
	}
	
	public function setUrlresponseAction(){
		$wholekeyword=$this->getRequest()->getParams();
	}
	
	public function saveKeywordAction(){
                $wholekeyword=$this->getRequest()->getParams();
		try {
			   $data =Mage::getModel('munoz/keyword')->saveKeyword($wholekeyword);
			   if($data){
			       echo json_encode(array('success'=>true,'response'=>$data));
			   }
			   else{
			      echo json_encode(array('success'=>false,'response'=>'error'));
			   }
		  } catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e);
		}
		
            }

        public function deleteKeywordAction(){
		//$data='';
            $wholekeyword=$this->getRequest()->getParams();
		if($wholekeyword['id']){
		$delete= Mage::getModel('munoz/keyword')->load($wholekeyword['id'])->delete();
              if($delete){
		echo json_encode(array('success'=>true,'response'=>'Keywords deleted Successfully'));
	      }
	      else{
		echo json_encode(array('success'=>false,'response'=>'Keywords not found'));
	      }
            }
      

        }
	 public function SetResponse($messaage,$session_id){
		  try {
			  $data =Mage::getModel('munoz/keyword')->saveResponse($messaage,$session_id);
			  
		  } catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e);
		  }
		 return $data;
		 
	 }
	

	
	public function setParameterAction(){
		try {
			$client_info= $this->getDetail();
			$params=['client_info'=>json_encode($client_info)];
			$json_a =$this->_postCurl(self::save_magento_client_user ,$params);
			$this->_googleAdwordsId($json_a);
			echo  $messaage= $json_a['message'];
			
		    } catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e);
		}
        }
	
	public function getDetail(){
		$userArray = Mage::getSingleton('admin/session')->getData();
		$user = Mage::getSingleton('admin/session'); 
		$userEmail = $user->getUser()->getEmail();
		$userName = $user->getUser()->getFirstname()." ".$user->getUser()->getLastname();
		$comName=Mage::getStoreConfig('general/store_information/name');
		$countryCode = Mage::getStoreConfig('general/country/default');
		$domain= $_SERVER['HTTP_HOST'];
        
		return $clint_info=array('name'=>$userName,'email'=>$userEmail,'company_name'=>$comName,'domain'=>$domain,'currency'=>$countryCode,'client_token'=>$this->_getClient_id());
        
        }
	
	public function dashboardAction(){
	$this->loadLayout();
        $this->renderLayout();
		
	}
	
	public function getDashboardApi(){
		return self::get_shop_stats_data."?client_token=".$this->_getClient_id();
	}
	
	public function campaign_statusAction(){
	$status=$this->getRequest()->getParam('status');
	$params=['client_token'=>$this->_getClient_id(),'status'=>$status];
	$json_a= $this->_postCurl(self::change_account_status ,$params);
	 try {
		  $json_a= $this->_postCurl(self::change_account_status ,$params);
		  $data =Mage::getModel('munoz/keyword')->saveCampaignStatus($json_a);
		  } catch (Exception $e) {
			Rollbar::report_exception($e);
		}
	 		 
	 return $json_a['status'];
	
	}
	 public function dashboardStatus(){
		$res= Mage::getModel('munoz/keyword')->load('complete','keyword_title');
		return $res->getKeywordTitle();
	  }
    
    public function SetcleintInfoAction(){
	$stores =array();
	foreach(Mage::app()->getStores() as $store) {
		array_push($stores,$store->getName().',');
	}
		$all_store =Mage::getStoreConfig('general/store_information/name');
		$ch = curl_init();
		$info['id']='';
		$info['clientId']=$this->_getClient_id();
		$info['store_name']= Mage::getStoreConfig('general/store_information/name');
		$info['name_ascii']='';
		$info['info_type']=''; 
		$info['owner']=$this->getDetail()['name']; 
		$info['email']=$this->getDetail()['email'];
		$info['phone']=Mage::getStoreConfig('general/store_information/phone');
		$info['domain']=$_SERVER['HTTP_HOST'];
		$info['main_language']=Mage::app()->getLocale()->getLocaleCode(); 
         //   $info['countries']=$countries; 
          
            $info['total_oders']=$this->getTotalorders();
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
	 $params= array('info'=>json_encode($info)); 
	try {
		$json_a=$this->_postCurl(self::create_ecommerce_info ,$params);
		echo $json_a['message'];
			
		} catch (Exception $e) {
		       // Rollbar::report_exception($e);
			Rollbar::report_exception($e);
		}	    
	}
    
	public function getTotalorders(){ 
        $salesModel=Mage::getModel("sales/order")->getCollection();
        return count($salesModel);

    }
    
    public function BackGroundProcessAction(){
	$wholekeyword=$this->getRequest()->getParams();
		if($wholekeyword['budget']){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_title','complete');
	 	if(count($collection) >0){
		  return true;
		  }
		  else{
		  require_once 'shell/createInstance.php';
		  exec('php -f createInstance.php > /dev/null 2>&1 &'); 
		  }
		}
	
	 
    }
    
    public function CreateEcommerceInfo(){
		
		$info['id']='';
		$info['clientId']=$this->_getClient_id();
		$info['store_name']= Mage::getStoreConfig('general/store_information/name');
		$info['name_ascii']='';
		$info['info_type']=''; 
		$info['owner']=$this->getDetail()['name']; 
		$info['email']=$this->getDetail()['email'];
		$info['phone']=Mage::getStoreConfig('general/store_information/phone');
		$info['domain']=$_SERVER['HTTP_HOST'];
		$info['main_language']=Mage::app()->getLocale()->getLocaleCode(); 
         //   $info['countries']=$countries; 
          
            $info['total_oders']=$this->getTotalorders();
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
            $info['logo_url']=$this->_getLogoUrls();
            $info['gender']=''; 
            $info['device']='';
          
            $info['age']='';
            $info['platform']='Magento';
           // $info['created_at']='';
         //   $info['installed_at']='';
	 $params= array('info'=>json_encode($info));
	return $this->_postCurl(self::create_ecommerce_info, $params);	
    }
    
		  public function _getLogoUrls(){

		$logo_src = Mage::getStoreConfig('design/header/logo_src');
		$design = Mage::getDesign();
		$design->setArea('frontend');
		$logo_url = $design->getSkinUrl($logo_src);
		$design->setArea('adminhtml');
		return $logo_url;

	
    }
    public function _googleAdwordsId($res){

	$price =$this->_allProductPrice();
	$design =Mage::getBaseDir('design').'/frontend/base/default/template/munoz/google/adwords/';
	$save_remarket_tag=$this->_remarketTag($design, $res['tag_id']);
	$convertaion =$design.'conversion.phtml';
	$myfile = fopen($convertaion, "w") or die("Unable to open file!");
	$snipt_code =str_replace("1.00",$price,$res['conversion_snippet_tag']);
	fwrite($myfile, $snipt_code);
	fclose($myfile);
	return true;		
    }
    
    public function _allProductPrice(){
	   
	$collection=  Mage::getResourceModel('catalog/product_collection')->addAttributeToSelect('price')->addAttributeToFilter('status','1');
	$sum = 0;
	$count = 0;
	foreach ($collection as $_product) {
	 $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
	    if ($stock>=1) {
	     $sum +=$_product->getPrice()*$stock;
		 $count +=$stock;
	    }
	}
		return $sum;
    }
    
    public function _remarketTag($design ,$tag_id){
	$remarketingtagFile=$design.'remarket_tag.phtml';
	$file = file_get_contents($remarketingtagFile);
	$new_file = str_replace('ramrket_id',$tag_id, $file);
	file_put_contents($remarketingtagFile,$new_file);
	return true;
    }
    
	public function SetAccurateValueAction(){
	$url='';
	
	$param=$this->getRequest()->getParams();
        if($param['values']=='device'){
            $url=self::update_device_info.'?device='.$param['slide'];    
	}
	if($param['values']=='gender'){
	
	$url=self::update_gender_info.'?gender='.$param['slide'];
	}
	if($param['values']=='age'){
		
	$age= implode(",",$param['slide']);
	
	$url=self::update_age_info.'?age='.$age;
	}
	 $main_url= $url.'&client_token='.$this->_getClient_id();
	
	 $data = $this->_getcurl($main_url);
	//$data = file_get_contents($main_url);
	$json_a=json_decode($data,true);
	if($json_a['message']=='success'){
	$data =Mage::getModel('munoz/keyword')->saveAccuRate($param);
	}
		
    
}
	public function _postCurl($url , $params){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
		curl_setopt($ch,CURLOPT_TIMEOUT,400);
		// $output contains the output string
		$output = curl_exec($ch);
		curl_close($ch);
		
		 return $json_a =json_decode($output,true);
		 
		
	}
	
	
	 public function _getClient_id() {
	 $url=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	    $disallowed = array('http://', 'https://');
	    foreach($disallowed as $d) {
	       if(strpos($url, $d) === 0) {
		  $u =str_replace($d, '', $url);
		 $url =str_replace('/', '', $u);
		  return md5($url);
	       }
	    }
	    return md5($url);
	 }
	 


}
?>