<?php
function msgBox($type,$msg=null,$url=null){
    echo '<script>';
    if($msg != false){
        echo 'alert("'.$msg.'");';
    }
    switch($type){
        case -1:
            echo 'history.go(-1);</script>';exit;
            break;
        case 0:
            echo "</script>";
            break;
        case 1:
            echo "location.href='{$url}';</script>";exit;
            break;
        default:break;
    }
}