<?php

$installer = $this;
$installer->startSetup();
$table     = $this->getTable('core/email_template');

//FRIENDLY TEMPLATE
$title2    = 'Abandoned Cart: Friendly';
$subject2  = 'Thanks for stopping by {{var store_name}}';
$template2 = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <tr>
        <td style="background-color: #EEE; padding:30px 37px 50px;" align="right">
                <table style="box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.26); background-color: #FFF;" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding: 20px 35px 35px; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0px; width: 100%; border-bottom: 1px solid rgb(11, 199, 255);">
                                <tr>
                                    <td align="right" valign="top">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="bottom">        
                                        <a href="{{var store_url}}"><img style="display: block; margin: 15px 0; text-align: left; max-width: 100%; padding: 0" src="{{var logo_url}}" alt="{{var logo_alt}}" /></a>
                                    </td>
                                </tr>
                            </table>
                            <h3 style="margin: 30px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Hi {{var customer_name}},</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">This is a friendly reminder that you still have items in your shopping cart (click to see your cart):</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">{{var products}}</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Because our products sell out fast, we encourage you to place an order soon.</h3>
                            {{depend coupon}}
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">
                            Do it now and get {{var discount_amount}} off your purchase <br /> 
                            The coupon expires in {{var coupon_days}} day(s): <strong>{{var coupon}}</strong><br/>
                            </h3>
                            {{/depend}}
                            <p style="text-align: center;padding-top: 20px;"><a href="{{var recover_url}}" style="display: inline-block;color: white;background: #71bc37;border: solid #71bc37;border-width: 10px 20px 8px;font-weight: bold;border-radius: 4px;">RESTORE CART</a></p>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Happy shopping,<br />
                            The {{var store_name}} team</h3>
                        </td>
                    </tr>
                </table>
        </td>
    </tr>
    <tr style="height: 30px">
        <td align="center" style="padding-top: 35px;">
            If you no longer wish to receive emails from us, click to <a style=" color: #888;text-decoration: none;font-weight: bold;" href="{{var unsubscribe_url}}">Unsubscribe</a>.
        </td>
    </tr>
</table>
</body>
</html>
';

//INFORMAL TEMPLATE
$title3    = 'Abandoned Cart: Informal';
$subject3  = 'Good reason to come back to {{var store_name}}';
$template3 = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <tr>
        <td style="background-color: #EEE; padding:30px 37px 50px;" align="right">
                <table style="box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.26); background-color: #FFF;" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding: 20px 35px 35px; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0px; width: 100%; border-bottom: 1px solid rgb(11, 199, 255);">
                                <tr>
                                    <td align="right" valign="top">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="bottom">        
                                        <a href="{{var store_url}}"><img style="display: block; margin: 15px 0; text-align: left; max-width: 100%; padding: 0" src="{{var logo_url}}" alt="{{var logo_alt}}" /></a>
                                    </td>
                                </tr>
                            </table>
                            <h3 style="margin: 30px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Hi {{var customer_name}},</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">It was awesome seeing you at {{var store_name}}!</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">We’ve spotted that the following item(s) are still sitting in your cart:</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">{{var products}}</h3>
                            {{depend coupon}}
                            
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">
                            There is a {{var discount_amount}} discount available for such items: <br />
                            <strong>{{var coupon}}</strong><br/>
                            Hurry up - the offer lasts for {{var coupon_days}} day(s) only!
                            </h3>
                            
                            {{/depend}}
                            
                            <p style="text-align: center;padding-top: 20px;"><a href="{{var recover_url}}" style="display: inline-block;color: white;background: #71bc37;border: solid #71bc37;border-width: 10px 20px 8px;font-weight: bold;border-radius: 4px;">RESTORE CART</a></p>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Cheers,<br />
                            The {{var store_name}} team</h3>
                        </td>
                    </tr>
                </table>
        </td>
    </tr>
    <tr style="height: 30px">
        <td align="center" style="padding-top: 35px;">
            If you no longer wish to receive emails from us, click to <a style=" color: #888;text-decoration: none;font-weight: bold;" href="{{var unsubscribe_url}}">Unsubscribe</a>.
        </td>
    </tr>
</table>
</body>
</html>
';

