<?php

namespace Persomi\Digitaldatalayer\Model\System\Config\Source;

use Magento\Framework\App\ObjectManager;

class Stockexposure implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
		$options[] = ['value' => 0,'label' => 'Don\'t expose stock'];
		$options[] = ['value' => 1,'label' => 'Only Expose In or Out of stock (availability)'];
		$options[] = ['value' => 2,'label' => 'Expose actual stock level'];
		
		return $options;
    }
}