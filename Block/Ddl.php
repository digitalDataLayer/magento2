<?php

namespace Persomi\Digitaldatalayer\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Cookie\Helper\Cookie as CookieHelper;
use Persomi\Digitaldatalayer\Helper\Data as DdlHelper;


class Ddl extends Template {
	
    public $_logger;
	/**
     * Helper
     *
     * @var \Persomi\Digitaldatalayer\Helper\Data
     */
    protected $_ddlHelper = null;
	
	/**
     * Cookie Helper
     *
     * @var \Magento\Cookie\Helper\Cookie
     */
    protected $_cookieHelper = null;
	
	/**
     * Cookie Helper
     *
     * @var \Persomi\Digitaldatalayer\Model\DataLayer
     */
    protected $_dataLayerModel = null;
    protected $_productMetadata = null;
	
	
	/**
     * @param Context $context
     * @param CookieHelper $cookieHelper
     * @param DdlHelper $ddlHelper
     * @param \Persomi\Digitaldatalayer\Model\DataLayer $dataLayer
     * @param array $data
     */
    public function __construct(
        Context $context, 
        DdlHelper $ddlHelper, 
        CookieHelper $cookieHelper, 
        \Persomi\Digitaldatalayer\Model\DataLayer $dataLayer,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollection,
        \Psr\Log\LoggerInterface $logger, //log injection
        array $data = []
    ) {
        $this->_cookieHelper = $cookieHelper;
        $this->_ddlHelper = $ddlHelper;
        $this->_dataLayerModel = $dataLayer;
        $this->_productMetadata = $productMetadata;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->_logger = $logger;
        parent::__construct($context, $data);
		
		$this->_dataLayerModel->setDigitalDataLayer();
    }
	
	protected function _toHtml() {
        if (!$this->_ddlHelper->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
	
	public function getMagentoVersion(){
		return $this->_productMetadata->getVersion();
	}
	
	public function getUser(){
		return $this->_dataLayerModel->getUser();
	}
	
	public function getProduct(){
		return $this->_dataLayerModel->getProduct();
	}
	
	public function getCart(){
		return $this->_dataLayerModel->getCart();
	}
	
	public function getListing(){
		return $this->_dataLayerModel->getListing();
	}
	
	public function getTransaction(){
		return $this->_dataLayerModel->getTransaction();
	}
	
	public function getVersion(){
		return $this->_ddlHelper->getVersion();
	}
	
	public function getEvents(){
		return $this->_dataLayerModel->getEvents();
	}
	
	public function getPage(){
		$_page = $this->_dataLayerModel->getPage();
		if(isset($_page['pageInfo']['pageName']) && $_page['pageInfo']['pageName'] == ""){
			$_page['pageInfo']['pageName'] = $this->getLayout()->getBlock('page.main.title')->getPageTitle();
		}
		return $_page;
	}

}