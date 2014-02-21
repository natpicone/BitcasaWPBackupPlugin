<?php
class BACKUP_Processed_DBTables extends BACKUP_Processed_Base
{
    const COMPLETE = -1;

    protected function getTableName()
    {
        return 'dbtables';
    }

    protected function getId()
    {
        return 'name';
    }

    public function get_table($name)
    {
        foreach ($this->processed as $table) {
            if ($table->name == $name) {
                return $table;
            }
        }
    }

    public function is_complete($name)
    {
        $table = $this->get_table($name);

        if ($table) {
            return $table->count == self::COMPLETE;
        }

        return false;
    }

    public function count_complete()
    {
        $i = 0;

        foreach ($this->processed as $table) {
            if ($table->count == self::COMPLETE) {
                $i++;
            }
        }

        return $i;
    }

    public function update_table($table, $count)
    {
        $this->upsert(array(
            'name' => $table,
            'count' => (int)$count,
        ));
    }
}
