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
 * keyword for collection
 */
class Rodrigo_Munoz_Model_Mysql4_Keyword_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract	{
		
    	public function _construct()	{
		    parent::_construct();
		    $this->_init('munoz/keyword');
    	}
	}
?>
