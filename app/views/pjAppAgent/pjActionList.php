<!--agents-->
<section id="agents" style="padding-top:0px;min-height:800px;">
    <div class="container">
        <div class="title">
        <!-- <h2 class="flex fadeInUp animated animated" style="visibility: visible; animation-name: fadeInUp;">Mazhandu Family Bus Services</h2>
            <h2 class="flex fadeInUp animated animated" style="visibility: visible; animation-name: fadeInUp;">Our Agents</h2> -->
            <p class="description flex fadeInUp animated animated" style="visibility: visible; animation-name: fadeInUp;">
                <span class="price" style="margin-left: 0px; margin-top: 0px; padding-top: 0px;font-size:18px;">
                    <b>Search by District</b>
                </span>
            </p>
            <p class="description flex fadeInUp animated animated" style="visibility: visible; animation-name: fadeInUp;width:250px;">
                <span style="margin-left: 0px; margin-top: 0px; padding-top: 0px;font-size:18px;">                        
                    <select name="search_district" id="search_district" class="form-control" value="" style="width:250px;cursor:pointer;" onchange="javascript:search_agents();">
                    <?php foreach ($tpl['districts'] as $district) { ?>
                        <option value="<?php echo $district['dt_id']; ?>"<?php if ($district['dt_id'] == $tpl['district_id']) { echo "selected"; } ?>><?php echo $district['dt_name']; ?></option>
                    <?php } ?>
                    </select>
                </span>
            </p>        
        </div>
        <!--end title-->
        <div id="list_agents" style="clear:both;">
            <?php if ($tpl['agents']) { ?>
                <div class="row" style="width:75%;margin:0 auto;">
                <?php foreach ($tpl['agents'] as $agent) { ?>        
                    <div class="col-lg-4 col-md-4 col-sm-3 col-xs-12;" style="margin-bottom:50px;height:150px;">
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
        </div>
        <div style="margin-bottom:20px;clear:both;height:auto;overflow:hidden;">
                <p class="description flex fadeInUp animated animated" style="visibility: visible; animation-name: fadeInUp;width:200px;">
                    <span style="margin-left: 0px; margin-top: 0px; padding-top: 0px;font-size:18px;">
                        <a href="<?php echo PJ_INSTALL_URL.'index.php?controller=pjAppAgent&action=pjActionList&district='.$tpl['district_id']; ?>" target="_blank" id="a_agent">
                            <button type="button" class="btn btn-primary pull-right" style="width:185px;margin-right:10px;" style="">List All Agents</button>
                        </a>
                    </span>
                </p>  
        </div>
    </div>
</section>
<!--agents-->