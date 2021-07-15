<?php if ($tpl['agents']) { ?>
    <div class="row" style="width:75%;margin:0 auto;">
    <?php foreach ($tpl['agents'] as $agent) { ?>        
        <div class="col-lg-4 col-md-6 col-sm-6 col-xs-12;" style="margin-bottom:50px;height:150px;">
            <div class="agent_name"><?php echo $agent['name']; ?> </div>
            <div><?php echo $agent['phone']; ?> </div>
            <div><?php echo $agent['email']; ?> </div>

            <?php if ($agent['address']) { ?>
                <div class="address_label">Address:</div>
                <div><?php echo preg_replace("/\n/","<br/>",$agent['address']); ?> </div>
            <?php } ?>
        </div>        
    <?php } ?>
    </div>
<?php } else { ?>
    <div class="no_agents">
        No agents found, please try with another district.
    </div>
<?php } ?>