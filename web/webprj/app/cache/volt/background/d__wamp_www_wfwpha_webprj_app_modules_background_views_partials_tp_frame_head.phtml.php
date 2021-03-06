
<!-- header section start-->
<div class="header-section">
    <!--toggle button start-->
    <a class="toggle-btn"><i class="fa fa-bars"></i></a>
    <!--toggle button end-->
    <?= $this->partial('partials/tp_page_nav') ?>
    <!--notification menu start -->
    <div class="menu-right">

        <ul class="notification-menu">
            <li>
                <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <?= $this->tag->image(['public/static/background/image/avatar-mini.jpg']) ?>
                    <?= $ht_user_name ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                    <!--<li><a href="#"><i class="fa fa-user"></i> 个人中心</a></li>-->
                    <li><a href='<?= $this->url->get('admin/session/logout') ?>'><i class="fa fa-sign-out"></i>退出</a></li>
                    <li><a href='<?= $this->url->get('admin/person') ?>'><i class="fa fa-user"></i>个人中心</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <!--notification menu end -->
</div>
<!-- header section end-->
