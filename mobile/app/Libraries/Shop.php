<?php

namespace App\Libraries;

class Shop
{
    public $db_name = '';
    public $prefix = 'ecs_';

    /**
     * Shop constructor.
     * @param $db_name
     * @param $prefix
     */
    public function __construct($db_name, $prefix)
    {
        $this->db_name = $db_name;
        $this->prefix = $prefix;
    }

    /**
     * 将指定的表名加上前缀后返回
     *
     * @access  public
     * @param   string $str 表名
     *
     * @return  string
     */
    public function table($str)
    {
        return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
    }

    /**
     * ECSHOP 密码编译方法;
     *
     * @access  public
     * @param   string $pass 需要编译的原始密码
     *
     * @return  string
     */
    public function compile_password($pass)
    {
        return md5($pass);
    }

    /**
     * 取得当前的域名
     *
     * @access  public
     *
     * @return  string      当前的域名
     */
    public function get_domain()
    {
        /* 协议 */
        $protocol = $this->http();

        /* 域名或IP地址 */
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } else {
            /* 端口 */
            if (isset($_SERVER['SERVER_PORT'])) {
                $port = ':' . $_SERVER['SERVER_PORT'];

                if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
                    $port = '';
                }
            } else {
                $port = '';
            }

            if (isset($_SERVER['SERVER_NAME'])) {
                $host = $_SERVER['SERVER_NAME'] . $port;
            } elseif (isset($_SERVER['SERVER_ADDR'])) {
                $host = $_SERVER['SERVER_ADDR'] . $port;
            }
        }

        return $protocol . $host;
    }

    /**
     * 获得 ECSHOP 当前环境的 URL 地址
     *
     * @access  public
     *
     * @return  void
     */
    public function url()
    {
        $curr = strpos(PHP_SELF, ADMIN_PATH . '/') !== false ?
            preg_replace('/(.*)(' . ADMIN_PATH . ')(\/?)(.)*/i', '\1', dirname(PHP_SELF)) :
            dirname(PHP_SELF);

        $root = str_replace('\\', '/', $curr);

        if (substr($root, -1) != '/') {
            $root .= '/';
        }

        return $this->get_domain() . $root;
    }

    /**
     * 获得 ECSHOP 当前环境的 HTTP 协议方式
     *
     * @access  public
     *
     * @return  void
     */
    public function http()
    {
        return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
    }

    /**
     * 获得数据目录的路径
     *
     * @param int $sid
     *
     * @return string 路径
     */
    public function data_dir($sid = 0)
    {
        if (empty($sid)) {
            $s = 'data';
        } else {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= $sid % 3000;
        }
        return $s;
    }

    /**
     * 获得图片的目录路径
     *
     * @param int $sid
     *
     * @return string 路径
     */
    public function image_dir($sid = 0)
    {
        if (empty($sid)) {
            $s = 'images';
        } else {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= ($sid % 3000) . '/';
            $s .= 'images';
        }
        return $s;
    }

    /**
     * 查询MYSQL拼接字符串数据
     * $select_array 查询的字段
     * $select_id 查询的ID值
     * $where 查询的条件 比如（AND goods_id = '$goods_id'）
     * $table 表名称
     * $id 被查询的字段
     * $is_db 查询返回数组方式
     */
    public function get_select_find_in_set($is_db = 0, $select_id, $select_array = [], $where = '', $table = '', $id = '', $replace = '')
    {
        if ($replace) {
            $replace = "REPLACE ($id,'$replace',',')";
        } else {
            $replace = "$id";
        }

        if ($select_array && is_array($select_array)) {
            $select = implode(',', $select_array);
        } else {
            $select = '*';
        }
        $sql = "SELECT $select FROM " . $GLOBALS['ecs']->table($table) . " WHERE find_in_set('$select_id', $replace) $where";
        if ($is_db == 1) {
            //多条数组数据
            return $GLOBALS['db']->getAll($sql);
        } elseif ($is_db == 2) {
            //一条数组数据
            return $GLOBALS['db']->getRow($sql);
        } else {
            //返回某个字段的值
            return $GLOBALS['db']->getOne($sql, true);
        }
    }
}
