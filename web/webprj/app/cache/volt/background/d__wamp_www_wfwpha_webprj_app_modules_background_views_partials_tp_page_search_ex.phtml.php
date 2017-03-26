
<div class="mbot30">
    <?php $search_action = $page_search['link']; ?>
    <form action='<?= $this->url->get($search_action) ?>' autocomplete="on">
        <div class="form-group">
            <div class="form-inline">
                <?php $conditions = (empty($page_search['conditions']) ? ([]) : ($page_search['conditions'])); ?>
                <?php foreach ($conditions as $element) { ?>
                    <?php if ($element['type'] == 0) { ?>
                    <div class="form-group">
                        <?php $search_keys = $element['keys']; ?>
                        <?php $search_key_default = $element['key_default']; ?>
                        <?php $search_value_default = $element['value_default']; ?>
                        <?php $empty_text = (empty($element['empty_text']) ? ('全部') : ($element['empty_text'])); ?>
                        <?php $empty_value = (empty($element['empty_value']) ? ('') : ($element['empty_value'])); ?>
                        <?= $this->tag->select(['search_key', $search_keys, 'useEmpty' => true, 'emptyText' => $empty_text, 'emptyValue' => $empty_value, 'class' => 'form-control', 'value' => $search_key_default]) ?>
                        <?= $this->tag->textField(['search_value', 'class' => 'form-control', 'value' => $search_value_default]) ?>
                    </div>
                    <?php } elseif ($element['type'] == 1) { ?>
                    <div class="form-group">
                        <label class="control-label"><?= $element['label'] ?></label>
                        <?php $empty_text = (empty($element['empty_text']) ? ('全部') : ($element['empty_text'])); ?>
                        <?php $empty_value = (empty($element['empty_value']) ? ('') : ($element['empty_value'])); ?>
                        <?= $this->tag->select([$element['key'], $element['options'], 'useEmpty' => true, 'emptyText' => $empty_text, 'emptyValue' => $empty_value, 'class' => 'form-control', 'value' => (empty($element['value']) ? ('') : ($element['value']))]) ?>
                    </div>
                    <?php } elseif ($element['type'] == 2) { ?>
                        <?php $date_type = (empty($element['date_type']) ? (0) : ($element['date_type'])); ?>
                        <?php if ($date_type == 0) { ?>
                        <div class="form-group">
                            <label class="control-label"><?= $element['label'] ?></label>
                            <div class="form-group">
                                <div class="input-group date">
                                    <?php $date = (empty($element['date']) ? ([]) : ($element['date'])); ?>
                                    <?php $key = (empty($date['key']) ? ('mg_date') : ($date['key'])); ?>
                                    <?php $value = (empty($date['value']) ? ('') : ($date['value'])); ?>
                                    <?= $this->tag->textField([$key, 'class' => 'form-control  cond-date', 'value' => $value]) ?>
                                    <span class="input-group-addon"><i class="fa fa-th"></i></span>
                                </div>
                            </div>
                        </div>
                        <?php } elseif ($date_type == 1) { ?>
                        <div class="form-group">
                            <label class="control-label"><?= $element['label'] ?></label>
                            <div class="form-group">
                                <div class="input-group input-daterange">
                                    <?php $date_start = (empty($element['date_start']) ? ([]) : ($element['date_start'])); ?>
                                    <?php $key_start = (empty($date_start['key']) ? ('mg_date_start') : ($date_start['key'])); ?>
                                    <?php $value_start = (empty($date_start['value']) ? ('') : ($date_start['value'])); ?>
                                    <?= $this->tag->textField([$key_start, 'class' => 'form-control cond-date', 'value' => $value_start]) ?>
                                    <span class="input-group-addon">to</span>
                                    <?php $date_end = (empty($element['date_end']) ? ([]) : ($element['date_end'])); ?>
                                    <?php $key_end = (empty($date_end['key']) ? ('mg_date_end') : ($date_end['key'])); ?>
                                    <?php $value_end = (empty($date_end['value']) ? ('') : ($date_end['value'])); ?>
                                    <?= $this->tag->textField([$key_end, 'class' => 'form-control cond-date', 'value' => $value_end]) ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } ?>
                    &nbsp;
                    <?php if (((empty($element['br']) ? (false) : ($element['br'])))) { ?>
                        <br>
                    <?php } ?>
                <?php } ?>
                <div class="form-group">
                    <?= $this->tag->submitButton(['筛选', 'class' => 'btn btn-success']) ?>
                </div>
                <div class="form-group">
                <?= $this->partial('partials/tp_page_header') ?>
                </div>
            </div>
        </div>
    </form>
</div>

