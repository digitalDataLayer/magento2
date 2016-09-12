<?php

namespace Persomi\Digitaldatalayer\Model;

use Magento\Framework\DataObject;
use Persomi\Digitaldatalayer\Helper\Data as DdlHelper;

class DataLayer extends DataObject {
	/**
     * @var Quote|null
     */
    protected $_quote = null;
    
    /**
     * Datalayer Variables
     * @var array
     */
    protected $_variables = [];
    /**
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_context;
    
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_checkoutSession;
    
    /**
     * @var string
     */
    protected $_fullActionName;
	
	protected $_debug = false;
	protected $_version = "1.0";
    protected $_user = null;
    protected $_page = null;
    protected $_cart = null;
    protected $_product = null;
    protected $_search = null;
    protected $_transaction = null;
    protected $_listing = null;
    protected $_events = array();
	protected $_ddlHelper = null;
	protected $_salesOrder;
	
	/**
     * @param MessageInterface $message
     * @param null $parameters
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
		DdlHelper $ddlHelper,
		\Magento\Sales\Model\Order $salesOrder,
        \Magento\Framework\Registry $registry
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_context = $context;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
		$this->_ddlHelper = $ddlHelper;
		$this->_salesOrder = $salesOrder;
        
        $this->fullActionName = $this->_context->getRequest()->getFullActionName();
    }
	
	protected function _getRequest(){
		return $this->_context->getRequest();
	}
	
	protected function _getControllerName(){
		return $this->_getRequest()->getControllerName();
    }
	
	protected function _getActionName(){
		return $this->_getRequest()->getActionName();
	}
	
	protected function _getModuleName(){
		return $this->_getRequest()->getModuleName();
	}
	
	protected function _getRouteName(){
		return $this->_getRequest()->getRouteName();
	}
	
	protected function _getCustomer()
    {
        return $this->_customerSession->getCustomer();
    }
	
	public function getUser(){
        return $this->_user;
    }
	
	public function getPage(){
		return $this->_page;
    }
	
	protected function _getBreadcrumb(){
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Helper\Data');
		return $helper->getBreadcrumbPath();
    }
	
	protected function getHttpReferer(){
		//$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Core\Helper\Http');
		return $this->_getRequest()->getServer('HTTP_REFERER');
    }
	
	public function _getPageBreadcrumb(){
		$arr = $this->_getBreadcrumb();
		$breadcrumb = array();
		try {
			foreach ($arr as $category) {
				$breadcrumb[] = $category['label'];
			}
		} catch (Exception $e) {
			
		}
		return $breadcrumb;
	}
	
	public function getCurrentUrl() {
		/** @var \Magento\Framework\UrlInterface $urlInterface */
		$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
		return $urlInterface->getCurrentUrl();
	}

	public function getCurrentLocale() {
		/** @var \Magento\Framework\ObjectManagerInterface $obj */
		$obj = \Magento\Framework\App\ObjectManager::getInstance();
		/** @var \Magento\Framework\Locale\Resolver $resolver */
		$resolver = $obj->get('Magento\Framework\Locale\Resolver');
		return $resolver->getLocale();
	}
	
	public function _isHome(){
		if ($this->_getRequest()->getRequestString() == "/") {
			return true;
		} else {
			return false;
		}
	}

    public function _isContent(){
		if ($this->_getModuleName() == 'cms') {
			return true;
        } else {
			return false;
		}
	}
	
	public function _isCategory(){
		if ($this->_getControllerName() == 'category') {
			return true;
		} else {
			return false;
		}
	}
	
	public function _isSearch(){
		if ($this->_getModuleName() == 'catalogsearch') {
			return true;
		} else {
			return false;
		}
	}
	
	public function _isProduct(){
		$onCatalog = false;
		if ($this->_coreRegistry->registry('current_product')) {
			$onCatalog = true;
		}
		return $onCatalog;
	}
	
	public function _isCart(){
		try {
			$request = $this->_getRequest();
			$module = $request->getModuleName();
			$controller = $request->getControllerName();
			$action = $request->getActionName();
			if ($module == 'checkout' && $controller == 'cart' && $action == 'index') {
				return true;
			}
		} catch (Exception $e) {
			
		}
		return false;
	}
	
	public function _isCheckout(){
		if (strpos($this->_getModuleName(), 'checkout') !== false && $this->_getActionName() != 'success') {
			return true;
		} else {
			return false;
		}
	}
	
	public function _isConfirmation(){
        // default controllerName is "onepage"
        // relax the check, only check if it contains checkout
        // some checkout systems have different prefix/postfix,
        // but all contain checkout
        if (strpos($this->_getModuleName(), 'checkout') !== false && $this->_getActionName() == "success") {
            return true;
        } else {
            return false;
        }
    }
	
	protected function _getCurrentProduct(){
        return $this->_coreRegistry->registry('current_product');
    }
	
	public function setDigitalDataLayer(){
		$this->_debug = $this->_ddlHelper->getDebugMode();
		$this->_userGroupExp = $this->_ddlHelper->getUserGropuExp();
		$this->_expAttr = explode(',', $this->_ddlHelper->getAttribute());
		$this->_stockExp = (int)$this->_ddlHelper->getStockExposure();
		$this->_listLimit = $this->_ddlHelper->getProductListExp();
		
		$this->_setUser();
		$this->_setPage();
	}
	
	public function _getPageType()
    {
        try {
            if ($this->_isHome()) {
                return 'home';
            } elseif ($this->_isContent()) {
                return 'content';
            } elseif ($this->_isCategory()) {
                return 'category';
            } elseif ($this->_isSearch()) {
                return 'search';
            } elseif ($this->_isProduct()) {
                return 'product';
            } elseif ($this->_isCart()) {
                return 'basket';
            } elseif ($this->_isCheckout()) {
                return 'checkout';
            } elseif ($this->_isConfirmation()) {
                return 'confirmation';
            } else {
                return $this->_getModuleName();
            }
        } catch (Exception $e) {
        }
    }
	
	public function _setUser()
    {
		try {
			$this->_user = array();
			$user = $this->_getCustomer();
			$user_id = $user->getEntityId();
			$firstName = $user->getFirstname();
			$lastName = $user->getLastname();
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$userGroupObj = $objectManager->create('Magento\Customer\Model\Group');
			$userGroup = $userGroupObj->load($this->_customerSession->getCustomerGroupId());
			
			if ($this->_isConfirmation()) {
				$orderId = $this->_checkoutSession->getLastOrderId();
				if ($orderId) {
					$order = $this->_salesOrder->load($orderId);
					$email = $order->getCustomerEmail();
				}
			} else {
				$email = $user->getEmail();
			}
			
			$this->_user['profile'] = array();
			
			$profile = array();
			$profile['profileInfo'] = array();
			if ($user_id) {
				$profile['profileInfo']['profileID'] = (string)$user_id;
			}
			if ($firstName) {
				$profile['profileInfo']['userFirstName'] = $firstName;
			}
			if ($lastName) {
				$profile['profileInfo']['userLastName'] = $lastName;
			}
			
			if ($email) {
				$profile['profileInfo']['email'] = $email;
			}
			
			$profile['profileInfo']['language'] = $this->getCurrentLocale();
			$profile['profileInfo']['returningStatus'] = $user_id ? 'true' : 'false';
            if ($userGroup && $this->_userGroupExp) {
                $profile['profileInfo']['segment']['userGroupId'] = $userGroup->getData('customer_group_id');
                $profile['profileInfo']['segment']['userGroup'] = $userGroup->getData('customer_group_code');
            }
			array_push($this->_user['profile'], $profile);
		
		} catch (Exception $e) { }
	}
	
	public function _setPage()
    {
		try {
			$this->_page = array();
			
			$this->_page['pageInfo'] = array();
			$this->_page['pageInfo']['pageName'] = '';
			$this->_page['pageInfo']['destinationURL'] = $this->getCurrentUrl();
			
			$referringURL = $this->getHttpReferer();
			if ($referringURL) {
				$this->_page['pageInfo']['referringURL'] = $referringURL;
            }
			
			if ($this->_getPageBreadcrumb()) {
				$this->_page['pageInfo']['breadcrumbs'] = $this->_getPageBreadcrumb();
			}
			
			$this->_page['pageInfo']['language'] = $this->getCurrentLocale();
			
			$this->_page['category'] = array();
			if ($_category = $this->_coreRegistry->registry('current_category')) {
				// There must be a better way than this
				$this->_page['category']['primaryCategory'] = $_category->getName();
			}
			
			$this->_page['category']['pageType'] = $this->_getPageType();
			
			/* if ($this->_debug) {
                $modules = (array)Mage::getConfig()->getNode('modules')->children();
                $mods = array();
                foreach ($modules as $key => $value) {
                    if (strpos($key, 'Mage_') === false) {
                        $mods[] = $key;

                    }
                }
                $this->_page['extra_modules'] = $mods;
            } */

        } catch (Exception $e) {
        }
    }
	
	public function _setProduct(){
		try {
			$product = $this->_getCurrentProduct();
			if (!$product) return false;
			$this->_product = array();
			array_push($this->_product, $this->_getProductModel($product, 'product'));
		} catch (Exception $e) {
			
		}
	}
	
	
}