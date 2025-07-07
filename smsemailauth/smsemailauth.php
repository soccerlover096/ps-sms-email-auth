<?php
if (!defined('_PS_VERSION_')) exit;

class SmsEmailAuth extends Module
{
    public function __construct()
    {
        $this->name = 'smsemailauth';
        $this->tab = 'authentication';
        $this->version = '1.0.0';
        $this->author = 'Your Name';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        
        parent::__construct();
        
        $this->displayName = $this->l('SMS/Email Authentication');
        $this->description = $this->l('Login and registration via SMS and Email');
        $this->confirmUninstall = $this->l('Are you sure?');
    }
    
    public function install()
    {
        Configuration::updateValue('SMSAUTH_ENABLED', false);
        Configuration::updateValue('SMSAUTH_CODE_LENGTH', 6);
        Configuration::updateValue('SMSAUTH_CODE_EXPIRY', 300);
        Configuration::updateValue('SMSAUTH_RESEND_DELAY', 60);
        
        return parent::install() &&
            $this->registerHook('displayCustomerLoginFormAfter') &&
            $this->registerHook('actionAuthentication') &&
            $this->registerHook('displayHeader') &&
            $this->installDb() &&
            $this->installTab();
    }
    
    public function uninstall()
    {
        Configuration::deleteByName('SMSAUTH_ENABLED');
        Configuration::deleteByName('SMSAUTH_CODE_LENGTH');
        Configuration::deleteByName('SMSAUTH_CODE_EXPIRY');
        Configuration::deleteByName('SMSAUTH_RESEND_DELAY');
        
        return parent::uninstall() &&
            $this->uninstallDb() &&
            $this->uninstallTab();
    }
    
    private function installDb()
    {
        $sql = [];
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'smsauth_codes` (
            `id_code` int(11) NOT NULL AUTO_INCREMENT,
            `identifier` varchar(255) NOT NULL,
            `type` varchar(10) NOT NULL,
            `code` varchar(10) NOT NULL,
            `attempts` int(11) DEFAULT 0,
            `date_add` datetime NOT NULL,
            `date_expire` datetime NOT NULL,
            PRIMARY KEY (`id_code`),
            KEY `identifier` (`identifier`),
            KEY `date_expire` (`date_expire`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';
        
        $sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'smsauth_providers` (
            `id_provider` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `type` varchar(10) NOT NULL,
            `settings` text,
            `active` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id_provider`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8mb4;';
        
        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }
    
    private function uninstallDb()
    {
        $sql = [];
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'smsauth_codes`';
        $sql[] = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.'smsauth_providers`';
        
        foreach ($sql as $query) {
            Db::getInstance()->execute($query);
        }
        return true;
    }
    
    private function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminSmsEmailAuth';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'SMS/Email Auth';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentModulesSf');
        $tab->module = $this->name;
        return $tab->add();
    }
    
    private function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminSmsEmailAuth');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }
    
    public function getContent()
    {
        if (Tools::isSubmit('submit'.$this->name)) {
            $this->postProcess();
        }
        return $this->renderForm();
    }
    
    private function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs'
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Module'),
                        'name' => 'SMSAUTH_ENABLED',
                        'values' => [
                            ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')]
                        ]
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Code Length'),
                        'name' => 'SMSAUTH_CODE_LENGTH',
                        'desc' => $this->l('Number of digits in verification code')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Code Expiry (seconds)'),
                        'name' => 'SMSAUTH_CODE_EXPIRY',
                        'desc' => $this->l('How long the code is valid')
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Resend Delay (seconds)'),
                        'name' => 'SMSAUTH_RESEND_DELAY',
                        'desc' => $this->l('Minimum time between code resends')
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save')
                ]
            ]
        ];
        
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->title = $this->displayName;
        $helper->submit_action = 'submit'.$this->name;
        
        $helper->fields_value['SMSAUTH_ENABLED'] = Configuration::get('SMSAUTH_ENABLED');
        $helper->fields_value['SMSAUTH_CODE_LENGTH'] = Configuration::get('SMSAUTH_CODE_LENGTH');
        $helper->fields_value['SMSAUTH_CODE_EXPIRY'] = Configuration::get('SMSAUTH_CODE_EXPIRY');
        $helper->fields_value['SMSAUTH_RESEND_DELAY'] = Configuration::get('SMSAUTH_RESEND_DELAY');
        
        return $helper->generateForm([$fields_form]);
    }
    
    private function postProcess()
    {
        Configuration::updateValue('SMSAUTH_ENABLED', Tools::getValue('SMSAUTH_ENABLED'));
        Configuration::updateValue('SMSAUTH_CODE_LENGTH', Tools::getValue('SMSAUTH_CODE_LENGTH'));
        Configuration::updateValue('SMSAUTH_CODE_EXPIRY', Tools::getValue('SMSAUTH_CODE_EXPIRY'));
        Configuration::updateValue('SMSAUTH_RESEND_DELAY', Tools::getValue('SMSAUTH_RESEND_DELAY'));
    }
}