<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>后台管理中心</title>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/bootstrap/3.3.6/bootstrap.min.css', false); ?>
    <?= $this->tag->stylesheetLink('/WFWPHA/libs/js3party/bootstrap/fonts/css/font-awesome.min.css', false); ?>
    <?= $this->tag->stylesheetLink('public/static/background/css/session/login.css') ?>
</head>
<body>
<div class="logo_banner" style="position:absolute; overflow:hidden;">
    <?php echo $this->tag->image("public/static/background/image/login/loginbg.png"); ?>
</div>
<div class="logo_banner" style="position:absolute; overflow:hidden;">
    <?php echo $this->tag->image("public/static/background/image/login/loginbg.png"); ?>
</div>
<div class="container">
    <div class="con_shade">
        <div class="con_bg"></div>
        <?= $this->tag->form(['method' => 'post']) ?>
        <?= $form->render('csrf') ?>
        <div class="form-signin">
            <h2 class="form-signin-heading"><?= $title ?></h2>
            <div class="form-group">
                <?= $form->label('account', ['class' => 'usernameimg']) ?>
                <?= $form->render('account', ['class' => 'form-control', 'autocomplete' => 'off']) ?>
            </div>

            <div class="form-group">
                <?= $form->label('password', ['class' => 'usernameimg']) ?>
                <?= $form->render('password', ['class' => 'form-control']) ?>
            </div>

            <span class="ti_shi text-center"><?= $this->getContent() ?></span>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <?= $form->render('remember') ?> 保持登录
                    </label>
                </div>
            </div>

            <?= $form->render('go', ['class' => 'btn btn-lg btn-primary btn-block']) ?>
            <!--            <button class="btn btn-lg btn-primary btn-block"">登　录</button>-->
        </div>
        <?= $this->tag->endform() ?>
    </div>
</div>
<?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/jquery/jquery-2.2.4.min.js', false); ?>
<?= $this->tag->javascriptInclude('/WFWPHA/libs/js3party/bootstrap/3.3.6/bootstrap.min.js', false); ?>
</body>
</html>
