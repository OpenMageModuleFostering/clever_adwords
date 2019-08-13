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
 * model for get all save keywords
 */

class Rodrigo_Munoz_Model_Keyword extends Mage_Core_Model_Abstract{	
	public function _construct() { 
	parent::_construct();       
	   $this->_init('munoz/keyword','id');   
	}  

	public function saveKeyword($data){
				$msg='';
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter( 'category_id', $data['id'] );
		if(count($collection)){	
			$coll = Mage::getModel('munoz/keyword')->load($data['id'],'category_id');  
			$coll->setCategoryId($data['id']);
			$coll->setKeywordTitle($data['value']);
			$coll->setKeywordType($data['type']);
			$coll->setStatus(1);
			$coll->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
			try
				{
				    $result = $coll->save();
				}
				catch(Exception $e)
				{
				    $error=$e;
				}

				if($result->getId()){
					if($data['type']=='calltoaction'){
					$msg="Tips Update successfully";	
					}
					else{
					$msg="Keyword Update successfully";
					}
					 
					
				}

		}
		else{
			$keyword =Mage::getModel('munoz/keyword');
			$keyword->setCategoryId($data['id']);
			$keyword->setKeywordTitle($data['value']);
			$keyword->setKeywordType($data['type']);
			$keyword->setStatus(1);
			$keyword->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));

				try
				{
				    $result = $keyword->save();
				}
				catch(Exception $e)
				{
				    $error=$e;
				}

				if($result->getId()){
					if($data['type']=='calltoaction'){
					$msg="Tips Update successfully";
					}
					else{
						$msg="Keyword Inserted successfully";
					}
				}
		}

		return $msg;
	
		}
		public function saveResponse($message,$session_id){
			
			//echo $message;
			//echo $session_id;
			if($message){
				$collection =Mage::getModel('munoz/keyword')->getCollection();
				$collection->addFieldToFilter('keyword_title',$session_id);
				$collection->addFieldToFilter('keyword_type',$message);
				//echo count($collection);
				if(count($collection)>0){
					$res= Mage::getModel('munoz/keyword')->load($session_id,'keyword_title');
					$res->setKeywordTitle($session_id);
					$res->setKeywordType($message);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
				}
				else{
					$res =Mage::getModel('munoz/keyword');
					$res->setKeywordTitle($session_id);
					$res->setKeywordType($message);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
				}
				try
				{
				    $result = $res->save();
				}
				catch(Exception $e)
				{
				    $error=$e;
				}
				
			}
			return true;
		}
		public function saveCampaignStatus($data){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_title',$data['account']);
		if(count($collection)>0){
					$res= Mage::getModel('munoz/keyword')->load($data['account'],'keyword_title');
					$res->setCategoryId('chm_status');
					$res->setKeywordTitle($data['account']);
					$res->setKeywordType($data['status']);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
				}
				else{
					$res =Mage::getModel('munoz/keyword');
					$res->setCategoryId('chm_status');
					$res->setKeywordTitle($data['account']);
					$res->setKeywordType($data['status']);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
				}
				try
				{
				    $result = $res->save();
				}
				catch(Exception $e)
				{
				    $error=$e;
				}
				return true;
			
		}
		
		public function saveAccuRate($param){
		$collection =Mage::getModel('munoz/keyword')->getCollection();
		$collection->addFieldToFilter('keyword_type',$param['values']);
		
		$response= json_encode($param['slide']);
		if(count($collection)>0){
			                
					$res= Mage::getModel('munoz/keyword')->load($param['values'],'keyword_type');
					$res->setKeywordTitle($response);
					$res->setKeywordType($param['values']);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
			
			
		}else{
					$res =Mage::getModel('munoz/keyword');
					$res->setKeywordTitle($response);
					$res->setKeywordType($param['values']);
					$res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
		}
		
		try
				{
				    $result = $res->save();
				}
				catch(Exception $e)
				{
				    echo $error=$e;
				}
				return true;
			
		}

		
}
?>