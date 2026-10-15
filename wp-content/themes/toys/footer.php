        </div>
        <div class="clearfix"></div>
    </div>
</div>
<div class="footer">
    <div class="container-fluid">
        <div class="col-sm-10">
            <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Footer Copytight Area')) : ?>
            <?php endif; ?>
        </div>
        <div class="col-sm-2 pull-right">
            <!-- <div class="text-right"><span>Designed &amp; Developed by: <a class="disabled" href="#">Web Matrix Infotech</a></span></div> -->
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="clearfix"></div>
</div>
<?php wp_footer(); ?>
</body>
</html>