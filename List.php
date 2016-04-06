<?php
class Magebuzz_Dailydeal_Block_List extends Mage_Catalog_Block_Product_List
{

	const STATUS_RUNNING = MST_Dailydeals_Model_Source_Status::STATUS_RUNNING;
	const STATUS_DISABLED = MST_Dailydeals_Model_Source_Status::STATUS_DISABLED;
	protected $_productCollection;
	protected $_sort_by;


        protected function _prepareLayout()
        {
            if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
				$breadcrumbsBlock->addCrumb('home', array(
					'label'=>Mage::helper('catalog')->__('Home'),
					'title'=>Mage::helper('catalog')->__('Go to Home Page'),
					'link'=>Mage::getBaseUrl()
				));

				$breadcrumbsBlock->addCrumb('deals', array(
					'label'=>Mage::helper('dailydeal')->__('Daily Deals'),
					'title'=>Mage::helper('dailydeal')->__('Daily Deals'),)
				);

				if ($headBlock = $this->getLayout()->getBlock('head')) {
					$headBlock->setTitle(Mage::helper('dailydeal')->__('Daily Deals'));
				}
			}

            parent::_prepareLayout();
        }

	protected function _getProductCollection($loadRecent)
	{
		$dealStatus = ($loadRecent) ? self::STATUS_DISABLED : self::STATUS_RUNNING;

		//load active deal ids
		$dealCollection = Mage::getModel('dailydeal/dailydeal')->getCollection()->addFieldToFilter('status', array('eq'=>$dealStatus))->setOrder('deal_id', 'DESC');
		$productIds = array(0);
		
		if (count($dealCollection)) {
			$dealIds = array();
 			foreach ($dealCollection as $deal) {
 				if (!array_key_exists($deal->getProductId(), $dealIds)) {
 					$dealIds[$deal->getProductId()] = $deal->getId();
 					$productIds[] = $deal->getProductId();
 				}
        	}

        	if (count($dealIds)) {
	        	$dealIdsString = implode(',', $dealIds);
	        } else {
		        $dealIdsString = 0;
	        }
        } else {
        	$dealIdsString = 0;
        }

		if ( isset( $_REQUEST['limit'] ) and $_REQUEST['limit'] != '' ) {
			$limit = $_REQUEST['limit'];
		} else {
			$limit = Mage::getBlockSingleton('catalog/product_list_toolbar')->getLimit();
		}

		if ( isset( $_REQUEST['p'] ) and $_REQUEST['p'] > 0 ) {
			$cur_page = $_REQUEST['p'];
		} else {
			$cur_page = 1;
		}
        //load product collection + deals info
        $resource = Mage::getSingleton('core/resource');
        $collection = Mage::getResourceModel('catalog/product_collection')
     	    ->addAttributeToSelect('*')
     	    ->addAttributeToFilter('entity_id', array('in', $productIds))
            ->addStoreFilter()
            ->joinTable($resource->getTableName('dailydeal_deal'),'product_id=entity_id', array('deal_id' => 'deal_id', 'deal_qty' => 'deal_qty', 'end_time' => 'end_time'), '{{table}}.deal_id IN ('.$dealIdsString.')','left')
			->setPageSize($limit)
			->setCurPage($cur_page)
			->setOrder(Mage::getBlockSingleton('catalog/product_list_toolbar')->getCurrentOrder(), Mage::getBlockSingleton('catalog/product_list_toolbar')->getCurrentDirection())
			;
			

        //set collection order
    	if ($loadRecent) {
    		$collection->setOrder('end_time', 'DESC');
    	} else {
        	$collection->setOrder('deal_id', 'DESC');
        }

        return $collection;



}



}