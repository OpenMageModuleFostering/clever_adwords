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
 * controller to send data in creted instances
 */
class Rodrigo_Munoz_Adminhtml_ExcelsheetController extends Mage_Adminhtml_Controller_Action
{

   
    public  $prd_atr_code=Array();
   
    public function IndexAction(){
      $this->loadLayout(); 
      $this->renderLayout(); 
  }
   
   
   
    public function CreateExelShetAction(){
        require_once 'shell/createInstance.php';
        $wholekeyword=$this->getRequest()->getParams();
        $shell = new Rodrigo_Shell_MyApi($wholekeyword);
              Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The whole process takes usually 2 working days.Please be patient, we do great things.'));
            $this->_redirect('*/*/index');

    }

}