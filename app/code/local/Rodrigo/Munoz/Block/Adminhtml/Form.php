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

	public function getUrl($url){
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



}
?>