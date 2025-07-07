<?php
class SmsEmailAuthAuthModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        if (!Configuration::get('SMSAUTH_ENABLED')) {
            Tools::redirect('index.php?controller=authentication');
        }
        
        $this->context->smarty->assign([
            'countries' => Country::getCountries($this->context->language->id),
            'resend_delay' => Configuration::get('SMSAUTH_RESEND_DELAY')
        ]);
        
        $this->setTemplate('module:smsemailauth/views/templates/front/auth.tpl');
    }
    
    public function postProcess()
    {
        if (Tools::isSubmit('ajax')) {
            $action = Tools::getValue('action');
            
            switch ($action) {
                case 'checkUser':
                    $this->ajaxCheckUser();
                    break;
                case 'sendCode':
                    $this->ajaxSendCode();
                    break;
                case 'verifyCode':
                    $this->ajaxVerifyCode();
                    break;
                case 'register':
                    $this->ajaxRegister();
                    break;
            }
        }
    }
    
    private function ajaxCheckUser()
    {
        $identifier = Tools::getValue('identifier');
        $type = Tools::getValue('type');
        
        $exists = false;
        if ($type === 'email') {
            $exists = Customer::customerExists($identifier);
        } else {
            $sql = 'SELECT id_customer FROM '._DB_PREFIX_.'customer WHERE phone_mobile = "'.pSQL($identifier).'"';
            $exists = Db::getInstance()->getValue($sql) ? true : false;
        }
        
        die(json_encode(['exists' => $exists]));
    }
    
    private function ajaxSendCode()
    {
        $identifier = Tools::getValue('identifier');
        $type = Tools::getValue('type');
        
        $code = $this->generateCode();
        $expiry = date('Y-m-d H:i:s', time() + Configuration::get('SMSAUTH_CODE_EXPIRY'));
        
        Db::getInstance()->insert('smsauth_codes', [
            'identifier' => pSQL($identifier),
            'type' => pSQL($type),
            'code' => pSQL($code),
            'date_add' => date('Y-m-d H:i:s'),
            'date_expire' => $expiry
        ]);
        
        // Send code via provider
        $this->sendCodeViaProvider($identifier, $type, $code);
        
        die(json_encode(['success' => true]));
    }
    
    private function ajaxVerifyCode()
    {
        $identifier = Tools::getValue('identifier');
        $code = Tools::getValue('code');
        
        $sql = 'SELECT * FROM '._DB_PREFIX_.'smsauth_codes 
                WHERE identifier = "'.pSQL($identifier).'" 
                AND code = "'.pSQL($code).'" 
                AND date_expire > NOW() 
                ORDER BY id_code DESC LIMIT 1';
        
        $result = Db::getInstance()->getRow($sql);
        
        if ($result) {
            // Delete used code
            Db::getInstance()->delete('smsauth_codes', 'id_code = '.(int)$result['id_code']);
            
            // Login user
            if ($result['type'] === 'email') {
                $customer = Customer::getCustomersByEmail($identifier);
                if ($customer && isset($customer[0])) {
                    $this->context->customer = new Customer($customer[0]['id_customer']);
                    $this->context->customer->logged = 1;
                    $this->context->cookie->id_customer = $customer[0]['id_customer'];
                    $this->context->cookie->customer_lastname = $this->context->customer->lastname;
                    $this->context->cookie->customer_firstname = $this->context->customer->firstname;
                    $this->context->cookie->logged = 1;
                    $this->context->cookie->passwd = $this->context->customer->passwd;
                    $this->context->cookie->email = $this->context->customer->email;
                    $this->context->cookie->write();
                }
            }
            
            die(json_encode(['success' => true]));
        }
        
        die(json_encode(['success' => false, 'message' => $this->l('Invalid code')]));
    }
    
    private function ajaxRegister()
    {
        $identifier = Tools::getValue('identifier');
        $type = Tools::getValue('type');
        $firstname = Tools::getValue('firstname');
        $lastname = Tools::getValue('lastname');
        
        $customer = new Customer();
        $customer->firstname = $firstname;
        $customer->lastname = $lastname;
        
        if ($type === 'email') {
            $customer->email = $identifier;
        } else {
            $customer->email = $identifier.'@temp.com';
            $customer->phone_mobile = $identifier;
        }
        
        $customer->passwd = Tools::encrypt(Tools::passwdGen());
        $customer->is_guest = 0;
        $customer->id_default_group = Configuration::get('PS_CUSTOMER_GROUP');
        
        if ($customer->add()) {
            $this->sendRegistrationEmail($customer);
            die(json_encode(['success' => true]));
        }
        
        die(json_encode(['success' => false]));
    }
    
    private function generateCode()
    {
        $length = Configuration::get('SMSAUTH_CODE_LENGTH');
        return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }
    
    private function sendCodeViaProvider($identifier, $type, $code)
    {
        // Implementation for sending via active provider
        return true;
    }
    
    private function sendRegistrationEmail($customer)
    {
        // Send welcome email
        return true;
    }
}