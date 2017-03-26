<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/8/30
 * Time: 19:43
 */

namespace Plugin\Tags;
use Phalcon\Tag;
use Plugin\Core\QSTBaseLogger;

class GroupLayout extends Tag{
    private static $_items = array(
        "KeySet" => '\Plugin\Tags\GroupLayout::KeySet',
        "KeyTable" => '\Plugin\Tags\GroupLayout::KeyTable',
        "TextBlock" => '\Plugin\Tags\GroupLayout::TextBlock',
        "ThumbnailBlock" => '\Plugin\Tags\GroupLayout::ThumbnailBlock'
    );

    private static function renderTableHeader($headers)
    {
        $template[] = '<thead><tr width="100%">';
        foreach ($headers as $header){
            $dispaly = isset($header["name"]) ? $header['name'] : "--";
            $template[] = "<th>$dispaly</th>";
        }
        $template[] = "</tr></thead>";
        return join("", $template);
    }

    public static function Table($headers, $data)
    {
        QSTBaseLogger::getDefault()->log("!!! data:" .json_encode($data));
        $template[] = "<table class=\"table info table-bordered\">";
        $template[] = self::renderTableHeader($headers);
        $template[] = "<tbody>";
        foreach ($data as $datum){
            $template[] = "<tr>";
            foreach ($headers as $header){
                $key = $header['key'];
                if(isset($datum[$key])){
                    if(isset($header['asTh']) && 1 == $header['asTh']){
                        $template[] = "<th>$datum[$key]</th>";
                    }else{
                        $template[] = "<td>$datum[$key]</td>";
                    }
                }else{
                    $template[] = "<td></td>";
                }
            }
            $template[] = "</tr>";
        }
        $template[] = "</tbody></table>";
        return join("", $template);
    }

    public static function KeyTable($data){
        $key = $data['key'];
        $tableData = $data['data'];
        $template[] = "<div class='row'>
            <div class='col-md-2 col-xs-2'><h5 class='text-left'>$key</h5></div>
            <div class='col-md-8 col-xs-8'>";
        $template[] = self::Table($tableData['headers'], $tableData['data']);
        $template[] = "</div></div>";
        return join("", $template);
    }

    public static function KeySet($data){
        $key = $data['key'];
        $value = $data['value'];
        $template = "<div class='row'>
            <div class='col-md-2 col-xs-2'><h5 class='text-left'>$key</h5></div>
            <div class='col-md-8 col-xs-8'><h5 class='text-left''>$value</h5></div>
        </div>";
        return $template;
    }

    public static function TextBlock($data){
        $keyTitle = "title";
        $keySubA = "subtitleA";
        $keySubB = 'subtitleB';
        $keyContent = "content";
        $title = isset($data[$keyTitle]) ? $data[$keyTitle] : "";
        $subtitleA = isset($data[$keySubA]) ? $data[$keySubA] : "";
        $subtitleB = isset($data[$keySubB]) ? $data[$keySubB] : "";
        $content = isset($data[$keyContent]) ? $data[$keyContent] : "";
        return "<div class='row'>
        <h3><strong>$title</strong></h3>
        <h4><small class='text-muted'>$subtitleA</small></h4>
        <h4><small class='text-muted'>$subtitleB</small></h4>
        <p>$content</p></div>";
    }

    public static function ThumbnailBlock($data){
        if(isset($data['image'])){
            $imgSrc = isset($data['image']['src']) ? $data['image']['src'] : "";
            $imgLink = isset($data['image']['link']) ? $data['image']['link'] : "";
            $imgAlt = isset($data['image']['alt']) ? $data['image']['alt'] : "thumbnail block";
        }else{
            $imgSrc = "";
            $imgLink =  "";
            $imgAlt = "thumbnail block";
        }
        $title = isset($data['title']) ? $data['title'] : "";
        $content = isset($data['content']) ? $data['content'] : "";
        $linksGroup = "";
        if(isset($data['labelLinks'])){
            foreach ($data['labelLinks'] as $link) {
                $labelLink = isset($link['link']) ? $link['link'] : "#";
                $labelName = isset($link['name']) ? $link['name'] : "";
                $links[] = "<a href=\"$labelLink\" role=\"button\"><span class='text-muted'>$labelName</span></a>";
            }
            if(isset($links)){
                $linksGroup = join(" | ", $links);
            }
        }
        return "<div class=\"thumbnail\">
        <a href=\"$imgLink\"><img src=\"$imgSrc\" alt=\"$imgAlt\"></a>
        <div class=\"caption\"><h4>$title</h4><p>$content</p><p>$linksGroup</p></div></div>";
    }

    public static function Group($items, $columns = 1, $type = null)
    {
        $len = 12 / $columns;
        $layoutBlank = "<div class='" . "col-md-$len col-xs-$len". "'>";
        $gtype = $type;
        $template[] = "<div class='row'>";
        foreach ($items as $item){
            if(isset($item['type'])){
                $itype = $item['type'];
            }else{
                $itype = $gtype;
            }
            if(!isset($itype)){
                throw new Tag\Exception("Group must be used with type");
            }
            if(isset($itype) && isset(GroupLayout::$_items[$itype])){
                $template[] = $layoutBlank;
                if(isset($item['data'])){
                    $itemData = $item['data'];
                }else{
                    $itemData = $item;
                }
                $template[] = call_user_func(GroupLayout::$_items[$itype], $itemData);
                $template[] = "</div>";
            }else{
                QSTBaseLogger::getDefault()->log($item['type']. " unsupport group item, => ". json_encode($item));
            }
        }
        $template[] = "</div>";
        return join("", $template);
    }

    public static function VerticalGroup($items, $type=null)
    {
        return self::Group($items, 1, $type);
    }
    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerGroup($compiler){
        $compiler->addFunction(
            'qst_group',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                $items = $compiler->expression($exprArgs[0]['expr']);
                if (isset($exprArgs[1])) {
                    $columns = $compiler->expression($exprArgs[1]['expr']);
                } else {
                    $columns = '1';
                }
                if (isset($exprArgs[2])) {
                    $type = $compiler->expression($exprArgs[2]['expr']);
                } else {
                    $type = null;
                }
                return "\Plugin\Tags\GroupLayout::Group($items, $columns, $type)";
            }
        );
    }

    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerVerticalGroup($compiler)
    {
        $compiler->addFunction(
            'qst_vertical_group',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                $items = $compiler->expression($exprArgs[0]['expr']);
                if (isset($exprArgs[1])) {
                    $type = $compiler->expression($exprArgs[1]['expr']);
                } else {
                    $type = null;
                }
                return "\Plugin\Tags\GroupLayout::VerticalGroup($items, $type)";
            }
        );
    }

    /**
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerVoltTags($compiler){
        self::registerGroup($compiler);
        self::registerVerticalGroup($compiler);
    }
}