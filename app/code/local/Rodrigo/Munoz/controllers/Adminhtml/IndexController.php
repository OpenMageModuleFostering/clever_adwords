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
    public function indexAction()
    {   
        $this->loadLayout();
        $this->renderLayout();
    }

    public function getFormAction(){
         $this->loadLayout();
        $this->renderLayout();
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
        $postData = $data['session_id'];
	 $url="http://manager.cleverppc.com/api/magento/v1/check_adwords_user_status?client_token=".$postData;
            $data = file_get_contents($url);
               $json_a=json_decode($data,true);


                echo  $messaage= $json_a['message'];
	       if($messaage=='success'){
			$session_id =$this->getSessionId();
			$this->SetResponse($messaage,$session_id);
	       }
	      return $messaage= $json_a['message'];
           
	}
	
	public function setUrlresponseAction(){
		$wholekeyword=$this->getRequest()->getParams();
	}
	
	
	
	
	public function saveKeywordAction(){
                $wholekeyword=$this->getRequest()->getParams();
		$data =Mage::getModel('munoz/keyword')->saveKeyword($wholekeyword);
                if($data){
                    echo json_encode(array('success'=>true,'response'=>$data));
		}
		else{
                   echo json_encode(array('success'=>false,'response'=>'error'));
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
		return $data =Mage::getModel('munoz/keyword')->saveResponse($messaage,$session_id);
		
	}
	public function getSessionId(){
		$domain=  Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
		return md5($domain); 
	}
	
	public function setParameterAction(){
                $client_info= $this->getDetail();
			$params=['client_info'=>json_encode($client_info)];
			$ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "http://manager.cleverppc.com/api/magento/v1/save_magento_client_user");
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                        curl_setopt($ch, CURLOPT_POST, true);
                         

                        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                        // $output contains the output string
                        $output = curl_exec($ch);

                        // close curl resource to free up system resources
				curl_close($ch);      
				//print_r($output);
				$json_a =json_decode($output, true);
				
				echo  $messaage= $json_a['message'];

        }
	public function getDetail(){
		$userArray = Mage::getSingleton('admin/session')->getData();
		$user = Mage::getSingleton('admin/session'); 
		$userEmail = $user->getUser()->getEmail();
		$userName = $user->getUser()->getFirstname()." ".$user->getUser()->getLastname();
		$countryCode = Mage::getStoreConfig('general/country/default');
		$domain=  Mage::getBaseUrl (Mage_Core_Model_Store::URL_TYPE_WEB);
        
		return $clint_info=array('email'=>$userEmail,'name'=>$userName,'domain'=>$domain,'currency'=>$countryCode,'client_token'=>md5($domain));
        
        }
	


}

?>