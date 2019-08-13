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
 * Admin_Form_Block
 */
class Rodrigo_Munoz_Block_Adminhtml_Form extends Mage_Core_Block_Template
{
	public function __construct()
  	{	
        parent::__construct();
            
  	}
  
	public function getMediaUrl(){
  		return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'munoz/';
	}

	public function getUrls($url){
		return Mage::helper("adminhtml")->getUrl("munoz/adminhtml_".$url);
	}
	public function getCategoryKeywords(){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_type','category');
		return $collection;

	}
	public function getDomain(){
		$domain=  Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
		return md5($domain);
	}
	public function getAouth(){
        $session_id=$this->getDomain();
        $collection =Mage::getModel('munoz/keyword')->getCollection();
        $collection->addFieldToFilter('keyword_title',$session_id);
        $collection->addFieldToFilter('keyword_type','success');
        return  count($collection);
    }
    
    public function CampaignStatus(){
	$res= Mage::getModel('munoz/keyword')->load('chm_status','category_id');
	return $res->getKeywordType();

    }
    public function add_keywords(){
	$collection =Mage::getModel('munoz/keyword')->getCollection();
	$collection->addFieldToFilter('keyword_type','category');
	
	 $all_cat = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addIsActiveFilter();
             $all_cat->addAttributeToFilter('is_active', 1)
             ->addAttributeToFilter('include_in_menu', 1);
	foreach($all_cat as $keywords){
		$collection->addFieldToFilter('category_id',$keywords->getId());
		if(count($collection) >0){
					$res= Mage::getModel('munoz/keyword')->load($keywords->getId(),'category_id');
					$res->setCategoryId($keywords->getId());
					$res->setKeywordTitle($keywords->getName());
					$res->setKeywordType('category');
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
		}
		else{
					$res= Mage::getModel('munoz/keyword');
					$res->setCategoryId($keywords->getId());
					$res->setKeywordTitle($keywords->getName());
					$res->setKeywordType('category');
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
		}

				    $result = $res->save();
				
		
	}
	
	
	
	
    }
    
    public function add_tips(){
	//print_r($this->getAddTip());
	
	$collection =Mage::getModel('munoz/keyword')->getCollection();
	$collection->addFieldToFilter('keyword_type','calltoaction');
	if(count($collection) >0){
		
	}else{
	foreach($this->getAddTip() as $tips){
		$res= Mage::getModel('munoz/keyword');
		
		$res->setCategoryId('tips');
					$res->setKeywordTitle($tips);
					$res->setKeywordType('calltoaction');
			
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
				$res->save();
		}
		
		
	//}
	}
    }
    
        public function add_Age(){
		$age='["18-24","25-34","35-44","45-54","55-64","65-more"]';
	$collection =Mage::getModel('munoz/keyword')->getCollection();
	$collection->addFieldToFilter('keyword_type','age');
	if(count($collection) >0){
		
	}else{
					$res= Mage::getModel('munoz/keyword');
					$res->setKeywordTitle($age);
					$res->setKeywordType('age');
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
					$res->save();
			
		
	//}
	}
    }
    
    
    
	public function getAddTip(){
	return $adtips=array('Best prices for you','Free shipping and always return','Best Brands, huge catalog');
	
	}
	public function getGender(){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_type','gender');
		if($collection >0){
		  foreach($collection as $data){
		
		    $value =$data->getKeywordTitle();
		  }
		}
		return $value;
	}
	public function getDevice(){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_type','device');
		if($collection >0){
		  foreach($collection as $data){
		
		    $value =$data->getKeywordTitle();
		  }
		}
		return $value;
	}
		public function getAge(){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_type','age');
		if($collection >0){
		  foreach($collection as $data){
		
		    $value =$data->getKeywordTitle();
		  }
		}
		return json_decode($value);
	}
}
?>