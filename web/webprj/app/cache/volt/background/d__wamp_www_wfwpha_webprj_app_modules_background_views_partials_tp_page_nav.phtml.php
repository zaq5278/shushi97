
<!--导航菜单-->
<ol class="breadcrumb">
    <!--<?= json_encode($nav_menu) ?>-->
    <?php $v16422204651iterator = $nav_menu; $v16422204651incr = 0; $v16422204651loop = new stdClass(); $v16422204651loop->self = &$v16422204651loop; $v16422204651loop->length = count($v16422204651iterator); $v16422204651loop->index = 1; $v16422204651loop->index0 = 1; $v16422204651loop->revindex = $v16422204651loop->length; $v16422204651loop->revindex0 = $v16422204651loop->length - 1; ?><?php foreach ($v16422204651iterator as $menu) { ?><?php $v16422204651loop->first = ($v16422204651incr == 0); $v16422204651loop->index = $v16422204651incr + 1; $v16422204651loop->index0 = $v16422204651incr; $v16422204651loop->revindex = $v16422204651loop->length - $v16422204651incr; $v16422204651loop->revindex0 = $v16422204651loop->length - ($v16422204651incr + 1); $v16422204651loop->last = ($v16422204651incr == ($v16422204651loop->length - 1)); ?>
        <!--<?= json_encode($menu) ?>-->
        <?php if ($v16422204651loop->last) { ?>
            <li class="active"><?= $menu['name'] ?></li>
        <?php } else { ?>
            <li><?= $this->tag->linkTo([$menu['link'], $menu['name']]) ?></li>
        <?php } ?>
    <?php $v16422204651incr++; } ?>
</ol>
