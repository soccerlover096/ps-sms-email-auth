<?php
class SmsAuthProvider extends ObjectModel
{
    public $id_provider;
    public $name;
    public $type;
    public $settings;
    public $active;
    
    public static $definition = [
        'table' => 'smsauth_providers',
        'primary' => 'id_provider',
        'fields' => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 50],
            'type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 10],
            'settings' => ['type' => self::TYPE_STRING],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool']
        ]
    ];
    
    public function getSettings()
    {
        return json_decode($this->settings, true);
    }
    
    public function setSettings($settings)
    {
        $this->settings = json_encode($settings);
    }
    
    public function sendMessage($to, $message)
    {
        $settings = $this->getSettings();
        
        switch ($this->name) {
            case 'twilio':
                return $this->sendTwilio($to, $message, $settings);
            case 'kavenegar':
                return $this->sendKavenegar($to, $message, $settings);
            case 'smtp':
                return $this->sendSmtp($to, $message, $settings);
            default:
                return false;
        }
    }
    
    private function sendTwilio($to, $message, $settings)
    {
        // Twilio implementation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.twilio.com/2010-04-01/Accounts/'.$settings['account_sid'].'/Messages.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'To' => $to,
            'From' => $settings['from_number'],
            'Body' => $message
        ]));
        curl_setopt($ch, CURLOPT_USERPWD, $settings['account_sid'].':'.$settings['auth_token']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    private function sendKavenegar($to, $message, $settings)
    {
        // Kavenegar implementation
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.kavenegar.com/v1/'.$settings['api_key'].'/sms/send.json');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'receptor' => $to,
            'message' => $message,
            'sender' => $settings['sender']
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    
    private function sendSmtp($to, $message, $settings)
    {
        // Email implementation using PrestaShop Mail class
        return Mail::Send(
            Context::getContext()->language->id,
            'verification',
            'Verification Code',
            ['message' => $message],
            $to,
            null,
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_.'smsemailauth/mails/'
        );
    }
}