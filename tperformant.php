<?php
/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2015 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Tperformant extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'tperformant';
        $this->tab = 'checkout';
        $this->version = '0.1.1';
        $this->author = 'tetele';
        $this->need_instance = 0;
        $this->module_key = '31986c8918ce3a5ea09b5797be06b9d1';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('2Performant tracking code');
        $this->description = $this->l(
            'This module implements the 2Performant tracking code from the 2Performant.com affiliate network'
        );

        $this->confirmUninstall = $this->l(
            'Are you sure you want to uninstall the module? This will prevent commission generation'
        );
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('TPERFORMANT_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('displayOrderConfirmation');
    }

    public function uninstall()
    {
        Configuration::deleteByName('TPERFORMANT_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitTperformantModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = true;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitTperformantModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activated'),
                        'name' => 'TPERFORMANT_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in production site'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(
                            'Your 2Performant unique code '.
                            '(get it from https://network.2performant.com/advertiser/settings/tracking_code)'
                        ),
                        'name' => 'TPERFORMANT_PROGRAM_UNIQUE',
                        'label' => $this->l('Program unique'),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'desc' => $this->l(
                            'Your 2Performant confirm code '.
                            '(get it from https://network.2performant.com/advertiser/settings/tracking_code)'
                        ),
                        'name' => 'TPERFORMANT_PROGRAM_CONFIRM',
                        'label' => $this->l('Confirm code'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'TPERFORMANT_LIVE_MODE' => Configuration::get('TPERFORMANT_LIVE_MODE', false),
            'TPERFORMANT_PROGRAM_UNIQUE' => Configuration::get('TPERFORMANT_PROGRAM_UNIQUE', ''),
            'TPERFORMANT_PROGRAM_CONFIRM' => Configuration::get('TPERFORMANT_PROGRAM_CONFIRM', ''),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        $this->context->smarty->assign('form_successes', array(
            'Settings updated!'
        ));
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookActionOrderStatusPostUpdate()
    {
        /* Place your code here. */
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (!Configuration::get('TPERFORMANT_LIVE_MODE', false)) {
            return;
        }

        $programUnique = Configuration::get('TPERFORMANT_PROGRAM_UNIQUE', false);
        $programConfirm = Configuration::get('TPERFORMANT_PROGRAM_CONFIRM', false);

        if (!$programUnique || !$programConfirm) {
            return;
        }

        $order = $params['order'];

        $amount = $order->total_paid_tax_excl - $order->total_shipping_tax_excl - $order->total_wrapping_tax_excl;
        $products = $description = array();

        foreach ($order->getProducts() as $product) {
            $products[] = array(
                'quantity' => $product['product_quantity'],
                'price' => $product['product_price'],
                'name' => $product['product_name']
            );

            $description[] = sprintf('%s x %s', $product['product_quantity'], $product['product_name']);
        }

        $description = implode('|', $description);

        $this->context->smarty->assign(
            array(
                'programUnique' => $programUnique,
                'programConfirm' => $programConfirm,
                'transactionId' => $order->reference,
                'amount' => $amount,
                'description' => $description
            )
        );
        return $this->display(__FILE__, 'orderConfirmation.tpl');
    }
}
