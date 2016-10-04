<?php

namespace Persomi\Digitaldatalayer\Model\System\Config\Source;

use Magento\Framework\App\ObjectManager;

class Productattributes implements \Magento\Framework\Option\ArrayInterface
{
	
    /**
     * @return array
     */
    public function toOptionArray()
    {
		$options = array(array('label' => 'none', 'value' => '0'));
		
		/** @var \Magento\Catalog\Model\Product\Attribute\Repository $obj */
		$attributeRepository = ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product\Attribute\Repository');
		
		/** @var \Magento\Framework\Api\SearchCriteriaBuilder $obj */
		$_searchCriteriaBuilder = ObjectManager::getInstance()->create('\Magento\Framework\Api\SearchCriteriaBuilder');
		
		$searchCriteria = $_searchCriteriaBuilder->create();
		foreach ($attributeRepository->getList($searchCriteria)->getItems() as $attribute) {
			if ($attribute->getIsUserDefined()){
				array_push($options, array('label' => $attribute->getAttributeCode(), 'value' => $attribute->getAttributeCode()));
			}
			
		}
		
		array_push($options, array('label' => 'weight', 'value' => 'weight'));
		return $options;
    }
}