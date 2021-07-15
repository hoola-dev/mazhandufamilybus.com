<?php
$front_messages = __('front_messages', true, false);
switch ($tpl['arr']['payment_method'])
{
    case 'cgrate':
        ?>
		<div class="bsSystemMessage">            
            <?php 
            if ($tpl['params']['success']) {
                $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
                $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
                echo $system_msg;
                echo "<br><br>".preg_replace("/\n/","<br>",$tpl['params']['message']); 
            } else {
                $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $tpl['params']['message'].'<br>[STAG]Start over[ETAG]');
                $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
                echo $system_msg;
            }
            ?>
        </div>
		<?php
        $_SESSION['wallet_message'] = '';
        break;
    case 'wallet':
        ?>
		<div class="bsSystemMessage">            
            <?php 
            if ($tpl['params']['success']) {
                $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
                $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
                echo $system_msg; 
                if (isset($_SESSION['wallet_message'])) { 
                    echo "<br><br>".preg_replace("/\n/","<br>",$_SESSION['wallet_message']);
                }
            } else {
                $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $tpl['params']['message'].'<br>[STAG]Start over[ETAG]');
                $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
                echo $system_msg;
            }
            ?>
        </div>
		<?php
        $_SESSION['wallet_message'] = '';
        break;
	case 'paypal':
		?><div class="bsSystemMessage"><?php echo $front_messages[1]; ?></div><?php
		if (pjObject::getPlugin('pjPaypal') !== NULL)
		{
			$controller->requestAction(array('controller' => 'pjPaypal', 'action' => 'pjActionForm', 'params' => $tpl['params']));
		}
        break;
    case '2checkout':
        ?><div class="bsSystemMessage">Your booking is saved. Redirecting to 2Checkout...</div><?php
        $controller->requestAction(array('controller' => 'pj2Checkout', 'action' => 'pjAction2CheckoutForm', 'params' => $tpl['params']));
        break;
    case 'flutterwave':
        ?><div class="bsSystemMessage">Your booking is saved. Redirecting to Flutterwave...</div><?php
        $controller->requestAction(array('controller' => 'pjFlutterwave', 'action' => 'pjActionFlutterwaveForm', 'params' => $tpl['params']));
        break;
    case 'pesapal':
        ?>
        <div class="bsSystemMessage">Your booking is saved. Redirecting to Pesapal...</div>
        <iframe src="<?php echo $tpl['params']['iframe_src'];?>" width="100%" height="700px"  scrolling="no" frameBorder="0">
        <p>Browser unable to load iFrame</p>
        </iframe>
        <?php
        break;
	case 'authorize':
		?><div class="bsSystemMessage"><?php echo $front_messages[2]; ?></div><?php
		if (pjObject::getPlugin('pjAuthorize') !== NULL)
		{
			$controller->requestAction(array('controller' => 'pjAuthorize', 'action' => 'pjActionForm', 'params' => $tpl['params']));
		}
		break;
	case 'bank':
		?>
		<div class="bsSystemMessage">
			<?php
			$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
			$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
			echo $system_msg; 
			?>
			<br /><br />
			<?php echo pjSanitize::html(nl2br($tpl['option_arr']['o_bank_account'])); ?>
		</div>
		<?php
        break;
    case 'cash':
        ?>
		<div class="bsSystemMessage">            
            <?php 
            $system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
            $system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
            echo $system_msg; 
            if (isset($_SESSION['cash_message'])) { 
                echo "<br><br>".preg_replace("/\n/","<br>",$_SESSION['cash_message']);
            }
            ?>
        </div>
		<?php
        $_SESSION['cash_message'] = '';
        break;
	case 'creditcard':
	default:
		?>
		<div class="bsSystemMessage">
			<?php
			$system_msg = str_replace("[STAG]", "<a href='#' class='bsStartOver'>", $front_messages[3]);
			$system_msg = str_replace("[ETAG]", "</a>", $system_msg); 
			echo $system_msg; 
			?>
		</div>
		<?php
}

?>