<section id="news" class="news">
    <div class="container">
        <div class="title">
            <h2 class="flex fadeInUp    animated" style="visibility: visible; animation-name: fadeInUp;">Latest News</h2>          
        </div>
        <div class="row">             
            <div class="col-md-12">
                <div class="row">
                    <div id="nmContainer_4262" class="nmContainer"> 
                        <div class="nmNewsList">
                            <?php foreach ($tpl['news'] as $news) { ?>
                                <?php if ($news['nw_link']) { ?>
                                    <a href="<?php echo $news['nw_link']; ?>" target='_blank'>
                                <?php } ?>
                                <div class="nmNewsBox nmFullNews">          
                                    <div class="nmNewsDetail">
                                        <img class="nmNewsThumb" src="<?php echo PJ_INSTALL_URL.'app/uploads/news/'.$news['nw_image']; ?>">
                                        <div class="nmDescription">
                                            <label class="nmTitle"><?php echo $news['nw_title']; ?></label>
                                            <div class="nmPublishedDate">
                                                <span><?php echo date('F d',strtotime($news['nw_date'])); ?></span><br>
                                            </div>
                                            <p><?php echo preg_replace('/\n/','<br>',$news['nw_description']); ?></p>
                                        </div>
                                    </div>
                                </div>
                                <?php if ($news['nw_link']) { ?>
                                    </a>
                                <?php } ?>                                    
                            <?php } ?>
                        </div>
                        <div class="nmPaginator"></div>
                    </div>                    
                </div>
            </div>       
        </div> 
    </div>
</section>