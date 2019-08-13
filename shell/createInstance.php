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
 * send category product and all information in API
 */
require_once 'app/Mage.php';
$app = Mage::app('admin');
umask(0);
require_once 'shell/abstracts.php';
require_once 'shell/defaultAttribute.php';
class Rodrigo_Shell_MyApi extends Mage_Shell_Abstract
{
    protected $_argname = array();
    public  $prd_atr_code= Array();
    const check_finish_process_time ='http://manager.cleverppc.com/api/ecommerce/v1/check_finish_process_time';
    const create_ecommerce_country ='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_country';
    const create_ecommerce_order= 'http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_order';
    const create_ecommerce_keyword ='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_keyword';
    const create_ecommerce_ad_tip='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_ad_tip';
    const create_ecommerce_name='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_name';
    const create_ecommerce_rel_prod_opt='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_rel_prod_opt';
    const create_ecommerce_rel_prod_coll ='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_rel_prod_coll';
    const create_ecommerce_dim_prod ='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_dim_prod';
    const create_ecommerce_info='http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_info';
    
    public function __construct($wholekeyword) {
       // parent::__construct();
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
	    //ignore_user_abort(true);
	    //set_time_limit(0);
	try {
	    $stat_time_api=$this->stat_time_api();
	    } catch (Exception $e) {
	     Rollbar::report_exception($e);
	    }     
	try {
	    $step=$this->setSteps();
	    } catch (Exception $e) {
	    Rollbar::report_exception($e);
	}
        $store_creat_date='';
        $xml = simplexml_load_file('app/etc/local.xml');     
        if(isset($xml->global->install->date)) {
            $store_creat_date=  (string)$xml->global->install->date;
        }   
        $now = Mage::getModel('core/date')->timestamp(time());    
        $countries='';
        $Allprodcut=$this->getProductById($wholekeyword['checkecCat'],$wholekeyword['unselectCat']);
        $Allcategory=$this->getCategory($wholekeyword['checkecCat'],$wholekeyword['unselectCat']);
        $budget=$wholekeyword['budget'];
        if($wholekeyword['countries'])
        {
          $countries=$wholekeyword['countries'];
        }
        else{
          $countries=Mage::getStoreConfig('general/country/default');
        }
        $count=$this->sendCountry($countries);
       $category =$this->getCategoryKeywords();  // get Category keyword
       $tips=$this->getAddTips(); // get Added Tip keyword
       $order=$this->getOrders(); // get orders 
       $admin=$this->getAdminDetail();
	try {
		  $setFirstData=  $this->setDataInFirstApi($Allcategory,$order,$admin,$tips,$category,$budget,$countries,$store_creat_date);
		 } catch (Exception $e) {
		     Rollbar::report_exception($e);
	     } 
	try {
		  $this->ad_tips();
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    }    
	try {
		  $this->keywords();
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    }      
	try {
		  $this->orderApi();
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    } 
	try {
	      $this->nameApi();
	      } catch (Exception $e) {
		  Rollbar::report_exception($e);
	  }
	try {
		$dimOption=$this->dimOption(); // set the prodcut option in api
	       } catch (Exception $e) {
		   Rollbar::report_exception($e);
	} 
	try {
		  $this->rel_prod_opt($Allprodcut);
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    }      
	try {
	      $dim_coll=  $this->setDataDimColl();
	    } catch (Exception $e) {
		Rollbar::report_exception($e);
	} 	
	try {
		 $rellProductColl=$this->rellProductColl($Allprodcut); // set the prodcut category in api'
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    }
	try {
		$this->end_time_api(); 
		} catch (Exception $e) {
		    Rollbar::report_exception($e);
	    }	
	try {
		$this->saveStaus();
	    }catch (Exception $e) {
		Rollbar::report_exception($e);
	}	   
        ini_set('max_execution_time', 3000); 
        // Time limit to infinity  
        // Get command line argument named "argname"
        // Accepts multiple values (comma separated)
        if($this->getArg('argname')) {
            $this->_argname = array_merge(
                $this->_argname,
                array_map(
                    'trim',
                    explode(',', $this->getArg('argname'))
                )
            );
        }
    }
    // Shell script point of entry
    public function run() {
       
       
    }
 
