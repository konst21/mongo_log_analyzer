<?php
class Alm_Db  extends Db {

    public function json_insert($regexp, $json, $collection_name, $tictic_interval)
    {
        $sql = <<<sql
REPLACE INTO tictic_json
SET
regexp2 = :regexp2,
json2 = :json2,
time_stamp = :time_stamp,
collection_name = :collection_name,
tictic_interval = :tictic_interval
sql;
        $data = [
            'regexp2' => $regexp,
            'json2' => $json,
            'collection_name' => $collection_name,
            'time_stamp' => time(),
            'tictic_interval' => $tictic_interval,
        ];
        $this->sql_prepare_and_execute($sql, $data);
    }

    public function json_select($regexp, $collection_name, $tictic_interval)
    {
        $sql = <<<sql
SELECT json2
FROM
tictic_json
WHERE
regexp2 = :regexp2
AND
collection_name = :collection_name
AND
tictic_interval = :tictic_interval
sql;
        $data = [
            'regexp2' => $regexp,
            'collection_name' => $collection_name,
            'tictic_interval' => $tictic_interval,
        ];
        return $this->select_value($sql, $data);
    }

    public function set_value($name, $value)
    {
        $sql = <<<sql
REPLACE INTO
options
SET
name = :name,
value = :value
sql;
        $data = [
            'name' => $name,
            'value' => $value,
        ];

        $this->sql_prepare_and_execute($sql, $data);
    }

    public function get_value($name)
    {
        $sql = <<<sql
SELECT value
FROM
options
WHERE
name = :name
sql;
        $data = [
            'name' => $name,
        ];

        return $this->select_value($sql, $data);
    }

    public function gate_realtime_values($gate, $regexp)
    {
        $sql = <<<sql
SELECT json2
FROM
tictic_json
WHERE
regexp2 = :regexp
AND
collection_name = :gate
AND
tictic_interval = :update_interval
sql;
        $data = [
            'regexp' => $regexp,
            'gate' => $gate,
            'update_interval' => Log_Config::$cyfe_realtime_update_interval*60,
        ];

        return $this->select_value($sql, $data);
    }

}