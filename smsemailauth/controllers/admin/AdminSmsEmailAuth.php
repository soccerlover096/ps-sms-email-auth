<?php
class AdminSmsEmailAuthController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'smsauth_providers';
        $this->className = 'SmsAuthProvider';
        $this->identifier = 'id_provider';
        
        parent::__construct();
        
        $this->fields_list = [
            'id_provider' => ['title' => 'ID', 'width' => 50],
            'name' => ['title' => $this->l('Provider Name')],
            'type' => ['title' => $this->l('Type')],
            'active' => ['title' => $this->l('Active'), 'active' => 'status', 'type' => 'bool']
        ];
        
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?')
            ]
        ];
    }
    
    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Provider Configuration'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Provider Name'),
                    'name' => 'name',
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Type'),
                    'name' => 'type',
                    'options' => [
                        'query' => [
                            ['id' => 'sms', 'name' => 'SMS'],
                            ['id' => 'email', 'name' => 'Email']
                        ],
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    'type' => 'textarea',
                    'label' => $this->l('Settings (JSON)'),
                    'name' => 'settings',
                    'desc' => $this->l('Provider-specific settings in JSON format')
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Active'),
                    'name' => 'active',
                    'values' => [
                        ['id' => 'active_on', 'value' => 1, 'label' => $this->l('Yes')],
                        ['id' => 'active_off', 'value' => 0, 'label' => $this->l('No')]
                    ]
                ]
            ],
            'submit' => [
                'title' => $this->l('Save')
            ]
        ];
        
        return parent::renderForm();
    }
}