    // Usage instructions
    public function usageHelp()
    {
       
        return <<<USAGE
        Usage:  php -f createInstance.php -- [options]
         
          --argname <argvalue>       Argument description
         
          help                   This help
 
USAGE;

    }
    /*************************************/
# fetching total complete orders count
/*************************************/
public function getTotalorders(){ 
    $time = time();
    $to = date('Y-m-d H:i:s', $time);
    $lastTime = $time - 86400; // 60*60*24*30
    $from = date('Y-m-d H:i:s', $lastTime);
    $order_items = Mage::getResourceModel('sales/order_item_collection')
	->addAttributeToSelect('order_id')
	->addAttributeToSelect('created_at')
       ->addFieldToFilter('created_at', array(
	'from'     => strtotime('-30 day', time()),
	'to'       => time(),
	'datetime' => true
      ))
	->load();
    return count($order_items);
}
/*************************************/
# fetching category keywords  saved
#  by user
/*************************************/
public function getCategoryKeywords(){ 
    $collection =Mage::getModel('munoz/keyword')->getCollection();
    $collection->addFieldToFilter('keyword_type','category');
    return $collection;
}
/*************************************/
# fetching tips saved by user
/*************************************/
public function getAddTips(){ // fetching tips saved by user
    $keyword =Mage::getModel('munoz/keyword')->getCollection(); 
    $keyword->addFieldToFilter('keyword_type','calltoaction');
    return $keyword;   
}
/*************************************/
# fetching orders
/*************************************/
public function getOrders(){
    $collection = Mage::getModel('sales/order')->getCollection()
    ->addFieldToFilter('status', 'complete');
    $collection->getSelect()->order('entity_id DESC')->limit(50);
    return $collection;
}   
/*************************************/
# fetching Admin Detail 
/*************************************/   
public function getAdminDetail(){
    $userArray = Mage::getSingleton('admin/session')->getData();
    // get individual data
    return $user = Mage::getSingleton('admin/session'); 
}
/*************************************/
# fetching category selected by users  
/*************************************/ 
public function getCategory($check, $unselect){
    $cate=array();
    $all_cat = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addIsActiveFilter()
	->addAttributeToFilter('is_active', 1)
	->addAttributeToSort('position', 'desc');
    if($check){
	$all_cat->addIdFilter($check);
    }
    if($unselect){     
	$all= Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('*')->addIsActiveFilter()
	->addAttributeToFilter('is_active', 1)
	->addAttributeToSort('position', 'desc');
	foreach($all as $select_cat){
	array_push($cate,$select_cat->getId());
	}
	$final_cat = array_diff($cate, $unselect);
	$all_cat->addIdFilter($final_cat);
	$preId=$final_cat;
    }
    return $all_cat;
}
/*************************************/
# fetching product  selected categories  
/*************************************/   
public function getProductById($check, $unselect){
    set_time_limit(600);
    return $collection = Mage::getModel('catalog/product')
	->getCollection()
	->addAttributeToFilter('status', array('eq' => 1))
	//->setPageSize(20)
	->addAttributeToSelect(array('name','price'))
	->joinField('qty',
	'cataloginventory/stock_item',
	'qty',
	'product_id=entity_id',
	'{{table}}.stock_id=1',
	'left'
	)->addAttributeToFilter('qty', array('gt' => 0));
}
/*************************************/
# fetching all 2 level  category 
/*************************************/
public function getAllLevelCat(){
    $categories = Mage::getModel('catalog/category')->getCollection()
    ->addAttributeToSelect('*')//or you can just add some attributes
    ->addAttributeToFilter('level', 2);//2 is actually the first level
    return $categories;
}  
/*************************************/
# fetching children category by id
/*************************************/
public function getTreeCat($id){     
    return $children = Mage::getModel('catalog/category')->load($id)->getChildrenCategories();
}
/*************************************/
# fetching custom attribute
/*************************************/ 
public function AttributeOptionArray()
{
   return  $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->getItems();                     
}
/*************************************/
# fetching attribute value by
#attribute code
/*************************************/ 
public function getAtrValue($code){
    $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product',$code);
    $collection =Mage::getResourceModel('eav/entity_attribute_option_collection')
	->setPositionOrder('asc')
	->setAttributeFilter($attributeId)
	->setStoreFilter(0)
	->load();                  
    return $collection = $collection->toOptionArray();
}
/*************************************/
# fetching product tag custmer and admin
/*************************************/ 
public function TagProduct(){
    $tag_model= Mage::getModel('tag/tag');
    $tag_collection= $tag_model->getResourceCollection()->load();
    return $mytags=$tag_collection->getItems();
}
/*************************************/
# fetching product by tag id
/*************************************/

public function getProductIdByTag($tagId){
    $collection = Mage::getResourceModel('tag/product_collection') ->addTagFilter($tagId);
    foreach($collection->getData() as $prd)
    {
    return $prd['entity_id'];
    }
}
/*************************************/
# fetching all category 
/*************************************/
function getTreeCategories($parentId, $isChild){
    $allCats = Mage::getModel('catalog/category')->getCollection()
	->addAttributeToSelect('*')
	->addAttributeToFilter('is_active','1')
	->addAttributeToFilter('include_in_menu','1')
	->addAttributeToFilter('parent_id',array('eq' => $parentId));
    return $allCats;
}
/*************************************/
# fetching product rellational category
/*************************************/
public function getRelationalCat($productIds){
    $cat =Mage::getModel('catalog/category')->load($productIds);
    return   $cat->getId();
   
}
/*************************************/
# fetching product aatribute
#by product id
/*************************************/

public function getProductAtribute($productId){
    $attr_array=array();
    $product = Mage::getModel('catalog/product')->load($productId);
    //$attributes = $product->getAttributes();
    $count=1;
    foreach($this->prd_atr_code as $attrcode){
	$attribute_value = $product->getData($attrcode);
	if(!$attribute_value){
	    $attribute_value='null';
	}
	array_push($attr_array,array('value'=>$attribute_value,'label'=>'opt'.$count.'_id','pid'=>$productId));
	$count++; 
    }
    return $attr_array;
}
/*************************************/
# fetching product stock 
#by product id
/*************************************/
public function getPorductStock($product_id){
    $model = Mage::getModel('catalog/product'); 
    $_product = $model->load($product_id); 
    return $stocklevel = (int)Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
}
/*************************************/
# fetching product ids by category id 
/*************************************/
public function getProductIds($catid){
    $category = new Mage_Catalog_Model_Category();
    $category->load($catid); //My category id is 6
    $prodCollection = $category->getProductCollection();
    foreach ($prodCollection as $product) {
    $prdIds[] = $product->getId(); //Array to store all the product ids
    }
    return $prdIds;
}
/**********************************/
# First API send basic information
# product categorry relations
/*********************************/

public function setDataInFirstApi($Allcategory,$order,$admin,$tips,$category,$budget,$countries,$created){
    $stores =array();
    foreach(Mage::app()->getStores() as $store) {
	array_push($stores,$store->getName().',');
    }
    $all_store =implode(" ",$stores);
    $info['id']='';
    $info['clientId']=$this->_getClient_id();
    $info['name_ascii']='';
    $info['info_type']=''; 
    $info['owner']=$admin->getUser()->getFirstname() .' '.$admin->getUser()->getLastname(); 
    $info['email']=$admin->getUser()->getEmail();
    $info['phone']=Mage::getStoreConfig('general/store_information/phone');
    $info['domain']=$_SERVER['HTTP_HOST'];
    $info['main_language']=Mage::app()->getLocale()->getLocaleCode(); 
    $info['currency']=Mage::app()->getStore()->getCurrentCurrencyCode();
    $info['adwordsId']='';
    $info['budget']=$budget;
    $info['camp_by_default']='1';
    $info['country']=Mage::getStoreConfig('general/country/default');;
    $info['city']=Mage::getStoreConfig('general/store_information/address');
    $info['province']=''; 
    $info['city']='';
    $info['zip']='';
    $info['latitude']='';
    $info['longitude']=''; 
    $info['iana_timezone']=Mage::getStoreConfig('general/locale/timezone');
    $info['gender']=''; 
    $info['device']='';
    $info['age']='';
    $info['platform']='Magento';
    $info['created_at']=$created;
    $params= array('info'=>json_encode($info));
    $this->_postCurl(self::create_ecommerce_info ,$params);

}
/***********************/
#  Second Api send Dim_prod
# product categorry relations
/***********************/
public function setDataDimColl(){
    $cat_id=array();
    $treeacat_id=array(); 
    for( $catLevel=1; $catLevel <= 5; $catLevel++ ){ // loop for
	if($catLevel >1){ // second dim_coll
	    $treeacat_id=array();
	    $i=0;
	    if(!empty($cat_id)){
		foreach($cat_id as $treeCat){
		    $all_Tree_cat = $this->getTreeCat($treeCat);
		    foreach($all_Tree_cat as $Cat_Tree){
			array_push($treeacat_id,$Cat_Tree->getId());
			$dim_colls[$i]['id'] ='';
			$dim_colls[$i]['clientId']=$this->_getClient_id();
			$dim_colls[$i]['product_ids']=$this->getProductIds($Cat_Tree->getId());
			$dim_colls[$i]['collId']= $Cat_Tree->getId();
			$dim_colls[$i]['coll_name']=$Cat_Tree->getName();
			$dim_colls[$i]['published']= $Cat_Tree->getIsActive();
			$dim_colls[$i]['main_image']=$Cat_Tree->getThumbnail();
			$dim_colls[$i]['coll_url']=$Cat_Tree->getUrl();
			$dim_colls[$i]['parent_id']=$this->_getParentId($Cat_Tree->getId());
			$dim_colls[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
			$i++;
		    }
		    $params= array('dim_coll'.$catLevel=>json_encode($dim_colls));
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_dim_coll".$catLevel);
		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, true);
		//    curl_setopt($ch, CURLOPT_HTTPGET, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
		curl_setopt($ch,CURLOPT_TIMEOUT,400);
		$output = curl_exec($ch);
		curl_close($ch);      
		$json_a =json_decode($output,true);
		$dim_colls=null;    
	    }
	    $cat_id=$treeacat_id;
	    // api six, seven ,eight, nine
	}// end catlavel  
	else{   
	    $all_cat = $this->getAllLevelCat();
	    if(count($all_cat) >0){
		$j=0;
		foreach ($all_cat as $category_first_level){
		    $dim_coll[$j]['id']='';
		    $dim_coll[$j]['clientId']=$this->_getClient_id();
		    $dim_coll[$j]['coll_level'] ='1';
		    $dim_coll[$j]['collId']= $category_first_level->getId();
		    $dim_coll[$j]['product_ids']= $this->getProductIds($category_first_level->getId());
		    $dim_coll[$j]['coll_name']=$category_first_level->getName();
		    $dim_coll[$j]['coll_url']=$category_first_level->getUrl();
		    $dim_coll[$j]['published']= $category_first_level->getIsActive();
		    $dim_coll[$j]['main_image']=$category_first_level->getThumbnail();
		    $dim_coll[$j]['parent_id']='0';
		    $dim_coll[$j]['lang']=Mage::app()->getLocale()->getLocaleCode();
		    array_push($cat_id,$category_first_level->getId());
		    $j++;
		} // end foreach
	    }  // end if 
	    $params= array('dim_coll1'=>json_encode($dim_coll));
	    $this->_postCurl('http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_dim_coll1' ,$params);	
	}// end else   
    } // end main foreach loop
}
/***********************/
# Third API
# product option relations
/***********************/
public function dimOption(){
    $count=1;
    $product_attribute =$this->AttributeOptionArray();
    if(count($product_attribute) >0){
	foreach($product_attribute as $prdAtr){
	    if(!in_array($prdAtr->getAttributecode(),getDefaultAttribute())){
	    $attributeValue=$this->getAtrValue($prdAtr->getAttributecode());
		if(count($attributeValue) > 0){
		    if($count < 11){
			$i=0;
			array_push($this->prd_atr_code, $prdAtr->getAttributecode());
			    foreach($attributeValue  as $option){
			    $dimProduct[$i]['id']='';
			    $dimProduct[$i]['clientId']=$this->_getClient_id();
			    $dimProduct[$i]['optId']=$option['value'];
			    $dimProduct[$i]['opt_name']=$option['label'];
			    $dimProduct[$i]['opt_url']='';
			    $dimProduct[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
			    $i++;
			}
			$params= array('dim_opt'.$count=>json_encode($dimProduct));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "http://manager.cleverppc.com/api/ecommerce/v1/create_ecommerce_dim_opt".$count);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, true);
			
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$output = curl_exec($ch);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
			curl_setopt($ch,CURLOPT_TIMEOUT,0);
			// close curl resource to free up system resources
			curl_close($ch);      
			$json_a =json_decode($output,true);
			$count++;
			$dimProduct=null;
		    }// end for each		
		}
	    } // end In_array
	}// end foreach
    } // end count

      
}
/***********************/
# fourth API send Dim_prod
# product categorry relations
# product option relations
# product name relations
/***********************/
public function rellProductColl($Allprodcut){
       // set_time_limit(600);
    $i=0;
    $prd_name=array();
    foreach ($Allprodcut as  $product) {
	//if(($product->getName() > 0) && ($product->getQty() >0)){
	if (!in_array($product->getName(), $prd_name)) {
	    $dim_prod[$i]['id']='';
	    $dim_prod[$i]['clientId']=$this->_getClient_id();
	    $dim_prod[$i]['prodId']=$product->getId();
	    $dim_prod[$i]['prod_name']=$product->getName();
	    $dim_prod[$i]['prod_url']=$product->getProductUrl();
	    $dim_prod[$i]['prod_price']=$product->getPrice();
	    $dim_prod[$i]['barcode']='';
	    $dim_prod[$i]['sku']=$product->getSku();
	    $dim_prod[$i]['meta']=$product->getMetaKeyword();
	    $dim_prod[$i]['published']=$product->getStatus();
	    $dim_prod[$i]['inv']='';
	    $dim_prod[$i]['inv_num']=$product->getQty();
	    $dim_prod[$i]['updated']=$product->getUpdatedAt();
	    $dim_prod[$i]['main_image']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
	    $dim_prod[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
	    array_push($prd_name,$product->getName());
	    $i++;
	}
    }	     
	// }             
    $params= array('dim_prod'=>json_encode($dim_prod));
    $this->_postCurl(self::create_ecommerce_dim_prod ,$params); 
}
/***********************/
# product categorry relations
/***********************/
public function rel_prod_coll($Allprodcut){
    $i=0;
    if(count($Allprodcut) >0){  // product category collections
	foreach($Allprodcut as $rellAtionPro){
	    if(count($rellAtionPro->getCategoryIds()) >0){
		$coll_id=1;
		foreach ($rellAtionPro->getCategoryIds() as $rellCatIds){
			     $rel_prod_coll[$i]['id']='';
			     $rel_prod_coll[$i]['clientId']=$this->_getClient_id();
			     $rel_prod_coll[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
			     $rel_prod_coll[$i]['prodId']=$rellAtionPro->getId();
			     $rel_prod_coll[$i]['coll'.$coll_id.'Id']=$this->getRelationalCat($rellCatIds);
			     $coll_id++;                   
		}
	     
	    }
	    $i++;            
	}
    }  // end product categorry collections
    $params= array('rel_prod_coll'=>json_encode(array_values($rel_prod_coll)));
    $this->_postCurl(self::create_ecommerce_rel_prod_coll ,$params);
			
}
/***********************/
# product categorry relations
/***********************/
public function rel_prod_opt($Allprodcut){
    if(count($Allprodcut) >0){ // prodcut option 
	$k=0;
	$prdt_name=array();
	foreach($Allprodcut as $rellAttrProdcut){
	    if (!in_array($rellAttrProdcut->getName(), $prdt_name)) {
		$rellProAtr =$this->getProductAtribute($rellAttrProdcut->getId());
		    if(count($rellProAtr) >0){
		    $attrCol=1;
		    foreach($rellProAtr as $atrFetch){
			$rel_prod_opt[$k]['id']='';
			$rel_prod_opt[$k]['clientId']=$this->_getClient_id();
			$rel_prod_opt[$k]['lang']=Mage::app()->getLocale()->getLocaleCode();
			$rel_prod_opt[$k]['prodId']=$rellAttrProdcut->getId();
			$rel_prod_opt[$k]['opt'.$attrCol.'Id']=$atrFetch['value'];
			$attrCol++;
		    }// end foreach     
		}
	     
	    }else{
		$rellProAtr =$this->getProductAtribute($rellAttrProdcut->getId());
		if(count($rellProAtr) >0){
		    $attrCol=1;
		    foreach($rellProAtr as $atrFetch){
			$rel_prod_opt[$k]['id']='';
			$rel_prod_opt[$k]['clientId']=$this->_getClient_id();
			$rel_prod_opt[$k]['lang']=Mage::app()->getLocale()->getLocaleCode();
			$rel_prod_opt[$k]['prodId']= end($prdt_name);
			$rel_prod_opt[$k]['opt'.$attrCol.'Id']=$atrFetch['value'];
			$attrCol++;
		    }// end foreach     
		}
				  
	    }
	    array_push($prdt_name,$rellAttrProdcut->getName(),$rellAttrProdcut->getId());
	      // end foreach
	    $k++;
	     
	}
	   
    } //end  prodcut option
	$rell_pro_opts= array('rel_prod_opt'=>json_encode(array_values($rel_prod_opt)));
	$rel =$this->_postCurl(self::create_ecommerce_rel_prod_opt ,$rell_pro_opts);
}
/***********************/
# second API send name
/***********************/
public function nameApi(){
    $m=0;
    $rel_prod_name[0]['Id']='';
    $rel_prod_name[0]['clientId']=$this->_getClient_id();
    for($i=1; $i<6; $i++){
         $rel_prod_name[0]['coll'.$i.'_id']='';
    }
    
    $j=1;
    foreach($this->prd_atr_code as $prdatrr){
        $rel_prod_name[0]['opt'.$j.'_id']=$prdatrr;
        $j++;
    }
    $rel_prod_name[0]['lang']=Mage::app()->getLocale()->getLocaleCode();
    $name= array('name'=>json_encode(array_values($rel_prod_name)));
    $this->_postCurl(self::create_ecommerce_name ,$name);
               
}

/***********************/
# second API send ad_tips
/***********************/
public function ad_tips(){
   $j=0;
foreach($this->getAddTips() As $tips){
    $ad_tip[$j]['id']='';
    $ad_tip[$j]['clientId']=$this->_getClient_id();
    $ad_tip[$j]['tip']=$tips->getKeywordTitle();
    $ad_tip[$j]['lang']=Mage::app()->getLocale()->getLocaleCode();
    $j++;
}
    $ad_tips= array('ad_tip'=>json_encode($ad_tip));
    $this->_postCurl(self::create_ecommerce_ad_tip ,$ad_tips);

 
}
/***********************/
# Third API send keywords
/***********************/
public function keywords(){
  $i=0;
foreach($this->getCategoryKeywords() as $kewwords){
    $EcommerceKeyword[$i]['clientId']=$this->_getClient_id();
    $EcommerceKeyword[$i]['id']='';
    $EcommerceKeyword[$i]['keyword']=$kewwords->getKeywordTitle();
    $EcommerceKeyword[$i]['monthly_searches']='';
    $EcommerceKeyword[$i]['cpc']='';
    $EcommerceKeyword[$i]['categories']='';
    $EcommerceKeyword[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
    $i++;
}
    $params= array('keyword'=>json_encode($EcommerceKeyword));
    $this->_postCurl(self::create_ecommerce_keyword ,$params);
       
}
/***********************/
# fourth API send order
/***********************/
public function orderApi(){
    $k=0;
    $order =$this->getOrders();
    foreach( $order as $orders){
    $Order[$k]['id']='';
    $Order[$k]['clientId']=$this->_getClient_id();
    $Order[$k]['productId']=$orders->getEntityId();
    $Order[$k]['order_date']=$orders->getCreatedAt();
    $Order[$k]['order_quantity']=$orders->getTotalQtyOrdered();
    $Order[$k]['order_price']=$orders->getTotalPaid();
    $k++;

    }
    $params= array('order'=>json_encode($Order));
    $this->_postCurl(self::create_ecommerce_order ,$params);

 
}
public function saveStaus(){
	$collection =Mage::getModel('munoz/keyword')->getCollection();
	$collection->addFieldToFilter('keyword_title','complete');
	if(count($collection)>0){
	    $res= Mage::getModel('munoz/keyword')->load('complete','keyword_title');
	    $res->setKeywordTitle('complete');
	    
	    $res->setCreatedAt(date("Y-m-d H:i:s", Mage::getModel('core/date')->timestamp(time())));
	}
	else{
	    $res =Mage::getModel('munoz/keyword');
	    $res->setKeywordTitle('complete');
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
public function setSteps(){
    $cleint_id=$this->_getClient_id();
    $url="http://manager.cleverppc.com/api/ecommerce/v1/last_step_status?client_token=".$cleint_id."&last_step=5&waiting=1";
    $data = file_get_contents($url);
    $json_a=json_decode($data,true);
}

public function stat_time_api(){
    $cleint_id=$this->_getClient_id();
    $url=self::check_finish_process_time."?client_token=".$cleint_id."&start=1";
    $data = file_get_contents($url);
    $json_a=json_decode($data,true);  
}
public function sendCountry($country_code){
	    $selected_country = explode(",", $country_code);
            $i=0;
            foreach($selected_country as $countries){
                $countryName = Mage::getModel('directory/country')->loadByCode($countries)->getName();
                $country[$i]['id']='';
                $country[$i]['clientId']=$this->_getClient_id();
                $country[$i]['ctry_iso_2']=$this->_IsoCountryCode($countryName,$countries);
                $country[$i]['ctry_name']= $countryName;
                $country[$i]['ctry_GgId']='';
                $i++;
            }
	    $params= array('country'=>json_encode($country));
	    $this->_postCurl(self::create_ecommerce_country ,$params);
 
}
public function _IsoCountryCode($countryName,$countries){
    $allLanguages = Mage::app()->getLocale()->getOptionLocales();
    foreach($allLanguages as $key=>$value){
	$ct=explode('(',$value['label']);
	if (in_array($countryName.")", $ct)) {
	    if($value['value']){
		return $value['value'];
	    }
	    else{
		return $countries;
	    }	
	}  
    }   
}
// curl function /////////
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
       
public function _getParentId($ids){
    if ($ids){
	$topParent = Mage::getModel('catalog/category')->setStoreId(Mage::app()->getStore()->getId())->load($ids);
    }
    else{
	$topParent = null;//it means you are in one catalog root.
    }
    return $topParent['parent_id'];
    }
// end curl ///////
public function end_time_api(){
    $cleint_id=$this->_getClient_id();
    $url=self::check_finish_process_time."?client_token=".$cleint_id."&stop=1";
    $data = file_get_contents($url);
    $json_a=json_decode($data,true);
    
}
}
$wholekeyword=$this->getRequest()->getParams();
$shell = new Rodrigo_Shell_MyApi($wholekeyword);
$shell->run();