//MINIMALIST TEMPLATE
$title4     = 'Abandoned Cart: Minimalist';
$subject4   = 'Abandoned cart at {{var store_name}}';
$template4  = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <tr>
        <td style="background-color: #EEE; padding:30px 37px 50px;" align="right">
                <table style="box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.26); background-color: #FFF;" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding: 20px 35px 35px; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0px; width: 100%; border-bottom: 1px solid rgb(11, 199, 255);">
                                <tr>
                                    <td align="right" valign="top">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="bottom">        
                                        <a href="{{var store_url}}"><img style="display: block; margin: 15px 0; text-align: left; max-width: 100%; padding: 0" src="{{var logo_url}}" alt="{{var logo_alt}}" /></a>
                                    </td>
                                </tr>
                            </table>
                            <h3 style="margin: 30px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Hi {{var customer_name}},</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">You still have item(s) in your shopping cart at {{var store_name}}!</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">{{var products}}</h3>
                            {{depend coupon}}
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">
                            Get {{var discount_amount}} off this purchase during the next {{var coupon_days}} day(s): <strong>{{var coupon}}</strong><br/>
                            </h3>
                            
                            {{/depend}}
                            
                            <p style="text-align: center;padding-top: 20px;"><a href="{{var recover_url}}" style="display: inline-block;color: white;background: #71bc37;border: solid #71bc37;border-width: 10px 20px 8px;font-weight: bold;border-radius: 4px;">RESTORE CART</a></p>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Best regards,<br />
                            The {{var store_name}} team</h3>
                        </td>
                    </tr>
                </table>
        </td>
    </tr>
    <tr style="height: 30px">
        <td align="center" style="padding-top: 35px;">
            If you no longer wish to receive emails from us, click to <a style=" color: #888;text-decoration: none;font-weight: bold;" href="{{var unsubscribe_url}}">Unsubscribe</a>.
        </td>
    </tr>
</table>
</body>
</html>
';

//LAST CALL TEMPLATE
$title5    = 'Abandoned Cart: Last call';
$subject5  = 'Your cart at {{var store_name}} is about to expire';
$template5 = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width"/>
</head>
<body>
<table border="0" cellpadding="0" cellspacing="0" style="margin:0;">
    <tr>
        <td style="background-color: #EEE; padding:30px 37px 50px;" align="right">
                <table style="box-shadow: 0px 2px 10px rgba(0, 0, 0, 0.26); background-color: #FFF;" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center" style="padding: 20px 35px 35px; text-align: left;">
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0px; width: 100%; border-bottom: 1px solid rgb(11, 199, 255);">
                                <tr>
                                    <td align="right" valign="top">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" valign="bottom">        
                                        <a href="{{var store_url}}"><img style="display: block; margin: 15px 0; text-align: left; max-width: 100%; padding: 0" src="{{var logo_url}}" alt="{{var logo_alt}}" /></a>
                                    </td>
                                </tr>
                            </table>
                            <h3 style="margin: 30px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Hi {{var customer_name}},</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">You shopping cart at {{var store_name}} expires in {{var coupon_days}} day(s).</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Don’t miss your chance to get the following items:</h3>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">{{var products}}</h3>
                            {{depend coupon}}
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">
                            Order now and get {{var discount_amount}} off your purchase:
                            <strong>{{var coupon}}</strong><br/>
                            </h3>
                            
                            {{/depend}}
                            
                            <p style="text-align: center;padding-top: 20px;"><a href="{{var recover_url}}" style="display: inline-block;color: white;background: #71bc37;border: solid #71bc37;border-width: 10px 20px 8px;font-weight: bold;border-radius: 4px;">VALIDATE NOW</a></p>
                            <h3 style="margin: 15px 0px 0px; color: #696060; font: 14px Helvetica, sans-serif; -webkit-text-size-adjust:none; clear: both;">Best regards,<br />
                            The {{var store_name}} team</h3>
                        </td>
                    </tr>
                </table>
        </td>
    </tr>
    <tr style="height: 30px">
        <td align="center" style="padding-top: 35px;">
            If you no longer wish to receive emails from us, click to <a style=" color: #888;text-decoration: none;font-weight: bold;" href="{{var unsubscribe_url}}">Unsubscribe</a>.
        </td>
    </tr>
</table>
</body>
</html>
';

$installer->run("

INSERT INTO `$table` SET 
    `template_code`           = '$title2', 
    `template_text`           = '$template2',
    `template_styles`         =  null,
    `template_subject`        = '$subject2',
    `template_sender_name`    =  null,
    `template_sender_email`   =  null,
    `template_type` = 2,
    `orig_template_code` = 'catalog_adjcartalert_template', 
    `orig_template_variables` = null
");

$installer->run("

INSERT INTO `$table` SET 
    `template_code`           = '$title3', 
    `template_text`           = '$template3',
    `template_styles`         =  null,
    `template_subject`        = '$subject3',
    `template_sender_name`    =  null,
    `template_sender_email`   =  null,
    `template_type`           =  2,
    `orig_template_code`      = 'catalog_adjcartalert_template', 
    `orig_template_variables` =  null
");

$installer->run("

INSERT INTO `$table` SET 
    `template_code`           = '$title4', 
    `template_text`           = '$template4',
    `template_styles`         =  null,
    `template_subject`        = '$subject4',
    `template_sender_name`    =  null,
    `template_sender_email`   =  null,
    `template_type`           =  2,
    `orig_template_code`      = 'catalog_adjcartalert_template', 
    `orig_template_variables` =  null
");


$installer->run("

INSERT INTO `$table` SET 
    `template_code`           = '$title5', 
    `template_text`           = '$template5',
    `template_styles`         = null,
    `template_subject`        = '$subject5',
    `template_sender_name`    = null,
    `template_sender_email`   = null,
    `template_type`           = 2,
    `orig_template_code`      = 'catalog_adjcartalert_template', 
    `orig_template_variables` = null
");

$installer->endSetup(); 
