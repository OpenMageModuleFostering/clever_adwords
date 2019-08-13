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
 * model for collection
 */


class Rodrigo_Munoz_Model_Mysql4_Keyword extends Mage_Core_Model_Mysql4_Abstract		{

	    public function _construct()	{    
	        $this->_init('munoz/keyword', 'id');
	    }
	}
?>