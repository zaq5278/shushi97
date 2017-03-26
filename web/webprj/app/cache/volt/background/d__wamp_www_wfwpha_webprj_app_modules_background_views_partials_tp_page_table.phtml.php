
<!--角色列表内容-->
<?php $tb_trs = (empty($page_table['trs']) ? ([]) : ($page_table['trs'])); ?>
<?php $tb_ths = (empty($page_table['ths']) ? ([]) : ($page_table['ths'])); ?>
<div>
    <table class="table table-bordered table-hover table-invoice">
        <thead>
            <tr>
                
                <th width="50" class="text-center">#</th>
                <?php foreach ($tb_ths as $item) { ?>
                <th width="<?= (empty($item['width']) ? ('') : ($item['width'])) ?>" class="<?= (empty($item['class']) ? ('') : ($item['class'])) ?>" ><?= $item['name'] ?></th>
                <?php } ?>
                
            </tr>
        </thead>

        <tbody>
            
            
            <?php $tb_trs_start = (empty($tb_trs['start']) ? (1) : ($tb_trs['start'])); ?>
            <?php $tb_trs_data = (empty($tb_trs['data']) ? (null) : ($tb_trs['data'])); ?>
            <?php foreach (range(0, $this->length($tb_trs_data)) as $index) { ?>
                
                <?php if (isset($tb_trs_data[$index])) { ?>
                    
                    <?php $item = $tb_trs_data[$index]; ?>
                    <?php $id = $item['id']; ?>
                    <tr>
                        
                        
                        <td align="center"><?= $index + $tb_trs_start ?></td>
                        
                        <?php if (isset($tb_trs['using'])) { ?>
                            
                            <?php $fields = $tb_trs['using']; ?>
                            <?php foreach ($fields as $field) { ?>
                                <td align="<?= (empty($field['align']) ? ('') : ($field['align'])) ?>">
                                    <?php $filed_type = (empty($field['type']) ? ('text') : ($field['type'])); ?>
                                    <?php if (($filed_type == 'image')) { ?>
                                        <image class="img-thumbnail" src="<?= $item[$field['field']] ?>" alt="图片">
                                    <?php } else { ?>
                                        <?= $item[$field['field']] ?>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                        <?php } else { ?>
                            
                            <?php foreach ($item as $item_filed) { ?>
                                <td><?= $item_filed ?></td>
                            <?php } ?>
                        <?php } ?>
                        

                        
                        
                        <?php $ops = (empty($tb_trs['op']) ? (null) : ($tb_trs['op'])); ?>
                        <?php if (isset($ops)) { ?>
                        <td align="center">
                            <!--<?php $actions = (empty($op['actions']) ? ([]) : ($op['actions'])); ?>-->
                            <?php $v16422204652iterator = $ops; $v16422204652incr = 0; $v16422204652loop = new stdClass(); $v16422204652loop->self = &$v16422204652loop; $v16422204652loop->length = count($v16422204652iterator); $v16422204652loop->index = 1; $v16422204652loop->index0 = 1; $v16422204652loop->revindex = $v16422204652loop->length; $v16422204652loop->revindex0 = $v16422204652loop->length - 1; ?><?php foreach ($v16422204652iterator as $action) { ?><?php $v16422204652loop->first = ($v16422204652incr == 0); $v16422204652loop->index = $v16422204652incr + 1; $v16422204652loop->index0 = $v16422204652incr; $v16422204652loop->revindex = $v16422204652loop->length - $v16422204652incr; $v16422204652loop->revindex0 = $v16422204652loop->length - ($v16422204652incr + 1); $v16422204652loop->last = ($v16422204652incr == ($v16422204652loop->length - 1)); ?>
                                
                                <?php $id_pre = (empty($action['id_pre']) ? ('MG') : ($action['id_pre'])); ?>
                                <?php $url = $action['link']; ?>
                                <?php $action_tip = (empty($action['tip']) ? (false) : ($action['tip'])); ?>
                                <?php $target = (empty($action['target']) ? ('_self') : ($action['target'])); ?>
                                
                                <?php $query_id = ['id' => $id]; ?>
                                <?php $query_params = null; ?>
                                
                                <?php if (isset($action['multiple']) && isset($action['key'])) { ?>
                                    
                                    
                                    <?php $am_index = $item[$action['key']]; ?>
                                    
                                    <?php $action_attr = $action['multiple'][$am_index]; ?>
                                    
                                    <?php $action_class = (empty($action_attr['class']) ? (null) : ($action_attr['class'])); ?>
                                    
                                    <?php $action_name = (empty($action_attr['name']) ? ('') : ($action_attr['name'])); ?>
                                    <?php $query_params = $action['key'] . '=' . $am_index; ?>
                                <?php } else { ?>
                                    
                                    <?php $action_class = (empty($action['class']) ? (null) : ($action['class'])); ?>
                                    
                                    <?php $action_name = (empty($action['name']) ? ('') : ($action['name'])); ?>
                                <?php } ?>

                                
                                <?php if (((empty($action['method']) ? ('get') : ($action['method']))) == 'post') { ?>
                                    <?php $action_class = $action_class . ' mg_post'; ?>
                                <?php } ?>
                                <?php if (isset($query_params)) { ?>
                                    <?php $url = $url . '?' . $query_params; ?>
                                <?php } ?>
                                <?= $this->tag->linkTo(['action' => $url, 'query' => $query_id, 'text' => $action_name, 'class' => $action_class, 'target' => $target, 'id' => $id_pre . $id, 'tip' => $action_tip]) ?>
                                <?php if (!$v16422204652loop->last) { ?>
                                    <?= '|' ?>
                                <?php } ?>
                            <?php $v16422204652incr++; } ?>
                        </td>
                        <?php } ?>
                        
                    </tr>
                <?php } ?>
            <?php } ?>
            
        </tbody>
    </table>

    <?php if (($this->length($tb_trs_data)) <= 0) { ?>
    <h3 class="text-center">没有相关数据</h3>
    <?php } ?>
</div>

