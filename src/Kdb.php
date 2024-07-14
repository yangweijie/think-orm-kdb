<?php
namespace think\db;

use Exception;
class Kdb extends Query
{
    /**
     * 设置DUPLICATE
     * @access public
     * @param array|string|Raw $duplicate DUPLICATE信息
     * @return $this
     * @throws Exception
     */
    public function duplicate($duplicate)
    {
        throw new Exception("不支持 on duplicate key");
    }

    public static function parseJson($sql) :string
    {
        $foo = strpos($sql, '->>') === false? 'json_extract_path':'json_extract_path_text';
        $op = $foo == 'json_extract_path_text'?'->>':'->';
        list($field, $jsonPath) = explode($op, $sql);
        $express_arr = [
            "{$foo}(".$field,
        ];
        $jsonPath = str_replace('$.', '', $jsonPath);
        array_map(function($item) use (&$express_arr){
            $express_arr[] = $item;
        }, explode('.', $jsonPath));
        $express_arr[] = ')';
        $express = str_replace(',)', ')', implode(',', $express_arr));

        $jsonPath = trim($jsonPath);
        if(strpos($jsonPath, "'") === false){
            $jsonPath = "'{$jsonPath}'";
        }
        return sprintf($express, $field, $jsonPath);
    }
}