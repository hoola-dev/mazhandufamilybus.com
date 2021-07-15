<form method="POST" name="<?php echo $tpl['arr']['name']; ?>" id="<?php echo $tpl['arr']['name']; ?>" action="https://checkout.flutterwave.com/v3/hosted/pay">
  <input type="hidden" name="public_key" value="<?php echo $tpl['arr']['public_key']; ?>" />
  <input type="hidden" name="customer[email]" value="<?php echo $tpl['arr']['email']; ?>" />
  <input type="hidden" name="customer[phone_number]" value="<?php echo $tpl['arr']['phone']; ?>" />
  <input type="hidden" name="customer[name]" value="<?php echo $tpl['arr']['customer_name']; ?>" />
  <input type="hidden" name="tx_ref" value="<?php echo $tpl['arr']['tx_ref']; ?>" />
  <input type="hidden" name="amount" value="<?php echo $tpl['arr']['amount']; ?>" />
  <input type="hidden" name="currency" value="<?php echo $tpl['arr']['currency']; ?>" />
  <input type="hidden" name="meta[token]" value="<?php echo $tpl['arr']['meta_token']; ?>" />
  <input type="hidden" name="redirect_url" value="<?php echo $tpl['arr']['redirect_url']; ?>" />  
  <?php if ($tpl['arr']['sub_account']) { ?>
  <input type="hidden" name="subaccounts[][id]" value="<?php echo $tpl['arr']['sub_account']; ?>" />  
  <?php } ?>
</form>
