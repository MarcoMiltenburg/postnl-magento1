<?php
/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
class TIG_PostNL_Test_Unit_Helper_CifTest extends TIG_PostNL_Test_Unit_Framework_TIG_Test_TestCase
{
    private $defaultAvailableProductOptions;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->defaultAvailableProductOptions = Mage::getStoreConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_AVAILABLE_PRODUCT_OPTIONS
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        /**
         * Some unittests depend on the default config value of the product options.
         * By resetting this config option those tests won't be broken.
         */
        Mage::app()->getStore()->setConfig(
            TIG_PostNL_Helper_DeliveryOptions::XPATH_AVAILABLE_PRODUCT_OPTIONS,
            $this->defaultAvailableProductOptions
        );
    }

    /**
     * @return TIG_PostNL_Helper_Cif
     */
    public function _getInstance()
    {
        return Mage::helper('postnl/cif');
    }

    /**
     * @return array
     */
    public function getExtraAtHomeProductCodesProvider()
    {
        return array(
            array(3628),
            array(3629),
            array(3653),
            array(3783),
            array(3790),
            array(3791),
            array(3792),
            array(3793),
        );
    }

    /**
     * @param $productCode
     *
     * @dataProvider getExtraAtHomeProductCodesProvider
     */
    public function testGetExtraAtHomeProductCodes($productCode)
    {
        $instance = $this->_getInstance();
        $result = $instance->getExtraAtHomeProductCodes(true);

        $this->assertArrayHasKey($productCode, $result);
    }

    public function isMultiColliAllowedProvider()
    {
        return array(
            'NL' => array('NL', true),
            'BE' => array('BE', true),
            'DE' => array('DE', false),
        );
    }

    /**
     * @dataProvider isMultiColliAllowedProvider
     */
    public function testIsMultiColliAllowedByShipment($destinationCountry, $expected)
    {
        $shipmentMock = $this->getMock('Mage_Sales_Model_Order_Shipment');

        /**
         * Set shipping address to overwrite the destination country code.
         *
         * @var Mage_Sales_Model_Order_Address $shippingAddress
         */
        $shippingAddress = new Mage_Sales_Model_Order_Address;
        $shippingAddress->setCountryId($destinationCountry);
        $shipmentMock->method('getShippingAddress')->willReturn($shippingAddress);

        $this->assertSame($expected, $this->_getInstance()->isMultiColliAllowed($shipmentMock));
    }

    /**
     * @dataProvider isMultiColliAllowedProvider
     */
    public function testIsMultiColliAllowedByOrder($destinationCountry, $expected)
    {
        $orderMock = $this->getMock('Mage_Sales_Model_Order');

        /**
         * @var Mage_Sales_Model_Order_Address $shippingAddress
         */
        $shippingAddress = new Mage_Sales_Model_Order_Address;
        $shippingAddress->setCountryId($destinationCountry);
        $orderMock->method('getShippingAddress')->willReturn($shippingAddress);

        $this->assertSame($expected, $this->_getInstance()->isMultiColliAllowed($orderMock));
    }

    /**
     * @dataProvider isMultiColliAllowedProvider
     */
    public function testIsMultiColliAllowedByPostNLShipment($destinationCountry, $expected)
    {
        $postnlShipmentMock = $this->getMockBuilder('TIG_PostNL_Model_Core_Shipment')
            ->setMethods(array('getShippingAddress'))
            ->getMock();

        /**
         * @var Mage_Sales_Model_Order_Address $shippingAddress
         */
        $shippingAddress = new Mage_Sales_Model_Order_Address;
        $shippingAddress->setCountryId($destinationCountry);
        $postnlShipmentMock->method('getShippingAddress')->willReturn($shippingAddress);

        $this->assertSame($expected, $this->_getInstance()->isMultiColliAllowed($postnlShipmentMock));
    }

    /**
     * @dataProvider isMultiColliAllowedProvider
     */
    public function testIsMultiColliAllowedByPostNLOrder($destinationCountry, $expected)
    {
        $postnlOrderMock = $this->getMockBuilder('TIG_PostNL_Model_Core_Order')
            ->setMethods(array('getOrder', 'getShippingAddress'))
            ->getMock();
        $postnlOrderMock->expects($this->once())->method('getOrder')->willReturnSelf();

        /**
         * @var Mage_Sales_Model_Order_Address $shippingAddress
         */
        $shippingAddress = new Mage_Sales_Model_Order_Address;
        $shippingAddress->setCountryId($destinationCountry);
        $postnlOrderMock->method('getShippingAddress')->willReturn($shippingAddress);

        $this->assertSame($expected, $this->_getInstance()->isMultiColliAllowed($postnlOrderMock));
    }
}
