{if Configuration::get('SMSAUTH_ENABLED')}
<div class="sms-email-auth-link">
    <hr>
    <p class="text-center">
        <a href="{$link->getModuleLink('smsemailauth', 'auth')}" class="btn btn-primary">
            <i class="material-icons">phone</i>
            {l s='Login with SMS/Email' mod='smsemailauth'}
        </a>
    </p>
</div>
{/if}