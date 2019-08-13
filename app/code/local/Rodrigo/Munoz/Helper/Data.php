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
 * Helper
 */
class Rodrigo_Munoz_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    public function blacklist(){
       return $Black_list  = array("","Default Category","home page","homepage","Frontpage","Brands","All","New products","New release","New Arrivals","sale","sales","On sale","New Arrival","All products","Products","Man","Men","Woman","Accessories","VIP");
    }
    
}