<?php

namespace App\Patches\Factory;

use Think\Model;

class Store
{
    private $db;

    public function __construct()
    {
        $this->db = new Model();
    }

    public function run()
    {
        $this->preProcessing();

        if (!$this->directoryPermissions(BASE_PATH)) {
            exit('升级前请给 app 目录读写权限，更新完成后再恢复为只读。');
        }

        $exists_log = $this->existsLog();

        $exists_patch = $this->existsPatch();

        $diff = empty($exists_log) ? $exists_patch : array_diff($exists_patch, $exists_log);

        $this->upgrade($diff);
    }

    /**
     * 数据库预处理
     */
    private function preProcessing()
    {
        $sql = <<<EOT
CREATE TABLE IF NOT EXISTS `__PREFIX__touch_upgrade` (
`id`  int UNSIGNED NOT NULL AUTO_INCREMENT ,
`upgrade`  varchar(255) NOT NULL ,
`time`  datetime NOT NULL ,
PRIMARY KEY (`id`)
);
EOT;

        $this->db->execute($sql);
    }

    /**
     * 测试目录读写权限
     * @param $d
     * @return bool
     */
    private function directoryPermissions($d)
    {
        $tfile = "_test.txt";
        $fp = fopen($d . "/" . $tfile, "w");
        if (!$fp) {
            return false;
        }
        fclose($fp);
        $rs = unlink($d . "/" . $tfile);
        if ($rs) {
            return true;
        }
        return false;
    }

    /**
     * 已更新日志
     * @return array
     */
    private function existsLog()
    {
        return dao('touch_upgrade')->getField('upgrade', true);
    }

    /**
     * 补丁文件
     * @return array
     */
    private function existsPatch()
    {
        $rs = glob(dirname(__DIR__) . '/*');

        $list = [];
        foreach ($rs as $dir) {
            $list[] = basename($dir);
        }

        array_shift($list);

        return $list;
    }

    /**
     * 批量执行补丁
     * @param array $patches
     */
    private function upgrade($patches = [])
    {
        foreach ($patches as $patch) {
            $this->handing($patch);
        }
    }

    /**
     * 执行补丁
     * @param $patch
     */
    private function handing($patch)
    {
        $handler = 'App\\Patches\\' . $patch . '\\' . $patch;
        $factory = new $handler();

        $factory->updateDatabaseOptionally();
        $factory->updateFiles();

        $data = [
            'upgrade' => $patch,
            'time' => date('Y-m-d H:i:s'),
        ];
        dao('touch_upgrade')->add($data);
    }
}
