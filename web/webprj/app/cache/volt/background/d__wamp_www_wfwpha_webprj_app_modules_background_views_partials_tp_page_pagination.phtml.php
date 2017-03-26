
<div class="text-right">
    <!-- 分页数据初始化 -->
    
    <?php $page_pagination = $tb_page; ?>
    <?php $page_cur = (empty($page_pagination['cur']) ? (1) : ($page_pagination['cur'])); ?>
    
    <?php $page_total = (empty($page_pagination['total']) ? (1) : ($page_pagination['total'])); ?>
    
    <?php $page_url = (empty($page_pagination['url']) ? ('') : ($page_pagination['url'])); ?>
<!--    <?php $page_url = $page_url; ?>-->
    
    <?php $page_rows_options = (empty($page_pagination['rows_options']) ? (['10' => '10', '20' => '20', '30' => '30', '50' => '50']) : ($page_pagination['rows_options'])); ?>
    
    <?php $page_rows_value = (empty($page_pagination['rows_value']) ? (10) : ($page_pagination['rows_value'])); ?>
    <?php $page_rows = $page_rows_options[$page_rows_value]; ?>

    
    <?php $data_total = (empty($page_pagination['data_total']) ? (0) : ($page_pagination['data_total'])); ?>
    
    <?php $data_first = (empty($page_pagination['data_start']) ? (1) : ($page_pagination['data_start'])); ?>
    
    <?php $data_last = min(($data_first + $page_rows),$data_total); ?>

    <ul class="pagination pull-left">
        <!--每页显示行数控制-->
        <li>
            <?= $this->tag->select(['page_row', $page_rows_options, 'value' => $page_rows_value]) ?>
        </li>
<!--        <li class="disabled"><span>每页</span></li>-->
    </ul>
    <!-- 分页描述 -->
    <ul class="pagination clear-border">
        <li class="disabled">
            <span>共<strong> <?= $data_total ?> </strong>条记录，第<strong><?= $page_cur ?></strong>页/共<strong> <?= ceil($page_total) ?> </strong>页</span>
        </li>
    </ul>

    <!-- 分页控制 -->
    <ul class="pagination ">
        <!--页码控制-->
        
        
        
        
	
        <?php if ($page_cur > 1) { ?>
            <li><a href="<?= $page_url . 1 ?>">首页</a></li>
            <li><a href="<?= $page_url . ($page_cur - 1) ?>">上一页</a></li>
        <?php } else { ?>
            <li class="disabled"><a>首页</a></li>
            <li class="disabled"><a>上一页</a></li>
        <?php } ?>
        
        <?php if ($page_cur >= $page_total) { ?>
            <li class="disabled"><a>下一页</a></li>
            <li class="disabled"><a>尾页</a></li>
        <?php } else { ?>
            <li><a href="<?= $page_url . ($page_cur + 1) ?>">下一页</a></li>
            <li><a href="<?= $page_url . $page_total ?>">尾页</a></li>
        <?php } ?>
    </ul>
</div>

