# PrestaShop SMS/Email Authentication Module

A comprehensive authentication module for PrestaShop that enables login and registration via SMS and Email verification.

## Features

- **Dual Authentication**: Support for both SMS and Email verification
- **Smart User Detection**: Automatically detects existing users and new registrations
- **International Support**: Full support for all countries and phone number formats
- **Customizable Settings**:
  - Verification code length
  - Code expiry time
  - Resend delay configuration
  - Custom registration fields
- **Multiple SMS Providers**:
  - Twilio
  - Kavenegar
  - Easy to add more providers
- **Admin Panel**: Complete provider management interface
- **Security Features**:
  - Rate limiting
  - Code expiration
  - Attempt tracking

## Installation

1. Download the module
2. Upload to your PrestaShop `/modules/` directory
3. Install from PrestaShop admin panel
4. Configure your SMS/Email providers
5. Enable the module

## Configuration

### Basic Settings
- **Enable Module**: Turn the module on/off
- **Code Length**: Set verification code length (4-8 digits)
- **Code Expiry**: Set how long codes remain valid (in seconds)
- **Resend Delay**: Minimum time between code resends

### Provider Setup

#### Twilio
```json
{
  "account_sid": "your_account_sid",
  "auth_token": "your_auth_token",
  "from_number": "+1234567890"
}
```

#### Kavenegar
```json
{
  "api_key": "your_api_key",
  "sender": "your_sender_number"
}
```

## Usage

1. Users can choose between SMS or Email authentication
2. For SMS: Select country code and enter phone number
3. For Email: Enter email address
4. New users will be prompted for registration details
5. Verification code is sent to the chosen method
6. Enter code to complete authentication

## Development

### Adding New SMS Providers

1. Extend the `sendMessage()` method in `SmsAuthProvider.php`
2. Add your provider's API implementation
3. Configure provider settings in admin panel

### Customizing Fields

Modify `controllers/front/auth.php` to add custom registration fields:

```php
$customer->custom_field = Tools::getValue('custom_field');
```

## Requirements

- PrestaShop 1.7+
- PHP 7.2+
- cURL extension
- MySQL 5.6+

## License

This module is licensed under the MIT License.

## Support

For support and feature requests, please create an issue on GitHub.