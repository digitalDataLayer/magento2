<?php


namespace Persomi\Digitaldatalayer\Helper;

use Magento\Framework\Module\ModuleListInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	const XML_PATH_ACTIVE = 'persomi/digital_data_layer/enabled';
    const XML_PATH_DEBUG = 'persomi/digital_data_layer/debug_enabled';
    const XML_PATH_USERGROUPEXP = 'persomi/digital_data_layer/user_group_enabled';
    const XML_PATH_ATTRIBUTES = 'persomi/digital_data_layer/attributes_enabled';
    const XML_PATH_STOCKEXPOSURE = 'persomi/digital_data_layer/stock_exposure';
    const XML_PATH_PRODUCTLISTEXPOSURE = 'persomi/digital_data_layer/prod_list_exposure';
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

	protected $_moduleList;


	const MODULE_NAME = 'Persomi_Digitaldatalayer';


    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface
     */
    public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		ModuleListInterface $moduleList
    ) {
        $this->_objectManager = $objectManager;
		$this->_moduleList = $moduleList;
        parent::__construct($context);
    }
    /**
     * Whether Digital Data Layer Manager is ready to use
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    /**
     * Get Debug Mode
     *
     * @return bool | null | string
     */
    public function getDebugMode() {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	/**
     * Get User Group Exp
     *
     * @return bool | null | string
     */
    public function getUserGropuExp() {
        return $this->scopeConfig->getValue(self::XML_PATH_USERGROUPEXP, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	/**
     * Get Attributes
     *
     * @return bool | null | string
     */
    public function getAttribute() {
        return $this->scopeConfig->getValue(self::XML_PATH_ATTRIBUTES, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	/**
     * Get Stock Exposure
     *
     * @return bool | null | string
     */
    public function getStockExposure() {
        return $this->scopeConfig->getValue(self::XML_PATH_STOCKEXPOSURE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	/**
     * Get Product List Exposure
     *
     * @return bool | null | string
     */
    public function getProductListExp() {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCTLISTEXPOSURE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	public function getVersion(){
        return $this->_moduleList->getOne(self::MODULE_NAME)['setup_version'];
    }
}