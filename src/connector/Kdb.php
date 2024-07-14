<?php

namespace think\db\connector;

use PDO;
use think\db\exception\PDOException;
use think\db\PDOConnection;
use think\Exception;

class Kdb extends PDOConnection
{

    protected $config = [
        // 数据库类型
        'type' => '',
        // 服务器地址
        'hostname' => '',
        // 数据库名
        'database' => '',
        // 用户名
        'username' => '',
        // 密码
        'password' => '',
        // 端口
        'hostport' => '',
        // 连接dsn
        'dsn' => '',
        // 数据库连接参数
        'params' => [],
        // 数据库编码默认采用utf8
        'charset' => 'utf8',
        // 数据库表前缀
        'prefix' => '',
        // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
        'deploy' => 0,
        // 数据库读写是否分离 主从式有效
        'rw_separate' => false,
        // 读写分离后 主服务器数量
        'master_num' => 1,
        // 指定从服务器序号
        'slave_no' => '',
        // 模型写入后自动读取主服务器
        'read_master' => false,
        // 是否严格检查字段是否存在
        'fields_strict' => true,
        // 开启字段缓存
        'fields_cache' => false,
        // 监听SQL
        'trigger_sql' => true,
        // Builder类
        'builder' => '',
        // Query类
        'query' => 'think\db\Kdb',
        // 是否需要断线重连
        'break_reconnect' => false,
        // 断线标识字符串
        'break_match_str' => [],
        // 自动参数绑定
        'auto_param_bind' => true,
    ];

    /**
     * 默认PDO连接参数
     * @var array
     */
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];

    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn(array $config): string {
        $dsn = 'kdb:dbname=' . $config['database'] . ';host=' . $config['hostname'];

        if (!empty($config['hostport'])) {
            $dsn .= ';port=' . $config['hostport'];
        }
//        $dsn .= ';charset=' . $config['charset'];

        return $dsn;
    }

    /**
     * 取得数据表的字段信息
     * @access public
     * @param  string $tableName
     * @return array
     */
    public function getFields(string $tableName): array
    {
        $database = $this->getConfig('database');
        list($tableName) = explode(' ', $tableName);
        $sql = "SELECT t1.TABLE_NAME::REGCLASS::OID, t1.COLUMN_NAME AS field, t1.data_type AS TYPE, t1.is_nullable, t1.column_default AS DEFAULT, t1.column_default AS extra, t2.column_name AS is_pk, t3.description AS COMMENT FROM information_schema.COLUMNS t1 LEFT JOIN information_schema.KEY_COLUMN_USAGE t2 ON t1.table_name = t2.table_name AND t1.column_name = t2.column_name LEFT JOIN pg_description t3 ON t1.dtd_identifier = t3.objsubid::TEXT AND t3.objoid = t1.TABLE_NAME::REGCLASS::OID WHERE t1.table_name = '{$tableName}'";
        try {
            $pdo    = $this->getPDOStatement($sql);
        }catch (PDOException $e){
            throw new Exception($e->getMessage());
        }
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);

        $info   = [];
        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $val = array_change_key_case($val);

                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => (bool) ('NO' == $val['is_nullable']),
                    'default' => $val['default'],
                    'primary' => $val['is_pk'] != null,
                    'autoinc' => (0 === strpos($val['extra'], 'nextval(')),
                    'comment' =>$val['comment'],
                ];
            }
        }

        return $this->fieldCase($info);
    }

    /**
     * 取得数据库的表信息
     * @access public
     * @param  string $dbName
     * @return array
     */
    public function getTables(string $dbName = ''): array
    {
        $sql    = "select tablename as Tables_in_test from pg_tables where  schemaname ='public'";
        $pdo    = $this->getPDOStatement($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];

        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }

        return $info;
    }

    protected function supportSavepoint(): bool
    {
        return true;
    }


}