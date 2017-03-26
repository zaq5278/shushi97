
<!--页面头部 = 标题 + 列表新增按钮/返回列表-->
<div class="page-header">
    <?php if (isset($page_header['action']['link'])) { ?>
    <?= $this->tag->linkTo([$page_header['action']['link'], $page_header['action']['name'], 'class' => 'btn btn-success add', 'role' => 'button']) ?>
    <?php } ?>
</div>
