{extends file='page.tpl'}

{block name='page_title'}
    {l s='Login / Register' mod='smsemailauth'}
{/block}

{block name='page_content'}
    <div id="sms-email-auth" class="auth-container">
        <div class="auth-form">
            <div class="auth-type-selector">
                <button class="btn btn-primary" data-type="sms">{l s='SMS' mod='smsemailauth'}</button>
                <button class="btn btn-secondary" data-type="email">{l s='Email' mod='smsemailauth'}</button>
            </div>
            
            <div id="step-identifier" class="auth-step">
                <div class="sms-input" style="display:none;">
                    <select id="country-code" class="form-control">
                        {foreach from=$countries item=country}
                            <option value="{$country.call_prefix}">+{$country.call_prefix} {$country.name}</option>
                        {/foreach}
                    </select>
                    <input type="tel" id="phone-number" class="form-control" placeholder="{l s='Phone Number' mod='smsemailauth'}">
                </div>
                
                <div class="email-input">
                    <input type="email" id="email-address" class="form-control" placeholder="{l s='Email Address' mod='smsemailauth'}">
                </div>
                
                <button id="check-user" class="btn btn-primary btn-block">{l s='Continue' mod='smsemailauth'}</button>
            </div>
            
            <div id="step-register" class="auth-step" style="display:none;">
                <h3>{l s='New User Registration' mod='smsemailauth'}</h3>
                <input type="text" id="firstname" class="form-control" placeholder="{l s='First Name' mod='smsemailauth'}">
                <input type="text" id="lastname" class="form-control" placeholder="{l s='Last Name' mod='smsemailauth'}">
                <button id="register-user" class="btn btn-primary btn-block">{l s='Register & Send Code' mod='smsemailauth'}</button>
            </div>
            
            <div id="step-verify" class="auth-step" style="display:none;">
                <h3>{l s='Enter Verification Code' mod='smsemailauth'}</h3>
                <input type="text" id="verification-code" class="form-control" placeholder="{l s='Code' mod='smsemailauth'}">
                <button id="verify-code" class="btn btn-primary btn-block">{l s='Verify' mod='smsemailauth'}</button>
                <div class="resend-container">
                    <span id="resend-timer"></span>
                    <button id="resend-code" class="btn btn-link" style="display:none;">{l s='Resend Code' mod='smsemailauth'}</button>
                </div>
            </div>
        </div>
    </div>
{/block}

{block name='javascript_bottom'}
    {$smarty.block.parent}
    <script src="{$module_dir}views/js/auth.js"></script>
{/block}