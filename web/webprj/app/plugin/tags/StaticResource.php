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

class StaticResource extends Tag{
    /**
     * register stylesheet_link tag for static libs css.
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerLibsLinks($compiler){
        $compiler->addFunction(
            'stylesheet_link_libs',
            function ($resolvedArgs, $exprArgs) use ($compiler) {
                $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                if(isset($firstArgument)){
                    $routePath = "";
                    if(defined('_LIBS_')){
                        $routePath = _LIBS_;
                    }
                    return "\$this->tag->stylesheetLink('".$routePath.substr($firstArgument, 1, strlen($firstArgument)-2)."', false);";
                }else{
                    QSTBaseLogger::getInstance()->log("stylesheet_link_libs src must be set and type string");
                    return "";
                }
            }
        );
    }

    /**
     * register javascript_include tag for static libs js.
     * @param $compiler \Phalcon\Mvc\View\Engine\Volt\Compiler
     */
    public static function registerLibsJsInclude($compiler){
        $compiler->addFunction(
            'javascript_include_libs',
                function ($resolvedArgs, $exprArgs) use ($compiler) {
                    $firstArgument = $compiler->expression($exprArgs[0]['expr']);
                    if(isset($firstArgument)){
                        $routePath = "";
                        if(defined('_LIBS_')){
                            $routePath = _LIBS_;
                        }
                        return "\$this->tag->javascriptInclude('".$routePath.substr($firstArgument, 1, strlen($firstArgument)-2)."', false);";
                    }else{
                        QSTBaseLogger::getInstance()->log("javascript_include_libs src must be set and type string");
                        return "";
                    }
                });
    }
    public static function registerLibsVoltTags($compiler){
        self::registerLibsLinks($compiler);
        self::registerLibsJsInclude($compiler);
    }
}