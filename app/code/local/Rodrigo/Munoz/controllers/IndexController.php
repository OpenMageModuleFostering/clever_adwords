<?php 
class Rodrigo_Munoz_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
	header('Content-Type: application/json');
	echo json_encode(array('success'=>true));
    }
    
    public function getNewproductListAction()
    {
	 $productarry = array();
	 $todayStartOfDayDate  = Mage::app()->getLocale()->date()
	->setTime('00:00:00')
	->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);

	$todayEndOfDayDate  = Mage::app()->getLocale()->date()
	->setTime('23:59:59')
	->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
	/** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
	$collection = Mage::getResourceModel('catalog/product_collection')
	->setVisibility(Mage::getSingleton('catalog/product_visibility')
	->getVisibleInCatalogIds());    
	$collection = $collection->addMinimalPrice()
	->addFinalPrice()
	->addTaxPercents()
	->addAttributeToSelect(Mage::getSingleton('catalog/config')
	->getProductAttributes())
	->addUrlRewrite()
	->addStoreFilter()
	->addAttributeToFilter('news_from_date', array('or'=> array(
	0 => array('date' => true, 'to' => $todayEndOfDayDate),
	1 => array('is' => new Zend_Db_Expr('null')))
	), 'left')
	->addAttributeToFilter('news_to_date', array('or'=> array(
	0 => array('date' => true, 'from' => $todayStartOfDayDate),
	1 => array('is' => new Zend_Db_Expr('null')))
	), 'left')
	->addAttributeToFilter(
	array(
	array('attribute' => 'news_from_date', 'is'=>new Zend_Db_Expr('not null')),
	array('attribute' => 'news_to_date', 'is'=>new Zend_Db_Expr('not null'))
	)
	)
	->addAttributeToSort('news_from_date', 'desc');
	$i=0;
	foreach ($collection as $product) //loop for getting products
	{					
				$dim_prod[$i]['id']='';
                                $dim_prod[$i]['clientId']=md5(Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB));
                                $dim_prod[$i]['prodId']=$product->getId();
                                $dim_prod[$i]['prod_name']=$product->getName();
                                $dim_prod[$i]['prod_url']=$product->getProductUrl();
                                $dim_prod[$i]['prod_price']=$product->getPrice();
                                $dim_prod[$i]['barcode']='';
                                $dim_prod[$i]['sku']=$product->getSku();
                                $dim_prod[$i]['meta']=$product->getMetaKeywords();
                                $dim_prod[$i]['published']=$product->getStatus();
                                $dim_prod[$i]['inv']='';
                                $dim_prod[$i]['inv_num']=$this->getPorductStock($product->getId());
                                $dim_prod[$i]['updated']=$product->getUpdatedAt();
                                $dim_prod[$i]['main_image']=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
                                $dim_prod[$i]['lang']=Mage::app()->getLocale()->getLocaleCode();
                            $i++;
		

            //$productarry[]=$product->getId();
	}
	header('Content-Type: application/json');
        echo json_encode($dim_prod);	  
    }
    public function getPorductStock($product_id){
        $model = Mage::getModel('catalog/product'); 
        $_product = $model->load($product_id); 
        return $stocklevel = (int)Mage::getModel('cataloginventory/stock_item') ->loadByProduct($_product)->getQty();
    }
}
?>