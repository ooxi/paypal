<?php
/**
 * This file is part of OXID eSales PayPal module.
 *
 * OXID eSales PayPal module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales PayPal module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales PayPal module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

require_once 'acceptance/oepaypal/oxidAdditionalSeleniumFunctions.php';

class Acceptance_oePayPal_oePayPalMobileTest extends oxidAdditionalSeleniumFunctions
{
    protected $_sVersion = "EE";

    protected function setUp($skipDemoData = false)
    {
        parent::setUp(false);

        if (OXID_VERSION_PE_PE) :
            $this->_sVersion = "PE";
        endif;
        if (OXID_VERSION_EE) :
            $this->_sVersion = "EE";
        endif;
        if (OXID_VERSION_PE_CE) :
            $this->_sVersion = "CE";
        endif;
    }

    /**
     * Executed after test is down
     *
     */
    protected function tearDown()
    {
        //$this->callUrl( shopURL . "/_restoreDB.php", "restoreDb=1" );
        //parent::tearDown();
    }

    /**
     * Call script file
     *
     * @param        $sShopUrl
     * @param string $sParams
     */
    public function callUrl($sShopUrl, $sParams = "")
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sShopUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sParams);
        curl_setopt($ch, CURLOPT_USERAGENT, "OXID-SELENIUMS-CONNECTOR");
        $sRes = curl_exec($ch);

        curl_close($ch);
    }

    /**
     * Returns PayPal login data by variable name
     *
     * @param $sVarName
     *
     * @return mixed|null|string
     * @throws Exception
     */
    public function getLoginDataByName($sVarName)
    {
        if (!$sVarValue = getenv($sVarName)) {
            $sVarValue = $this->getArrayValueFromFile($sVarName, 'acceptance/oepaypal/testdata/oepaypalData.php');
        }

        if (!$sVarValue) {
            throw new Exception('Undefined variable: ' . $sVarName);
        }

        return $sVarValue;
    }


    // ------------------------ PayPal module ----------------------------------

    /**
     * test for activating mobile theme as main theme
     *
     * @group paypal_standalone_mobile
     */
    public function testActivateMobilePayPal()
    {
        $this->getLoginDataByName('sOEPayPalUsername');
        $this->open(shopURL . "_prepareDB.php?version=" . $this->_sVersion);
        $this->open(shopURL . "admin");
        $this->loginAdminForModule("Extensions", "Modules");

        $this->openTab("link=PayPal");
        $this->frame("edit");
        $this->clickAndWait("module_activate");
        $this->frame("list");
        $this->clickAndWait("//a[text()='Settings']");
        $this->frame("edit");
        $this->click("//b[text()='API signature']");
        $this->click("//b[text()='Development settings']");

        $this->select("//select[@name='confselects[sOEPayPalTransactionMode]']", "value=Authorization");

        $this->type("//input[@name='confstrs[sOEPayPalUsername]']", $this->getLoginDataByName('sOEPayPalUsername'));
        $this->type("//input[@class='password_input'][1]", $this->getLoginDataByName('sOEPayPalPassword'));
        $this->type("//input[@name='confpassword[sOEPayPalPassword]']", $this->getLoginDataByName('sOEPayPalPassword'));
        $this->type("//input[@name='confstrs[sOEPayPalSignature]']", $this->getLoginDataByName('sOEPayPalSignature'));

        $this->click("//input[@name='confbools[blOEPayPalSandboxMode]' and @type='checkbox']");
        $this->type("//input[@name='confstrs[sOEPayPalSandboxUsername]']", $this->getLoginDataByName('sOEPayPalSandboxUsername'));
        $this->type("css=.password_input:nth(2)", $this->getLoginDataByName('sOEPayPalSandboxPassword'));
        $this->type("//input[@name='confpassword[sOEPayPalSandboxPassword]']", $this->getLoginDataByName('sOEPayPalSandboxPassword'));
        $this->type("//input[@name='confstrs[sOEPayPalSandboxSignature]']", $this->getLoginDataByName('sOEPayPalSandboxSignature'));
        $this->clickAndWait("//input[@name='save']");

        $this->selectMenu("Extensions", "Modules");
        $this->openTab("link=OXID eShop theme switch");
        $this->frame("list");
        $this->clickAndWait("//a[text()='Settings']");
        $this->frame("edit");
        $this->click("//b[text()='General parameters']");
        $this->type("//input[@name='confstrs[sOEThemeSwitcherMobileTheme]']", 'some_unexisting_theme');
        $this->clickAndWait("//input[@name='save']");
        $this->frame("list");
        $this->clickAndWait("//a[text()='Overview']");
        $this->frame("edit");
        $this->clickAndWait("module_activate");

        $this->selectMenu("Extensions", "Themes");
        $this->openTab("link=OXID eShop mobile theme", "//input[@value='Activate']");
        $this->clickAndWaitFrame("//input[@value='Activate']", "list");

        $this->callUrl(shopURL . "/_restoreDB.php", "dumpDb=1");
    }


    /**
     * testing PayPal ECS in detail page
     *
     * @group paypal_standalone_mobile
     */
    public function testMobileECS()
    {
        // Open shop and add product to the basket
        $this->openShop();
        $this->loginInFrontendMobile("testing_account@oxid-esales.com", "useruser");
        $this->searchFor("1001");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div[2]/h4/a");
        $this->clickAndWait("id=toBasket");

        // Open ECS in details page
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->assertTrue($this->isElementPresent("id=actionNotAddToBasketAndGoToCheckout"), "No button in PayPal popup");
        $this->assertTrue($this->isElementPresent("id=actionAddToBasketAndGoToCheckout"), "No button in PayPal popup");
        $this->assertTrue($this->isElementPresent("link=open current cart"), "No link open current cart in popup");
        $this->assertTrue($this->isElementPresent("//button[text()='cancel']"), "No cancel button in PayPal popup");

        // Select add to basket and go to checkout
        $this->clickAndWait("id=actionAddToBasketAndGoToCheckout");
        $this->assertTrue($this->isTextPresent("Item price: €0,99"), "Item price was not displayed or was displayed incorrectly in PayPal");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"), "Item quantity was not displayed or was displayed incorrectly in PayPal");

        // Cancel order
        $this->clickAndWait("name=cancel_return");
        // Go to checkout with PayPal  with same amount in basket
        $this->clickAndWait("id=paypalExpressCheckoutDetailsButton");
        $this->clickAndWait("id=actionNotAddToBasketAndGoToCheckout");
        $this->assertTrue($this->isTextPresent("Item price: €0,99"), "Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("€1,98"), "Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"), "Item quantity doesn't mach ot didn't displayed");

        // Cancel order
        $this->clickAndWait("name=cancel_return");

        // Go to home page and purchase via PayPal
        $this->clickAndWait("id=miniBasket");
        $this->assertTrue($this->isTextPresent("1,98 €"), "Item price doesn't mach ot didn't displayed");

        $this->clickAndWait("//input[@name='paypalExpressCheckoutButton']");
        $this->assertTrue($this->isTextPresent("€1,98"), "Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 2"), "Item quantity doesn't mach ot didn't displayed");
        $this->waitForItemAppear("id=submitLogin");

        $this->_loginToSandbox();

        $this->waitForItemAppear("id=continue");
        $this->assertTrue($this->isTextPresent("Test product 1"), "Purchased product name is not displayed");
        $this->assertTrue($this->isTextPresent("€1,98"), "Item price doesn't mach ot didn't displayed");
        $this->assertTrue($this->isTextPresent("exact:Anzahl: 2"), "Item quantity doesn't mach ot didn't displayed");
        $this->clickAndWait("id=continue");

        $this->waitForItemAppear("id=miniBasket");

        $this->assertTrue($this->isElementPresent("link=Test product 1"), "Purchased product name is not displayed in last order step");
        $this->assertEquals("Grand total 1,98 €", $this->getText("basketGrandTotal"), "Grand total price changed  or didn't displayed");
        $this->assertTrue($this->isTextPresent("PayPal"), "Payment method not displayed in last order step");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTrue($this->isTextPresent("Thank you for your order in OXID eShop"), "Order is not finished successful");
    }


    /**
     * login customer by using login fly out form.
     *
     * @param string  $userName     user name (email).
     * @param string  $userPass     user password.
     * @param boolean $waitForLogin if needed to wait until user get logged in.
     */
    public function loginInFrontendMobile($userName, $userPass, $waitForLogin = true)
    {
        $this->selectWindow(null);
        $this->clickAndWait("//a[text()='Log in']");
        $this->type("//input[@id='loginUser']", $userName);
        $this->type("//input[@id='loginPwd']", $userPass);
        if ($waitForLogin) {
            $this->clickAndWait("//form[@name='login']//input[@type='submit']", "//a[text()='Log out']");
        } else {
            $this->clickAndWait("//form[@name='login']//input[@type='submit']");
        }
    }

    /**
     * testing PayPal ECS in basket
     *
     * @group paypal_standalone_mobile
     */
    public function testMobilePayPalBasket()
    {
        // Open shop and add product to the basket
        $this->openShop();
        $this->searchFor("1402");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div[2]/h4/a");
        $this->clickAndWait("id=toBasket");

        //Open basket and press top ECS button
        $this->clickAndWait("id=miniBasket");
        $this->assertTrue($this->isTextPresent("159,00 €"), "Item price doesn't mach or doesn't displayed 1");
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepTop']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on top of basket's page 1");
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepBottom']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on bottom of basket's page 1");
        $this->clickAndWait("//div[@id='btnNextStepTop']//form//input[@name='paypalExpressCheckoutButton']");

        //Cancel order in PayPal and return to the basket
        $this->assertTrue($this->isTextPresent("€159.00"), "Item price doesn't mach or doesn't displayed 2");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 1"), "Item quantity doesn't mach or doesn't displayed 2");
        $this->clickAndWait("name=cancel_return");

        //Press bottom ECS button
        $this->clickAndWait("//div[@id='btnNextStepBottom']//form//input[@name='paypalExpressCheckoutButton']");
        $this->assertTrue($this->isTextPresent("€159.00"), "Item price doesn't mach or doesn't displayed 3");
        $this->assertTrue($this->isTextPresent("exact:Quantity: 1"), "Item quantity doesn't mach or doesn't displayed 3");
        $this->clickAndWait("name=cancel_return");

        //Checking whether user was redirected back to the basket
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepTop']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on top of basket's page 3");
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepBottom']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on bottom of basket's page 3");
    }

    /**
     * testing PayPal Standard payment method checkout, and "Used PayPal solution" options, which turn off ECS
     *
     * @group paypal_standalone_mobile
     * @group paypal_mobile
     */
    public function testStandardPayPal()
    {
        //Add product and go to checkout
        $this->openShop();
        $this->loginInFrontendMobile("testing_account@oxid-esales.com", "useruser");
        $this->searchFor("1001");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div[2]/h4/a");
        $this->clickAndWait("id=toBasket");
        $this->clickAndWait("id=minibasketIcon");
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepTop']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on top of basket's page");
        $this->assertTrue($this->isElementPresent("//div[@id='btnNextStepBottom']//form//input[@name='paypalExpressCheckoutButton']"), "No ECS button on bottom of basket's page");
        $this->assertEquals("Test product 1", $this->getText("link=Test product 1"), "No product name in detail page");
        $this->clickAndWait("//input[@value='Continue']");
        $this->clickAndWait("id=userNextStepBottom");

        //Go to the 3rd step and check PayPal payment method
        $this->click("//div[@id='shippingMethods']/div");
        $this->clickAndWait("//a[contains(text(),'Test S&H set')]");
        $this->click("//div[@id='paymentMethods']/div");
        $this->click("link=PayPal");
        $this->assertTrue($this->isElementPresent("css=img.paypalPaymentImg"), "No PayPal logo in 3rd payment step");
        $this->assertTrue($this->isElementPresent("link=exact:?"), "No sign '?' near PayPal logo");
        $this->assertTrue($this->isElementPresent("id=displayCartInPayPal"), "No Display Cart In PayBal checkbox in payment step");
        $this->assertTrue($this->isChecked("id=displayCartInPayPal"), "Display Cart In PayPal checkbox is not checked in payment step");
        $this->clickAndWait("id=paymentNextStepBottom");

        //Go to sandbox to make order
        $this->_loginToSandbox();
        $this->waitForItemAppear("id=continue");
        $this->waitForItemAppear("id=displayShippingAmount");
        $this->click("id=continue");
        $this->waitForItemAppear("id=orderPayment");
        $this->clickAndWait("//button[text()='Order now']");
        $this->assertTrue($this->isTextPresent("Thank you for your order in OXID eShop"), "Order is not finished successful");

        // Turn Off all PayPal shortcut in frontend
        if (OXID_VERSION_EE):
            $this->open(shopURL . "/_updateDB.php?filename=testPayPalShortcut_ee.sql");
        endif;
        if (OXID_VERSION_PE):
            $this->open(shopURL . "/_updateDB.php?filename=testPayPalShortcut_pe.sql");
        endif;

        //Add product and go to checkout
        $this->openShop();
        $this->loginInFrontendMobile("testing_account@oxid-esales.com", "useruser");
        $this->searchFor("1001");
        $this->clickAndWait("//ul[@id='searchList']/li/form/div[2]/h4/a");
        $this->assertFalse($this->isElementPresent("id=paypalExpressCheckoutDetailsButton"), "ECS button should be not visible in detail page");
        $this->clickAndWait("id=toBasket");
        $this->clickAndWait("id=minibasketIcon");
        $this->assertFalse($this->isElementPresent("//div[@id='btnNextStepTop']//form//input[@name='paypalExpressCheckoutButton']"), "ECS button should not be displayed on top of basket's page");
        $this->assertFalse($this->isElementPresent("//div[@id='btnNextStepBottom']//form//input[@name='paypalExpressCheckoutButton']"), "ECS button should not be displayed on bottom of basket's page");
        $this->assertEquals("Test product 1", $this->getText("link=Test product 1"));
        $this->clickAndWait("//input[@value='Continue']");
        $this->clickAndWait("id=userNextStepBottom");

        //Go to the 3rd step and select PayPal payment method
        $this->click("//div[@id='shippingMethods']/div");
        $this->clickAndWait("//a[contains(text(),'Test S&H set')]");
        $this->click("//div[@id='paymentMethods']/div");
        $this->assertFalse($this->isElementPresent("link=PayPal"), "PayPal payment method should not be displayed, as option in admin 'PayPal Basis' is off");

        //Check does PayPal shortcut
        $this->assertFalse($this->isElementPresent("css=img.paypalPaymentImg"), "PayPal logo should not be displayed, as option in admin 'PayPal Basis' is of");
        $this->assertFalse($this->isElementPresent("link=exact:?"), "Sign '?' near PayPal logo should not be displayed, as option in admin 'PayPal Basis' is off");
    }

    /**
     * Login to PayPal sandbox.
     *
     * @param string $sLoginEmail    email to login.
     * @param string $sLoginPassword password to login.
     */

    protected function _loginToSandbox($sLoginEmail = null, $sLoginPassword = null)
    {
        if (!isset($sLoginEmail)) {
            $sLoginEmail = $this->getLoginDataByName('sBuyerLogin');
        }
        if (!isset($sLoginPassword)) {
            $sLoginPassword = $this->getLoginDataByName('sBuyerPassword');
        }

        $this->type("login_email", $sLoginEmail);
        $this->type("login_password", $sLoginPassword);
        $this->click("id=submitLogin");
    }
}