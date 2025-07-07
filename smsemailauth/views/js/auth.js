$(document).ready(function() {
    var authType = 'email';
    var identifier = '';
    var resendDelay = parseInt($('#sms-email-auth').data('resend-delay') || 60);
    var resendTimer;
    
    // Type selector
    $('.auth-type-selector button').click(function() {
        authType = $(this).data('type');
        $('.auth-type-selector button').removeClass('btn-primary btn-secondary');
        $(this).addClass('btn-primary').siblings().addClass('btn-secondary');
        
        if (authType === 'sms') {
            $('.sms-input').show();
            $('.email-input').hide();
        } else {
            $('.sms-input').hide();
            $('.email-input').show();
        }
    });
    
    // Check user
    $('#check-user').click(function() {
        if (authType === 'sms') {
            identifier = $('#country-code').val() + $('#phone-number').val();
        } else {
            identifier = $('#email-address').val();
        }
        
        if (!identifier) {
            alert('Please enter your ' + authType);
            return;
        }
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax: true,
                action: 'checkUser',
                identifier: identifier,
                type: authType
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.exists) {
                    sendCode();
                } else {
                    $('#step-identifier').hide();
                    $('#step-register').show();
                }
            }
        });
    });
    
    // Register user
    $('#register-user').click(function() {
        var firstname = $('#firstname').val();
        var lastname = $('#lastname').val();
        
        if (!firstname || !lastname) {
            alert('Please fill all fields');
            return;
        }
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax: true,
                action: 'register',
                identifier: identifier,
                type: authType,
                firstname: firstname,
                lastname: lastname
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    sendCode();
                }
            }
        });
    });
    
    // Send code
    function sendCode() {
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax: true,
                action: 'sendCode',
                identifier: identifier,
                type: authType
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    $('#step-identifier, #step-register').hide();
                    $('#step-verify').show();
                    startResendTimer();
                }
            }
        });
    }
    
    // Verify code
    $('#verify-code').click(function() {
        var code = $('#verification-code').val();
        
        if (!code) {
            alert('Please enter the code');
            return;
        }
        
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: {
                ajax: true,
                action: 'verifyCode',
                identifier: identifier,
                code: code
            },
            success: function(response) {
                var data = JSON.parse(response);
                if (data.success) {
                    window.location.href = 'index.php?controller=my-account';
                } else {
                    alert(data.message || 'Invalid code');
                }
            }
        });
    });
    
    // Resend code
    $('#resend-code').click(function() {
        sendCode();
    });
    
    // Timer function
    function startResendTimer() {
        var timeLeft = resendDelay;
        $('#resend-code').hide();
        $('#resend-timer').show();
        
        resendTimer = setInterval(function() {
            timeLeft--;
            $('#resend-timer').text('Resend in ' + timeLeft + 's');
            
            if (timeLeft <= 0) {
                clearInterval(resendTimer);
                $('#resend-timer').hide();
                $('#resend-code').show();
            }
        }, 1000);
    }
});