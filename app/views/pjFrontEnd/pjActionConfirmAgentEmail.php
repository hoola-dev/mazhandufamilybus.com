<script language="javascript">
alert("<?php echo $tpl['params']['message']; ?>");
<?php
if ($tpl['params']['success'] == 1) {
?>
    location.href= "<?php echo PJ_INSTALL_URL.'index.php?controller=pjAdmin&action=pjActionLogin'; ?>";
<?php
} else {
?>
     location.href= "<?php echo PJ_INSTALL_URL; ?>";
<?php
}
?>
</script>