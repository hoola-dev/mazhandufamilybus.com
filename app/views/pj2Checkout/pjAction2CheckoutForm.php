<?php
$url = PJ_TEST_MODE ? 'https://sandbox.2checkout.com/checkout/purchase' : 'https://2checkout.com/checkout/purchase';
?>
<form action="<?php echo $url; ?>" method="post" style="display: inline" name="<?php echo $tpl['arr']['name']; ?>" id="<?php echo $tpl['arr']['id']; ?>" target="<?php echo $tpl['arr']['target']; ?>">    
<input type='hidden' name='sid' value='<?php echo $tpl['arr']['sid']; ?>'>
<input type='hidden' name='mode' value='2CO' >
<input type='hidden' name='li_0_type' value='product' >
<input type='hidden' name='li_0_name' value='<?php echo htmlspecialchars($tpl['arr']['li_0_name']); ?>' >
<input type='hidden' name='li_0_product_id' value='<?php echo $tpl['arr']['li_0_product_id']; ?>' >
<input type='hidden' name='li_0__description' value='<?php echo htmlspecialchars($tpl['arr']['li_0_name']); ?>' >
<input type='hidden' name='li_0_price' value='<?php echo number_format($tpl['arr']['li_0_price'], 2, '.', ''); ?>' >
<input type='hidden' name='li_0_quantity' value='1' >
<input type='hidden' name='li_0_tangible' value='N' >
<input type='hidden' name='currency_code' value='<?php echo $tpl['arr']['currency_code']; ?>' >
<input type='hidden' name='first_name' value='<?php echo $tpl['arr']['first_name']; ?>' >
<input type='hidden' name='last_name' value='<?php echo $tpl['arr']['last_name']; ?>' >
<input type='hidden' name='street_address' value='<?php echo $tpl['arr']['street_address']; ?>' >
<input type='hidden' name='city' value='<?php echo $tpl['arr']['city']; ?>' >
<input type='hidden' name='state' value='<?php echo $tpl['arr']['state']; ?>' >
<input type='hidden' name='zip' value='<?php echo $tpl['arr']['zip']; ?>' >
<input type='hidden' name='country' value='<?php echo $tpl['arr']['country']; ?>' >
<input type='hidden' name='email' value='<?php echo $tpl['arr']['email']; ?>' >
<input type='hidden' name='phone' value='<?php echo $tpl['arr']['phone']; ?>' >
<input type='hidden' name='demo' value='Y' />
<input type='hidden' name='x_receipt_link_url' value='<?php echo PJ_INSTALL_URL; ?>index.php?controller=pjFrontEnd&action=pjActionConfirm2Checkout' >
<!-- <input name='submit' type='submit' value='Checkout'> -->
</form>
