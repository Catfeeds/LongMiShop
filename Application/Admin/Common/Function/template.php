<?php


define('TEMPLATE_DISPLAY', 0);
define('TEMPLATE_FETCH', 1);
define('TEMPLATE_INCLUDEPATH', 2);

function tpl_wappage_editor($editorparams = '', $editormodules = array()) {
    global $_GPC;
    $content = '';
    $filetree = file_tree('./Application/Admin/Widget');
    if (!empty($filetree)) {
        foreach ($filetree as $file) {
            if (strexists($file, 'widget-')) {
                $fileinfo = pathinfo($file);
                $_GPC['iseditor'] = false;
                $display = widget('/'.$fileinfo['filename'], TEMPLATE_FETCH);
                $_GPC['iseditor'] = true;
                $editor = widget('/'.$fileinfo['filename'], TEMPLATE_FETCH);
                $content .= "<script type=\"text/ng-template\" id=\"{$fileinfo['filename']}-display.html\">".str_replace(array("\r\n", "\n", "\t"), '', $display)."</script>";
                $content .= "<script type=\"text/ng-template\" id=\"{$fileinfo['filename']}-editor.html\">".str_replace(array("\r\n", "\n", "\t"), '', $editor)."</script>";
            }
        }
    }
    return $content;
}




function widget($filename, $flag = TEMPLATE_DISPLAY) {
    $compile = "./Application/Admin/Widget/{$filename}.html";
    if(!is_file($compile)) {
        exit("Error: template source '{$filename}' is not exist!");
    }
    switch ($flag) {
        case TEMPLATE_DISPLAY:
        default:
            extract($GLOBALS, EXTR_SKIP);
            include $compile;
            break;
        case TEMPLATE_FETCH:
            extract($GLOBALS, EXTR_SKIP);
            ob_flush();
            ob_clean();
            ob_start();
            include $compile;
            $contents = ob_get_contents();
            ob_clean();
            return $contents;
            break;
        case TEMPLATE_INCLUDEPATH:
            return $compile;
            break;
    }
}


//
//
//function template($filename, $flag = TEMPLATE_DISPLAY) {
//    $source = "/web/themes/{$_W['template']}/{$filename}.html";
//    $compile = IA_ROOT . "/data/tpl/web/{$_W['template']}/{$filename}.tpl.php";
//    if(!is_file($source)) {
//        $source = IA_ROOT . "/web/themes/default/{$filename}.html";
//        $compile = IA_ROOT . "/data/tpl/web/default/{$filename}.tpl.php";
//    }
//
//    if(!is_file($source)) {
//        exit("Error: template source '{$filename}' is not exist!");
//    }
//    if(DEVELOPMENT || !is_file($compile) || filemtime($source) > filemtime($compile)) {
//        template_compile($source, $compile);
//    }
//    switch ($flag) {
//        case TEMPLATE_DISPLAY:
//        default:
//            extract($GLOBALS, EXTR_SKIP);
//            include $compile;
//            break;
//        case TEMPLATE_FETCH:
//            extract($GLOBALS, EXTR_SKIP);
//            ob_flush();
//            ob_clean();
//            ob_start();
//            include $compile;
//            $contents = ob_get_contents();
//            ob_clean();
//            return $contents;
//            break;
//        case TEMPLATE_INCLUDEPATH:
//            return $compile;
//            break;
//    }
//}