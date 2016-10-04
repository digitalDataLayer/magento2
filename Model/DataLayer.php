<?php

namespace Persomi\Digitaldatalayer\Model;

use Magento\Framework\DataObject;
use Persomi\Digitaldatalayer\Helper\Data as DdlHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;

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
	protected $_storeManager;
	protected $_productloader;
	protected $_categoryloader;
	protected $_stockRegistry;
	protected $_calculationTool;
	protected $_ConfigurableProductType;
	protected $_GroupedProductType;
	protected $catalogHelper;
	
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
		ConfigurableProductType $ConfigurableProductType,
		GroupedProductType $GroupedProductType,
		\Magento\Sales\Model\Order $salesOrder,
		\Magento\Catalog\Model\ProductFactory $productFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
		\Magento\Tax\Model\Calculation $calculation,
		\Magento\Catalog\Helper\Data $catalogHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_context = $context;
        $this->_coreRegistry = $registry;
        $this->_checkoutSession = $checkoutSession;
		$this->_ddlHelper = $ddlHelper;
		$this->_salesOrder = $salesOrder;
		$this->_storeManager = $storeManager;
		$this->_productloader = $productFactory;
		$this->_categoryloader = $categoryFactory;
		$this->_stockRegistry = $stockRegistry;
		$this->_calculationTool = $calculation;
		$this->_ConfigurableProductType = $ConfigurableProductType;
		$this->_GroupedProductType = $GroupedProductType;
		$this->catalogHelper = $catalogHelper;
        
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
	
	protected function _getProduct($productId){
        return $this->_productloader->create()->load($productId);
    }
	
	public function getUser(){
        return $this->_user;
    }
	
	public function getPage(){
		return $this->_page;
    }
	
	public function getProduct(){
        return $this->_product;
    }
	
	public function getCart(){
        return $this->_cart;
    }
	
	public function getTransaction(){
        return $this->_transaction;
    }
	
	public function getListing(){
        return $this->_listing;
    }
	
	public function getEvents(){
        return array();
    }
	
	protected function _getBreadcrumb(){
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Helper\Data');
		return $helper->getBreadcrumbPath();
    }
	
	protected function getCatalogHelper(){
		$helper = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Helper\Data');
		return $helper;
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
	
	public function _getCurrency(){
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
	
	public function _getProductStock($productId){
        return (int)$this->_stockRegistry->getStockItem($productId)->getQty();
    }
	
	public function getAttributeCollection(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		/** @var \Magento\Catalog\Model\Product\Attribute\Repository $obj */
		$attributeRepository = $objectManager->create('\Magento\Catalog\Model\Product\Attribute\Repository');
		
		/** @var \Magento\Framework\Api\SearchCriteriaBuilder $obj */
		$_searchCriteriaBuilder = $objectManager->create('\Magento\Framework\Api\SearchCriteriaBuilder');
		
		$searchCriteria = $_searchCriteriaBuilder->create();
		return $attributeRepository->getList($searchCriteria)->getItems();
	}
	
	public function _extractShippingMethod($order){
        try {
            $shipping_method = $order->getShippingMethod();
        } catch (Exception $e) {
        }
        return $shipping_method ? $shipping_method : '';
    }
	
	/*
     * Get information on pages to pass to front end
     */
    public function getCurrentPrice($_price, $p1=true, $p2=false)
    {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$priceHelper = $objectManager->create('Magento\Framework\Pricing\Helper\Data');
		if ($p1) {			
            return number_format($priceHelper->currency((float)$_price, $p1, $p2), 2, '.', '');
        } else {
            return floatval(number_format($priceHelper->currency((float)$_price, $p1, $p2), 2, '.', ''));
        }
    }
	
	protected function _getCategory($category_id){
        return $this->_categoryloader->create()->load($category_id);
    }
	
	/*
     * Get information on pages to pass to front end
     */
    public function getProductPrice($_product, $_price, $IncTax){
		//var_dump($_price);
		return round($this->getCurrentPrice($this->catalogHelper->getTaxPrice($_product, $_price, $IncTax), false, false),2);
    }
	
	public function _getProductCategories($product){
        try {
            $cats = $product->getCategoryIds();
            if ($cats) {
                $category_names = array();
                foreach ($cats as $category_id) {
                    $_cat = $this->_getCategory($category_id);
                    $category_names[] = $_cat->getName();
                }
                return $category_names;
            }
        } catch (Exception $e) {
        }

        return false;
    }
	
	public function setDigitalDataLayer(){
		$this->_debug = $this->_ddlHelper->getDebugMode();
		$this->_userGroupExp = $this->_ddlHelper->getUserGropuExp();
		$this->_expAttr = explode(',', $this->_ddlHelper->getAttribute());
		$this->_stockExp = (int)$this->_ddlHelper->getStockExposure();
		$this->_listLimit = $this->_ddlHelper->getProductListExp();
		
		$this->_setUser();
		$this->_setPage();
		
		if ($this->_isProduct()) {
			$this->_setProduct();
		}
		
		if ($this->_isCategory() || $this->_isSearch()) {
			$this->_setListing();
		}
				
		if (!$this->_isConfirmation()) {
			$this->_setCart();
		}
		
		if ($this->_isConfirmation()) {
			$this->_setTransaction();
		}
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
	
	public function _setCart(){
		try {
			if (!isset($this->_checkoutSession)) {
				return;
            }
			$cart = array();
			$quote = $this->_checkoutSession->getQuote();
            
			// Set normal params
			$cart_id = $this->_checkoutSession->getQuoteId();
			if ($cart_id) {
				$cart['cartID'] = (string)$cart_id;
			}
			
			$cart['price'] = array();
			if ($quote->getSubtotal()) {
				$cart['price']['basePrice'] = $quote->getSubtotal();
            }
			
			if ($quote->getBaseSubtotal()) {
				$cart['price']['basePrice'] = $this->getCurrentPrice(floatval($quote->getBaseSubtotal()), false, false);
			} else {
				$cart['price']['basePrice'] = 0.0;
			}
			
			if ($quote->getShippingAddress()->getCouponCode()) {
				$cart['price']['voucherCode'] = $quote->getShippingAddress()->getCouponCode();
            }
			
			if ($quote->getShippingAddress()->getDiscountAmount()) {
				$cart['price']['voucherDiscount'] = abs((float)$quote->getShippingAddress()->getDiscountAmount());
            }
			
			$cart['price']['currency'] = $this->_getCurrency();
			if ($cart['price']['basePrice'] > 0.0) {
				$taxRate = (float)$quote->getShippingAddress()->getTaxAmount() / $quote->getSubtotal();
                $cart['price']['taxRate'] = round($taxRate, 3); // TODO: Find a better way
            }
            if ($quote->getShippingAmount()) {
                $cart['price']['shipping'] = (float)$quote->getShippingAmount();
            }
            if ($this->_extractShippingMethod($quote)) {
                $cart['price']['shippingMethod'] = $this->_extractShippingMethod($quote);
            }
			if ($quote->getShippingAddress()->getTaxAmount() && $quote->getSubtotal()) {
                $cart['price']['priceWithTax'] = (float)$quote->getShippingAddress()->getTaxAmount() + $this->getCurrentPrice($quote->getSubtotal(), false, false);
            } else {
                $cart['price']['priceWithTax'] = $cart['price']['basePrice'];
            }
            if ($quote->getGetData()['grand_total']) {
                $cart['price']['cartTotal'] = (float)$quote->getGetData()['grand_total'];
            } else {
                $cart['price']['cartTotal'] = $cart['price']['priceWithTax'];
            }
            // $cart['attributes'] = array();
            if ($cart['price']['basePrice'] === 0.0 && $cart['price']['cartTotal'] === 0.0 && $cart['price']['priceWithTax'] === 0.0) {
                unset($cart['price']);
            }

            // Line items
            $items = $quote->getAllVisibleItems();
            if (!$items && isset($cart['price'])) {
                if ($this->_debug) {
                    $cart['price']['testLog'] = "Second method used to retrieve cart items.";
                }

                // In case items were not retrieved for some reason
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $cartHelper = $objectManager->create('Magento\Checkout\Model\Cart');
                $items = $cartHelper->getCart()->getItems();
            }
            $cart['items'] = $this->_getLineItems($items, 'cart');
            if (empty($cart['items'])) {
                unset($cart['items']);
            }
			
			if ($cart_id || isset($cart['items']) || isset($cart['price'])) {
                $this->_cart = $cart;
            }
        } catch (Exception $e) {
        }
	}
	
	public function _getLineItems($items, $page_type){
		$line_items = array();
        try {
			foreach ($items as $item) {
				$productId = $item->getProductId();
				$product = $this->_getProduct($productId);
				$simplePId = null;
				if ($option = $item->getOptionByCode('simple_product')) {
					$simplePId = $option->getProduct()->getId();
				}
				
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$parentIds = $objectManager->create('Magento\GroupedProduct\Model\Product\Type\Grouped')->getParentIdsByChild($productId);
				if (!empty($parentIds)) {
                    $litem_model = $this->_getProductModel($product, 'linked');
					$litem_model['linkedProduct'] = array();
					array_push($litem_model['linkedProduct'], $this->_getProductModel($this->_getProduct($parentIds[0]), 'linked'));
				} else {
					$litem_model = $this->_getProductModel($item, 'cart', $simplePId);
				}
				if ($litem_model['productInfo'] && $item->getSku()) {
					// use SKU from order or quote item since it's the actual sku selected/sold and not the parent one
					$litem_model['productInfo']['sku'] = $item->getSku();
				}
				if ($page_type === 'cart') {
					$litem_model['quantity'] = floatval($item->getQty());
				} else {
					$litem_model['quantity'] = floatval($item->getQtyOrdered());
				}
				
				if (!is_array($litem_model['price'])) {
                    $litem_model['price'] = array();
                }
                if ($item->getCouponCode()) {
                    $litem_model['price']['voucherCode'] = $item->getCouponCode();
                }
                if ($item->getDiscountAmount()) {
                    $litem_model['price']['voucherDiscount'] = abs(floatval($item->getDiscountAmount()));
                }
                $litem_model['price']['cartTotal'] = floatval($item->getRowTotalInclTax());
                if ($this->_debug) {
                    $litem_model['price']['all']['_getCalculationPrice'] = $this->getCurrentPrice($product->getCalculationPrice(), false, false);
                    $litem_model['price']['all']['_getCalculationPriceOriginal'] = $this->getCurrentPrice($product->getCalculationPriceOriginal(), false, false);
                    $litem_model['price']['all']['_getBaseCalculationPrice'] = $this->getCurrentPrice($product->getBaseCalculationPrice(), false, false);
                    $litem_model['price']['all']['_getBaseCalculationPriceOriginal'] = $this->getCurrentPrice($product->getBaseCalculationPriceOriginal(), false, false);
                    $litem_model['price']['all']['_getOriginalPrice'] = $this->getCurrentPrice($product->getOriginalPrice(), false, false);
                    $litem_model['price']['all']['_getBaseOriginalPrice'] = $this->getCurrentPrice($product->getBaseOriginalPrice(), false, false);
                    $litem_model['price']['all']['_getConvertedPrice'] = $this->getCurrentPrice($product->getConvertedPrice(), false, false);
                }
                array_push($line_items, $litem_model);
            }
        } catch (Exception $e) {
        }

        return $line_items;
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
	
	public function _getProductModel($product, $_page_, $simpleProductId = null){
		
		$product_model = array();
		$options = array();
		$product_model['attributes'] = array();
		
		//If there is optional data then add it
		if ($_page_ === 'cart') {
			$opt = $product->getProduct()->getTypeInstance(true)->getOrderOptions($product->getProduct());
			if (isset($opt['attributes_info'])) {
				foreach ($opt['attributes_info'] as $attribute) {
					$options[lcfirst($attribute['label'])] = $attribute['value'];
                }
			}
			$productId = $product->getProductId();
			$product = $this->_getProduct($productId);
		}
		
		if ($_page_ === 'list') {
			$productId = $product->getId();
			$product = $this->_getProduct($productId);
        }
		
		//Configurable products don't have any regular price or special price, so load from simple product object
		if($simpleProductId != null){
			$_product = $this->_getProduct($simpleProductId);
		}else{
			$_product = $product;
		}
		
		try {
			// Product Info
			$product_model['productInfo'] = array();
			$product_model['productInfo']['productID'] = $product->getId();
			$product_model['productInfo']['sku'] = $product->getSku();
			$product_model['productInfo']['productName'] = $product->getName();
			$product_model['productInfo']['description'] = strip_tags($product->getDescription());
			$product_model['productInfo']['productURL'] = $product->getProductUrl();
			
			if ($this->_stockExp!=0) {
				$stock = $this->_getProductStock($product->getId());
                if($this->_stockExp==2){
					$product_model['productInfo']['stockLevel'] = $stock;
                } else if($this->_stockExp==1){
					$product_model['productInfo']['availability'] = ($stock>0 ? 'in stock' : 'out of stock');
				}
			}
			
			$store = $this->_storeManager->getStore();
			//Check if images contain placeholders
            if ($product->getImage() && $product->getImage() !== "no_selection") {				
				$product_model['productInfo']['productImage'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .  $product->getImage();
            }
            if ($product->getThumbnail() && $product->getThumbnail() !== "no_selection") {
				$product_model['productInfo']['productThumbnail'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getThumbnail();
            }
			
            //Attributes
            if (isset($options['size']) && $options['size'] != "") {
				$product_model['productInfo']['size'] = $options['size'];
                unset($options['size']);
            }
            if (isset($options['color']) && $options['color'] != "") {
                $product_model['productInfo']['color'] = $options['color'];
                unset($options['color']);
            }
			
			if ($product->getWeight() && in_array('weight', $this->_expAttr)) {
                $product_model['attributes']['weight'] = floatval($product->getWeight());
            }
			
            try {
                $attributes = $this->getAttributeCollection();
                foreach ($attributes as $attr) {
					$attrCode = $attr->getAttributecode();
					if ($attrCode === 'color' || $attrCode === 'manufacturer' || $attrCode === 'size') {
						if ($product->getAttributeText($attrCode) && in_array($attrCode, $this->_expAttr)) {
							$product_model['productInfo'][$attrCode] = $product->getAttributeText($attrCode);
                        }
					} elseif ($attr->getData('is_user_defined') && in_array($attrCode, $this->_expAttr) && $product->getData($attrCode)) {
						if ($attr->getData('frontend_class') === 'validate-number') {
                            if ($attr->getFrontend()->getValue($product) !== 'No') {
                                $product_model['attributes'][$attrCode] = floatval($attr->getFrontend()->getValue($product));
                            }
                        } elseif ($attr->getData('frontend_class') === 'validate-digits') {
                            if ($attr->getFrontend()->getValue($product) !== 'No') {
                                $product_model['attributes'][$attrCode] = intval($attr->getFrontend()->getValue($product));
                            }
                        } else {
                            if ($product->getAttributeText($attrCode)){
                                $product_model['attributes'][$attrCode] = $product->getAttributeText($attrCode);
                            } elseif ($product->getData($attrCode)) {
                                $product_model['attributes'][$attrCode] = $product->getData($attrCode);
                            }
                        }
                    }
                }
                //Add the options captured earlier
                if (count($options)) {
                    //$product_model['attributes']['options'] = $options;
                    $product_model['attributes'] += $options;
                }
            } catch (Exception $e) {
            }
			
			
            // Category
            // Iterates through all categories, checking for duplicates
            $allcategories = $this->_getProductCategories($product);
            if ($allcategories) {
                $catiterator = 0;
                $setCategories = array();
                foreach ($allcategories as $cat) {
                    if ($catiterator === 0) {
                        $product_model['category']['primaryCategory'] = $cat;
                        $catiterator++;

                    } else {
                        if (!in_array($cat, $setCategories)) {
                            $product_model['category']["subCategory$catiterator"] = $cat;
                            $catiterator++;
                        }
                    }
                    array_push($setCategories, $cat);
                }
                if ($product->getTypeID()) {
                    $product_model['category']['productType'] = $product->getTypeID();
                }
            }
			// Price
            $product_model['price'] = array();
			if (!$this->getProductPrice($_product,$_product->getSpecialPrice(),false) || $this->getProductPrice($_product, $_product->getSpecialPrice(),false) !== $this->getProductPrice($_product, $_product->getFinalPrice(),false)) {
				$product_model['price']['basePrice'] = $this->getProductPrice($_product, $_product->getPrice(),false);
				$product_model['price']['priceWithTax'] = $this->getProductPrice($_product, $_product->getPrice(),true);
            } else {
                $product_model['price']['basePrice'] = $this->getProductPrice($_product, $_product->getSpecialPrice(),false);
				$product_model['price']['priceWithTax'] = $this->getProductPrice($_product, $_product->getSpecialPrice(),true);
                $product_model['price']['regularPrice'] = $this->getProductPrice($_product, $_product->getPrice(),false);
				$product_model['price']['regularPriceWithTax'] = $this->getProductPrice($_product, $_product->getPrice(),true);
            }
            $product_model['price']['currency'] = $this->_getCurrency();

            if (!$product_model['price']['priceWithTax']) {
                unset($product_model['price']['priceWithTax']);
            }
			
			// In case 'basePrice' did not exist
            if (!$product_model['price']['basePrice']) {
                $product_model['price']['basePrice'] = $this->getProductPrice($_product, $_product->getGroupPrice(), false);
            }
            if (!$product_model['price']['basePrice']) {
                $product_model['price']['basePrice'] = $this->getProductPrice($_product, $_product->getMinimalPrice(), false);
            }
            if (!$product_model['price']['basePrice']) {
                $product_model['price']['basePrice'] = $this->getProductPrice($_product, $_product->getSpecialPrice(), false);
            }
			
			if (!$product_model['price']['basePrice']) {
                // Extract price for bundle products
                $price_model = $product->getPriceModel();
                if (method_exists($price_model, 'getOptions')) {
                    $normal_price = 0.0;
                    $_options = $price_model->getOptions($product);
                    foreach ($_options as $_option) {
                        if (!method_exists($_option, 'getDefaultSelection')) {
                            break;
                        }
                        $_selection = $_option->getDefaultSelection();
                        if ($_selection === null) continue;
                        $normal_price += $this->getProductPrice($product,$_selection->getPrice(), false);
                    }
                    $product_model['price']['basePrice'] = $normal_price;
                }
            }

            if ($this->_debug) {
                $product_model['price']['all'] = array();
                $product_model['price']['all']['getPrice'] = $this->getProductPrice($_product, $_product->getPrice(), false);
                $product_model['price']['all']['getMinimalPrice'] = $this->getProductPrice($_product, $_product->getMinimalPrice(), false);
                $product_model['price']['all']['getPriceModel'] = $_product->getPriceModel();
                $product_model['price']['all']['getGroupPrice'] = $this->getProductPrice($_product, $_product->getGroupPrice(), false);
                $product_model['price']['all']['getTierPrice'] = $_product->getTierPrice();
                $product_model['price']['all']['getTierPriceCount'] = $_product->getTierPriceCount();
                $product_model['price']['all']['getFormatedTierPrice'] = $_product->getFormatedTierPrice();
                $product_model['price']['all']['getFormatedPrice'] = $_product->getFormatedPrice();
                $product_model['price']['all']['getFinalPrice'] = $this->getProductPrice($_product, $_product->getFinalPrice(), false);
                $product_model['price']['all']['getCalculatedFinalPrice'] = $_product->getCalculatedFinalPrice();
                $product_model['price']['all']['getSpecialPrice'] = $this->getProductPrice($_product,$_product->getSpecialPrice(), false);
            }
			
			// Calculate Tax Rate
            $store = $store = $this->_storeManager->getStore('default');
			$request = $this->_calculationTool->getRateRequest(null, null, null, $store);
			$taxClassId = $product->getTaxClassId();
			$percent = $this->_calculationTool->getRate($request->setProductClassId($taxClassId));
			$product_model['price']['taxRate'] = ((float)$percent) / 100;

			//$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$isConfigurable = ($product->getTypeId() === ConfigurableProductType::TYPE_CODE);
			$isGrouped = ($product->getTypeId() === GroupedProductType::TYPE_CODE);
            // For configurable/grouped/composite products, add all associated products to 'linkedProduct'
            if ($_page_ === 'product') {
				if ($isConfigurable || $isGrouped || $product->isComposite()) {
                    $product_model['linkedProduct'] = array();
					$simple_collection = array();
					// Add simple products related to configurable products
                    if ($isConfigurable) {
                        $conf = $this->_ConfigurableProductType->getUsedProductCollection($product);
						$simple_collection = $conf->addAttributeToSelect('*')->addFilterByRequiredOptions();
                    } else {
                        $type_instance = $product->getTypeInstance(true);
                        if (method_exists($type_instance, 'getSelectionsCollection')) {
                            // Add simple products related to bundle products
                            $simple_collection = $type_instance->getSelectionsCollection(
                                $type_instance->getOptionsIds($product), $product
                            );
                        } else if (method_exists($type_instance, 'getAssociatedProducts')) {
                            // Add simple products related to grouped products
                            $simple_collection = $type_instance->getAssociatedProducts($product);
                        }
                    }
					
					// Add related products to the data layer
                    $min_price = 0.0;
                    foreach ($simple_collection as $simple_product) {
						array_push($product_model['linkedProduct'], $this->_getProductModel($simple_product, false));
                        $simple_product_price = $this->getCurrentPrice(floatval($simple_product->getPrice()), false, false);
                        if ($simple_product_price && (!$min_price || $simple_product_price < $min_price)) {
                            $min_price = $simple_product_price;
                        }
                    }
					
					

                    // If price could not be extracted before, can set it now
                    if (!$product_model['price']['basePrice']) {
                        $product_model['price']['basePrice'] = $this->getCurrentPrice(floatval($min_price), false, false);
                    }
					
					if (!$product_model['linkedProduct']) {
                        unset($product_model['linkedProduct']);
                    }
                }
            }

            if ($this->_debug) {
                $product_model['more']['isConfigurable'] = $isConfigurable;
                $product_model['more']['isSuperGroup'] = $product->isSuperGroup();
                $product_model['more']['isSuperConfig'] = $product->isSuperConfig();
                $product_model['more']['isGrouped'] = $isGrouped;
                $product_model['more']['isSuper'] = $product->isSuper();
                $product_model['more']['isVirtual'] = $product->isVirtual();
                $product_model['more']['isRecurring'] = $product->isRecurring();
                $product_model['more']['isComposite'] = $product->isComposite();
                $product_model['more']['getTypeId'] = $product->getTypeId();
                $product_model['more']['getImage'] = $product->getImage();
                $product_model['more']['getImageURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .  $product->getImage();
                $product_model['more']['getSmallImage'] = $product->getSmallImage();
                $product_model['more']['getSmallImageURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .  $product->getSmallImage();
                $product_model['more']['getThumbnail'] = $product->getThumbnail();
                $product_model['more']['getThumbnailURL'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .  $product->getThumbnail();
            }
        } catch (Exception $e) {
        }
        return $product_model;
    }
	
	public function _setListing(){
		try {
			$this->_listing = array();
			if ($this->_isCategory()) {
				return '';
			} elseif ($this->_isSearch()) {
                if (isset($_GET['q'])) {
                    $this->_listing['query'] = $_GET['q'];
                }
            }

            // Note: data on products are retrieved later, after the content layout block,
            // since the product list is compiled then.
        } catch (Exception $e) {
        }
    }
	
	
	public function _setTransaction(){
		try {
			$orderId = $this->_checkoutSession->getLastOrderId();
			
			if ($orderId) {
				$transaction = array();
				$order = $this->_salesOrder->load($orderId);
				
				// Get general details
				$transaction['transactionID'] = $order->getIncrementId();
				$transaction['total'] = array();
				$transaction['total']['currency'] = $this->_getCurrency();
				$transaction['total']['basePrice'] = (float)$order->getSubtotal();
				$transaction['total']['transactionTotal'] = (float)$order->getGrandTotal();
				
				
				$voucher = $order->getCouponCode();
				$transaction['total']['voucherCode'] = $voucher ? $voucher : "";
				$voucher_discount = -1 * $order->getDiscountAmount();
				$transaction['total']['voucherDiscount'] = $voucher_discount ? $voucher_discount : 0;
				
				$transaction['total']['shipping'] = (float)$order->getShippingAmount();
				$transaction['total']['shippingMethod'] = $this->_extractShippingMethod($order);
				
				// Get addresses
				$transaction['profile'] = array();
				if ($order->getBillingAddress()) {
					$billingAddress = $order->getBillingAddress();
					$transaction['profile']['address'] = $this->_getAddress($billingAddress);
				}
				
				if ($order->getShippingAddress()) {
					$shippingAddress = $order->getShippingAddress();
					$transaction['profile']['shippingAddress'] = $this->_getAddress($shippingAddress);
				}
				
				// Get items
				$items = $order->getAllVisibleItems();
				$line_items = $this->_getLineItems($items, 'transaction');
				$transaction['item'] = $line_items;
				$this->_transaction = $transaction;
            }
        } catch (Exception $e) {
			
        }
    }
	
	
	public function _getAddress($address){
		$billing = array();
		try {
			if ($address) {
				$billing['line1'] = $address->getName();
				$billing['line2'] = $address->getStreetFull();
				$billing['city'] = $address->getCity();
				$billing['postalCode'] = $address->getPostcode();
				$billing['country'] = $address->getCountry();
				$state = $address->getRegion();
				$billing['stateProvince'] = $state ? $state : '';
			}
		} catch (Exception $e) {
			
		}
		return $billing;
	}
	
	
}