<?php
$sub_total = 0;

if(isset($tpl['price_arr']))
{
	$sub_total += $tpl['price_arr']['sub_total'];
}
if(isset($tpl['return_price_arr']))
{
	$sub_total += $tpl['return_price_arr']['sub_total'];
}
?>
<span class="bsTitle"><?php __('front_roundtrip_price');?>: <?php echo pjUtil::formatCurrencySign(number_format($sub_total, 2), $tpl['option_arr']['o_currency']);?></span>
