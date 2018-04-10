<?php
/**
 * Created by PhpStorm.
 * User: shayao
 * Date: 2018/4/9
 * Time: 0:45
 */

function IndexBy($model, $column)
{
    $list = [];
    foreach ($model as $index => $item) {
        if (is_object($item)) {
            $list[$item->$column] = $item;
        }
        if (is_array($item)) {
            $list[$item[$column]] = $item;
        }
    }
    return $list;
}

function PointTwoStay($num)
{
    return sprintf("%.3f", substr(sprintf("%.6f", $num), 0, -3));
}