<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/8/30
 * Time: 19:43
 */

namespace Plugin\Tags;
use Phalcon\Di;
use Phalcon\Tag;
use Plugin\Core\QSTBaseLogger;

class ExTags extends Tag{
    private static $tagCounter = array();
    /**
     * @param $options array
     * @link http://tools.qst.org/mediawiki/index.php/%E9%AA%91%E5%A3%AB%E5%9B%A2web%E6%A1%86%E6%9E%B6%E5%8F%8A%E7%BB%84%E4%BB%B6#.E5.A4.9A.E7.BA.A7.E8.81.94.E5.8A.A8.E6.95.B0.E6.8D.AE.E9.80.89.E6.8B.A9.E6.8E.A7.E4.BB.B6.EF.BC.88php.2Bjs.EF.BC.89
     * @return string
     */
    public static function TreeSelector($options){
        if(!isset(self::$tagCounter['treeSelector'])){//当前页码第一次使用该标签，引用依赖的tree-selector.js
            self::$tagCounter['treeSelector'] = 0;
            Di::getDefault()->get('assets')->addJs(_LIBS_."libs/jsapi/tree-selector/tree-selector.js", false)
                ->addJs(_LIBS_."libs/jsapi/tree-selector/tree-selector-loader.js", false)
                ->addCss(_LIBS_."libs/jsapi/tree-selector/tree-selector.css", false);
        }else{
            self::$tagCounter['treeSelector'] += 1;
        }
        if(!isset($options['selector']) || !isset($options['trigger'])){
            QSTBaseLogger::getDefault()->log("[ExTags::TreeSelector] miss required parameters, ". json_encode($options));
            return "";
        }
        $id = "tree_selector_" . self::$tagCounter['treeSelector'];
        $data = json_encode($options);
        $script = '<script> QstLoad.instance["' . $id . '"]={data:'.$data.',loader:"treeSelectorLoader"};'. '</script>';
        $buttonValue = isset($options['trigger']['nameItem']['value']) ? $options['trigger']['nameItem']['value'] : $options['trigger']['defaultDisplay'];
        $nameValue = isset($options['trigger']['nameItem']['value']) ? $options['trigger']['nameItem']['value'] : "";
        $idValue = isset($options['trigger']['idItem']['value']) ? $options['trigger']['idItem']['value'] : "";

        $label = '<label class="control-label">'.$options['trigger']['label'].'</label>';
        $button = '<input type="button" class="btn btn-success" value="'.$buttonValue.'">';
        $nameItem = '<input type="text" name="'.$options['trigger']['nameItem']['key']. '"value="'.$nameValue.'"class="form-control span" style="display: none">';
        $keyItem = '<input type="text" name="'.$options['trigger']['idItem']['key']. '"value="'.$idValue.'"class="form-control span" style="display: none">';

        return '<div class="form-group qst-tree-selector" qst-load = "'.$id.'">'. $label. $button. $nameItem. $keyItem .
            '<div class="qst-select-panel" style="width: 500px;position: absolute;display:none"></div></div>' . $script;
    }

    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerTreeSelector($compiler){
        $compiler->addFunction(
            'data_tree_selector',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                return '\Plugin\Tags\ExTags::TreeSelector(' . $resolvedArgs. ')';

            }
        );
    }

    public static function TagSelector( $label, $key, $candidates, $maxChosen, $chosen = array(), $selfSubmit = false, $buttonDispaly = "提交")
    {
        if(!isset(self::$tagCounter['tagSelector'])){//当前页码第一次使用该标签，引用依赖的tag-selector.js
            self::$tagCounter['tagSelector'] = 0;
            Di::getDefault()->get('assets')->addJs(_LIBS_."libs/jsapi/tag-selector/tag-selector.js", false)
                ->addJs(_LIBS_."libs/jsapi/tag-selector/tag-selector-loader.js", false);
        }else{
            self::$tagCounter['tagSelector'] += 1;
        }
        if(!is_array($candidates)){
            QSTBaseLogger::getDefault()->getLog("candidates must be set as array");
            return "";
        }
        if(!isset($key)){
            QSTBaseLogger::getDefault()->getLog("key must be set for TagSelector");
            return "";
        }
        $id = "tar_selector_". self::$tagCounter['tagSelector'];
        $_label = isset($label) ? $label : "";
        if(true == $selfSubmit){
            $button = "<button class=\"btn btn-success submit\">$buttonDispaly</button>";
        }else{
            $button = "";
        }
        $template[] = "<div class='form-group has-error' qst-load=\"$id\" data-max='$maxChosen'>
        <label for=\"$key\" class=\"control-label\">$_label</label>
        <div class=''><input name='$key' type='text' style='display: none' value=''>
        <div class=\"panel panel-default\" style=\"border: 1px solid; margin-bottom: 8px;\">
        <div class=\"panel-body\">
        <div class=\"col-md-10 col-xs-10 row\"><span class=\"help-block \" style='display: none'>请先选择候选条件</span>
        <ul class=\"nav nav-pills chosen\" role=\"tablist\"></ul></div>
        <div class=\"col-md-2 col-xs-2 row pull-right\" style=\"text-align: right\">$button</div>
        </div></div><ul class='nav nav-pills candidates'>";
        $index = 0;
        foreach ($candidates as $candidate){
            if(is_array($candidate)){
                $dataId = $candidate['key'];
                $display = $candidate['name'];
            }else if(is_string($candidate)){
                $dataId = $index;
                $display = $candidate;
            }else{
                QSTBaseLogger::getDefault()->log("unsupport candidate type");
                continue;
            }
            $template[] = "<li class=\"list-group-item \" style='border: none; padding: 5px; margin: 2px'>
                <a type=\"button\" class=\"btn btn-success candidate\" data-id='$dataId'>$display</a>
            </li>";
            $index += 1;
        }
        $template[] = "</ul></div></div>";
        $template[] = "<script> QstLoad.instance['$id']={data: " . json_encode($chosen) . ",loader:\"tagSelectorLoader\"};</script>";
        return join("", $template);
    }

    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerTagSelector($compiler){
        $compiler->addFunction(
            'tag_selector',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                $label = $compiler->expression($exprArgs[0]['expr']);
                $key = $compiler->expression($exprArgs[1]['expr']);
                $candidates = $compiler->expression($exprArgs[2]['expr']);
                if (isset($exprArgs[3])) {
                    $maxChosen = $compiler->expression($exprArgs[3]['expr']);
                } else {
                    $maxChosen = false;
                }
                if (isset($exprArgs[4])) {
                    $chosen = $compiler->expression($exprArgs[4]['expr']);
                } else {
                    $chosen = array();
                }
                if (isset($exprArgs[5])) {
                    $selfSubmit = $compiler->expression($exprArgs[5]['expr']);
                } else {
                    $selfSubmit = false;
                }
                if (isset($exprArgs[6])) {
                    $buttonDispaly = $compiler->expression($exprArgs[6]['expr']);
                } else {
                    $buttonDispaly = "提交";
                }
                return "\Plugin\Tags\ExTags::TagSelector( $label, $key, $candidates, $maxChosen, $chosen, $selfSubmit, $buttonDispaly)";
            }
        );
    }

    private static function renderStatusButton($parameters)
    {
        $status = isset($parameters['status']) ? $parameters['status'] : "参数缺失";
        $display = isset($parameters['name']) ? $parameters['name'] : "参数缺失";
        $pClass = "mutex-btn-" . $parameters['type'];
        if('submit' == $parameters['type']){
            $pClass .= " btn-success";
        }else{
            $pClass .= " btn-default";
        }
        $pModal = isset($parameters['modal']) ? $parameters['modal'] : "";
        $pTitle = isset($parameters['title']) ? $parameters['title'] : "";
        $pPlaceholder = isset($parameters['placeholder']) ? $parameters['placeholder'] : "";
        return "<li class='list-group-item' style='border: none; padding: 5px; margin: 2px'>
            <button type='button' class='btn $pClass' data-status='$status' data-modal='$pModal' data-title='$pTitle' data-content='$pPlaceholder'>$display</button></li>";
    }

    public static function MutexOperations($keyStatus, $keyExtra, $operations)
    {
        $template[] = "<div class='form-group qst-mutex-operations'>
            <div class=''><ul class='nav nav-pills'><input class='form-status' type='text' name='$keyStatus' style='display: none' value=''>";
        if(null != $keyExtra){
            $template[] = "<input class='form-extra' type='text' name='$keyExtra' style='display: none' value=''>";
            $template[] = "<div class=\"modal fade\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"myModalLabel\" aria-hidden=\"true\" >
            <div class=\"modal-dialog\"><div class=\"modal-content\">
            <div class=\"modal-header\"><button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">×</button>
            <h4 class=\"modal-title\" id=\"myModalLabel\">标题：</h4></div>
            <div class=\"modal-body\"> <textarea class=\"form-control\" rows=\"3\" placeholder=\"\" style='width: 100%'></textarea></div>
            <div class=\"modal-footer\"><button type=\"button\" class=\"btn btn-primary\">确认</button>
            <button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">取消</button></div></div><!-- /.modal-content --></div><!-- /.modal-dialog --></div>";
        }
        foreach ($operations as $operation){
            $type = isset($operation['type']) ? $operation['type'] : "";
            if("" == $type){continue;}
            $template[] = self::renderStatusButton($operation);
        }
        $template[] = "</ul></div></div>";
        return join("", $template);
    }

    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    private static function registerMutexOperations($compiler)
    {
        $compiler->addFunction(
            'mutex_operations',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                $keyStatus = $compiler->expression($exprArgs[0]['expr']);
                $keyExtra = $compiler->expression($exprArgs[1]['expr']);
                $operations = $compiler->expression($exprArgs[2]['expr']);
                return "\Plugin\Tags\ExTags::MutexOperations($keyStatus, $keyExtra, $operations)";
            }
        );
    }
    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerVoltTags($compiler){
        self::registerTreeSelector($compiler);
        self::registerTagSelector($compiler);
        self::registerMutexOperations($compiler);
    }
}
