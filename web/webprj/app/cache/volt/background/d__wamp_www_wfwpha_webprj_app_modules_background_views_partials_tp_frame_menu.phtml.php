<!-- left side start-->
<div class="left-side sticky-left-side">
    <!--logo and iconic logo start-->
    <div class="logo">
        <a href="javascript:;"><?php echo $this->tag->image("public/static/background/image/logo.png"); ?></a>
    </div>

    <div class="logo-icon text-center">
        <a href="index.html"><?php echo $this->tag->image("public/static/background/image/logo_icon.png"); ?></a>
    </div>
    <!--logo and iconic logo end-->

    <div class="left-side-inner">
        <!--sidebar nav start-->
        <ul class="nav nav-pills nav-stacked custom-nav">
            <?php foreach ($menu_root as $mi_one_level) { ?>
                <?php if (isset($mi_one_level['sub_menu'])) { ?>
                <li class="<?= ((empty($mi_one_level['active']) ? (false) : ($mi_one_level['active'])) ? 'menu-list nav-active' : 'menu-list') ?>"><a href="#"><i class="<?= $mi_one_level['font_icon'] ?>"></i><span><?= $mi_one_level['name'] ?></span></a>
                    <ul class="sub-menu-list">
                        <?php $v16422204652iterator = $mi_one_level['sub_menu']; $v16422204652incr = 0; $v16422204652loop = new stdClass(); $v16422204652loop->self = &$v16422204652loop; $v16422204652loop->length = count($v16422204652iterator); $v16422204652loop->index = 1; $v16422204652loop->index0 = 1; $v16422204652loop->revindex = $v16422204652loop->length; $v16422204652loop->revindex0 = $v16422204652loop->length - 1; ?><?php foreach ($v16422204652iterator as $mi_two_level) { ?><?php $v16422204652loop->first = ($v16422204652incr == 0); $v16422204652loop->index = $v16422204652incr + 1; $v16422204652loop->index0 = $v16422204652incr; $v16422204652loop->revindex = $v16422204652loop->length - $v16422204652incr; $v16422204652loop->revindex0 = $v16422204652loop->length - ($v16422204652incr + 1); $v16422204652loop->last = ($v16422204652incr == ($v16422204652loop->length - 1)); ?>
                        <li <?= (empty($mi_two_level['active']) ? '' : 'class=active') ?>>
                        <a href="<?= (empty($mi_two_level['link_action']) ? '#' : $this->url->get($mi_two_level['link_action'])) ?>"><?= $mi_two_level['name'] ?></a>
                        </li>
                        <?php $v16422204652incr++; } ?>
                    </ul>
                <?php } else { ?>
                <li <?= (empty($mi_one_level['active']) ? '' : 'class=active') ?>>
                <a href="<?= (empty($mi_one_level['link_action']) ? '#' : $this->url->get($mi_one_level['link_action'])) ?>"><i class="<?= $mi_one_level['font_icon'] ?>"></i><span><?= $mi_one_level['name'] ?></span></a>
                </li>
                <?php } ?>
            <?php } ?>
        </ul>
        <!--sidebar nav end-->
    </div>
</div>
<!-- left side end-->
