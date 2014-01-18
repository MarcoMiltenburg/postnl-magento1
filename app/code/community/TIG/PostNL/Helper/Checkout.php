<?php
/**
 *                  ___________       __            __   
 *                  \__    ___/____ _/  |_ _____   |  |  
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/       
 *          ___          __                                   __   
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_ 
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |  
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|  
 *                  \/                           \/               
 *                  ________       
 *                 /  _____/_______   ____   __ __ ______  
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \ 
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/ 
 *                        \/                       |__|    
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL: 
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@totalinternetgroup.nl for more information.
 *
 * @copyright   Copyright (c) 2013 Total Internet Group B.V. (http://www.totalinternetgroup.nl)
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Helper_Checkout extends TIG_PostNL_Helper_Data
{
    /**
     * XML path to checkout on/off switch
     */
    const XML_PATH_CHECKOUT_ACTIVE = 'postnl/checkout/active';
    const XML_PATH_USE_CHECKOUT    = 'postnl/cif/use_checkout';
    
    /**
     * XML path to all PostNL Checkout payment methods.
     * N.B. last part of the XML path is missing.
     */
    const XML_PATH_CHECKOUT_PAYMENT_METHOD = 'postnl/checkout_payment_methods';
    
    /**
     * XML path to test / live mode setting
     */
    const XML_PATH_TEST_MODE = 'postnl/checkout/mode';
    
    /**
     * XML path for config options used to determine whether or not PostNL Checkout is available
     */
    const XML_PATH_SHOW_CHECKOUT_FOR_LETTER     = 'postnl/checkout/show_checkout_for_letter';
    const XML_PATH_SHOW_CHECKOUT_FOR_BACKORDERS = 'postnl/checkout/show_checkout_for_backorders';
    
    /**
     * Log filename to log all non-specific PostNL debug messages
     */
    const POSTNL_DEBUG_LOG_FILE = 'TIG_PostNL_Checkout_Debug.log';
    
    /**
     * Array of payment methods supported by PostNL Checkout. 
     * Keys are the names used in system.xml, values are codes used by PostNL Checkout.
     * 
     * @var array
     */
    protected $_checkoutPaymentMethods = array(
        'ideal'                  => 'IDEAL',
        'creditcard'             => 'CREDITCARD',
        'checkpay'               => 'CHECKPAY',
        'paypal'                 => 'PAYPAL',
        'directdebit'            => 'MACHTIGING',
        'acceptgiro'             => 'ACCEPTGIRO',
        'vooraf_betalen'         => 'VOORAF',
        'termijnen'              => 'TERMIJNEN',
        'giftcard'               => 'KADOBON',
        'rabobank_internetkassa' => 'RABOINTKASSA', 
        'afterpay'               => 'AFTERPAY',
        'klarna'                 => 'KLARNA',
    );
    
    /**
     * An array of required configuration settings
     * 
     * @var array
     */
    protected $_checkoutRequiredFields = array(
        'postnl/checkout/active',
        'postnl/cif/webshop_id',
        'postnl/cif/public_webshop_id',
    );
    
    /**
     * Array containing conversions between PostNL CHeckout payment option fields and those used by Magento payment methods.
     * This array should be extended as time goes on in order to support as many payment methods as possible.
     * 
     * @var array
     */
    protected $_optionConversionArray = array(
        'sisow' => array(
            '0031' => '01',
            '0721' => '05',
            '0021' => '06',
            '0751' => '07',
            '0761' => '02',
            '0771' => '08',
            '0091' => '04',
            '0511' => '09',
            '0161' => '10',
         ),
         'buckaroo3extended_ideal' => array(
            '0031' => 'ABNANL2A',
            '0081' => 'FRBKNL2L',
            '0721' => 'INGBNL2A',
            '0021' => 'RABONL2U',
            '0751' => 'SNSBNL2A',
            '0761' => 'ASNBNL21',
            '0771' => 'RBRBNL21',
            '0091' => 'FRBKNL2L',
            '0511' => 'TRIONL2U',
            '0161' => 'FVLBNL22',
         ),
    );
    
    /**
     * Gets a list of payment methods supported by PostNL Checkout
     * 
     * @return array
     */
    public function getCheckoutPaymentMethods()
    {
        $paymentMethods = $this->_checkoutPaymentMethods;
        return $paymentMethods;
    }
    
    /**
     * Returns an array of configuration settings that must be entered for PostNL Checkout to function
     * 
     * @return array
     */
    public function getCheckoutRequiredFields()
    {
        $requiredFields = $this->_checkoutRequiredFields;
        return $requiredFields;
    }
    
    /**
     * Returns a conversion array used to convert PostNL Checkout payment method fields to those used by Magento payment methods.
     * 
     * @return array
     */
    public function getOptionConversionArray()
    {
        $conversionArray = array(
            'conversion_array' => $this->_optionConversionArray
        );
        
        $conversionObject = new Varien_Object($conversionArray);
        
        /**
         * You can observe this event in order to add (or modify) conversion options. This prevents you from having to overload 
         * this helper if you want to change this functionality.
         */
        Mage::dispatchEvent(
            'postnl_checkout_option_conversion_before', 
            array(
                'conversion_object' => $conversionObject,
            )
        );
        
        return $conversionObject->getConversionArray();;
    }
    
    /**
     * Restores a quote to working order
     * 
     * @param Mage_Sales_Model_Quote $quote
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function restoreQuote(Mage_Sales_Model_Quote $quote)
    {
        $quote->setIsActive(true)
              ->save();
        
        return $quote;
    }
    
    /**
     * Check if PostNL Checkout may be used for a specified quote
     * 
     * @param Mage_Sales_Model_Quote $quote
     * @param boolean $sendPing
     * 
     * @return boolean
     */
    public function canUsePostnlCheckout(Mage_Sales_Model_Quote $quote, $sendPing = false)
    {
        if (Mage::registry('can_use_postnl_checkout') !== null) {
            return Mage::registry('can_use_postnl_checkout');
        }
        
        $checkoutEnabled = $this->isCheckoutEnabled();
        if (!$checkoutEnabled) {
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }
        
        /**
         * PostNL Checkout cannot be used for virtual orders
         */
        if ($quote->isVirtual()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0104',
                    'message' => $this->__('The quote is virtual.'),
                )
            );
            Mage::register('postnl_enabled_checkout_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }
        
        /**
         * Check if the quote has a valid minimum amount
         */
        if (!$quote->validateMinimumAmount()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0105',
                    'message' => $this->__("The quote's grand total is below the minimum amount required."),
                )
            );
            Mage::register('postnl_enabled_checkout_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }
        
        /**
         * Check that dutch addresses are allowed
         */
        if (!$this->canUseStandard()) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0106',
                    'message' => $this->__('No standard product options are enabled. At least 1 option must be active.'),
                )
            );
            Mage::register('postnl_enabled_checkout_errors', $errors);
            Mage::register('can_use_postnl_checkout', false);
            return false;
        }
        
        $storeId = $quote->getStoreId();
        
        /**
         * Check if PostNL Checkout may be used for 'letter' orders and if not, if the quote could fit in an envelope
         */
        $showCheckoutForLetters = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_CHECKOUT_FOR_LETTER, $storeId);
        if (!$showCheckoutForLetters) {
            $isLetterQuote = $this->quoteIsLetter($quote, $storeId);
            if ($isLetterQuote) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0101',
                        'message' => $this->__("The quote's total weight is below the miniumum required to use PostNL Checkout."),
                    )
                );
                Mage::register('postnl_enabled_checkout_errors', $errors);
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
        }
        
        /**
         * Check if PostNL Checkout may be used for out-og-stock orders and if not, whether the quote has any such products
         */
        $showCheckoutForBackorders = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_CHECKOUT_FOR_BACKORDERS, $storeId);
        if (!$showCheckoutForBackorders) {
            $containsOutOfStockItems = $this->quoteHasOutOfStockItems($quote);
            if ($containsOutOfStockItems) {
                $errors = array(
                    array(
                        'code'    => 'POSTNL-0102',
                        'message' => $this->__('One or more items in the cart are out of stock.'),
                    )
                );
                Mage::register('postnl_enabled_checkout_errors', $errors);
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
        }
        
        if ($sendPing === true) {
            /**
             * Send a ping request to see if the PostNL Checkout service is available
             */
            try {
                $cif = Mage::getModel('postnl_checkout/cif');
                $result = $cif->ping();
            } catch (Exception $e) {
                $this->logException($e);
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
            
            if ($result !== 'OK') {
                Mage::register('can_use_postnl_checkout', false);
                return false;
            }
        }
        
        Mage::register('can_use_postnl_checkout', true);
        return true;
    }

    /**
     * Checks if a quote is a letter.
     * For now it only checks if the total weight of the quote is less than 2 KG
     * 
     * @param mixed $quoteItems Either a quote object, or an array or collection of quote items
     * @param null|int $storeId
     * 
     * @return boolean
     * 
     * @todo Expand this method to also check the size of products to see if they fit in an envelope
     */
    public function quoteIsLetter($quoteItems, $storeId = null)
    {
        if ($quoteItems instanceof Mage_Sales_Model_Quote) {
            $quoteItems = $quoteItems->getAllItems();
        }
        
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $totalWeight = 0;
        foreach ($quoteItems as $item) {
            $totalWeight += $item->getRowWeight();
        }
        
        $kilograms = Mage::helper('postnl/cif')->standardizeWeight($totalWeight, $storeId);
        
        if ($kilograms < 2) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if a quote has out of stock products
     * 
     * @param mixed $quoteItems Either a quote object, or an array or collection of quote items
     * 
     * @return boolean
     */
    public function quoteHasOutOfStockItems($quoteItems)
    {
        if ($quoteItems instanceof Mage_Sales_Model_Quote) {
            $quoteItems = $quoteItems->getAllItems();
        }
        
        $productIds = array();
        foreach ($quoteItems as $item) {
            $productIds[] = $item->getProductId();
        }
        
        /**
         * Filter the stock collection by the products in the quote and whose quantity is equal to or below 0
         * 
         * The resulting query:
         * SELECT `main_table`.`item_id` , `cp_table`.`type_id`
         * FROM `cataloginventory_stock_item` AS `main_table`
         * INNER JOIN `catalog_product_entity` AS `cp_table` ON main_table.product_id = cp_table.entity_id
         * WHERE (
         *     product_id
         *     IN (
         *         {$productIds}
         *     )
         * )
         * AND (
         *     qty <=0
         * )
         */
        $stockCollection = Mage::getResourceModel('cataloginventory/stock_item_collection');
        $stockCollection->addFieldToSelect('item_id')
                        ->addFieldToFilter('product_id', array('in' => $productIds))
                        ->addFieldToFilter('qty', array('lteq' => 0));
        
        if ($stockCollection->getSize() > 0) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if the module is set to test mode
     * 
     * @return boolean
     */
    public function isTestMode($storeId = false)
    {
        if (Mage::registry('postnl_checkout_test_mode') !== null) {
            return Mage::registry('postnl_checkout_test_mode');
        }
        
        if ($storeId === false) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $testModeAllowed = $this->isTestModeAllowed();
        if (!$testModeAllowed) {
            Mage::register('postnl_checkout_test_mode', false);
            return false;
        }
        
        $testMode = Mage::getStoreConfigFlag(self::XML_PATH_TEST_MODE, $storeId);
        
        Mage::register('postnl_checkout_test_mode', $testMode);
        return $testMode;
    }
    
    /**
     * Checks if PostNL Checkout is active
     * 
     * @param null|int $storeId
     * 
     * @return boolean
     */
    public function isCheckoutActive($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $useCheckout = Mage::getStoreConfigFlag(self::XML_PATH_USE_CHECKOUT, $storeId);
        
        if (!$useCheckout) {
            return false;
        }
        
        $isActive = Mage::getStoreConfigFlag(self::XML_PATH_CHECKOUT_ACTIVE, $storeId);
        return $isActive;
    }
    
    /**
     * Check if PostNL checkout is enabled
     * 
     * @param null|int $storeId
     * 
     * @return boolean
     */
    public function isCheckoutEnabled($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $isPostnlEnabled = $this->isEnabled($storeId, false, $this->isTestMode());
        if ($isPostnlEnabled === false) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0107',
                    'message' => $this->__('You have not yet enabled the PostNL extension.'),
                )
            );
            Mage::register('postnl_enabled_checkout_errors', $errors);
            return false;
        }
        
        $isCheckoutActive = $this->isCheckoutActive($storeId);
        if (!$isCheckoutActive) {
            $errors = array(
                array(
                    'code'    => 'POSTNL-0027',
                    'message' => $this->__('You have not yet enabled PostNL Checkout.'),
                )
            );
            Mage::register('postnl_enabled_checkout_errors', $errors);
            return false;
        }
        
        $isConfigured = $this->isCheckoutConfigured($storeId);
        if (!$isConfigured) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if all required fields are entered
     * 
     * @param null|int $storeId
     * 
     * @return boolean
     */
    public function isCheckoutConfigured($storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        
        $errors = array();
        
        /**
         * Get the system > config fields for this section
         */
        $configFields = Mage::getSingleton('adminhtml/config');
        $sections     = $configFields->getSections('postnl');
        $section      = $sections->postnl;
        
        /**
         * First check if all required configuration settings are entered
         */
        $requiredFields = $this->getCheckoutRequiredFields();
        foreach ($requiredFields as $requiredField) {
            $value = Mage::getStoreConfig($requiredField, $storeId);
            
            if ($value === null || $value === '') {
                $fieldParts = explode('/', $requiredField);
                $field = $fieldParts[2];
                $group = $fieldParts[1];
                
                $label      = (string) $section->groups->$group->fields->$field->label;
                $groupLabel = (string) $section->groups->$group->label;
                $errors[] = array(
                    'code'    => 'POSTNL-0034',
                    'message' => $this->__('%s > %s is required.', $this->__($groupLabel), $this->__($label)),
                );
            }
        }
        
        /**
         * If any errors were detected, add them to the registry and return false
         */
        if (!empty($errors)) {
            Mage::register('postnl_is_configured_checkout_errors', $errors);
            return false;
        }
        
        /**
         * Go through each supported payment method. At least one of them must be activated.
         */
        $paymentMethods = $this->getCheckoutPaymentMethods();
        $paymentMethodSettings = Mage::getStoreConfig(self::XML_PATH_CHECKOUT_PAYMENT_METHOD, $storeId);
        foreach ($paymentMethods as $methodCode => $method) {
            if (array_key_exists($methodCode, $paymentMethodSettings)
                && $paymentMethodSettings[$methodCode] === '1'
            ) {
                return true;
            }
        }
        
        /**
         * If no payment method was activated the extension is not configured properly
         */
        $errors = array(
            array(
                'code'    => 'POSTNL-0028',
                'message' => $this->__('You need to enable at least one payment method.'),
            )
        );
        Mage::register(
            'postnl_is_configured_checkout_errors', 
            $errors
        );
        return false;
    }
}
