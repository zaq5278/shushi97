<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>系统管理后台</title>
    
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/bootstrap/3.3.6/bootstrap.min.css', false); ?>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/bootstrap/fonts/css/font-awesome.min.css', false); ?>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/jquery-ui/1.11.4/css/jquery-ui.min.css', false); ?>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/AdminEx/css/style.css', false); ?>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/AdminEx/css/style-responsive.css', false); ?>

    <?= '<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->' ?>
    <?= '<!--[if lt IE 9]>' ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/AdminEx/js/html5shiv.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/AdminEx/js/respond.min.js', false); ?>
    <?= '<![endif]-->' ?>
    
    <?= $this->assets->outputCss() ?>
    
<script>
    var _HOME_ROOT_ ="<?php echo $home_root ?>";
</script>
    
 </head>

<body class="sticky-header">
    <section>
        <!-- left side start-->
        <?= $this->partial('partials/tp_frame_menu') ?>
        <!-- left side end-->
        <!-- main content start-->
        <div class="main-content" >
            <!-- frame head start-->
            <?= $this->partial('partials/tp_frame_head') ?>
            <!-- frame head end-->

            <!--body wrapper start-->
            <!-- partial('partials/tp_page_nav') -->
            <div class="wrapper">
                
                <?= $this->getContent() ?>
            </div>
            <!--body wrapper end-->
        </div>
        <!--main content end-->
    </section>

    <!--裁剪插件不支持-->
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/jquery/jquery-1.10.2.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/jquery-ui/1.11.4/js/jquery-ui.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/bootstrap/3.3.6/bootstrap.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/jquery-nicescroll/3.6.8/js/jquery.nicescroll.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/jquery-migrate/1.4.1/jquery-migrate.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/AdminEx/js/modernizr.min.js', false); ?>
    <?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/AdminEx/js/scripts.js', false); ?>
    <?= $this->assets->outputJs() ?>
</body>
</html>
