<?php
require $content_tpl;
$content = ob_get_contents();
ob_end_clean();

$content = preg_replace('/\r\n|\n|\t/', '', $content);
$content = str_replace("'", "\"", $content);
//echo "document.writeln('$content');"
?>
$('#load_booking_container').append('<?php echo $content; ?>');