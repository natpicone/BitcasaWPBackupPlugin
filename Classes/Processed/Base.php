<?php
abstract class WPB2D_Processed_Base
{
    protected
        $db,
        $processed = array()
        ;

    public function __construct()
    {
        $this->db = WPB2D_Factory::db();

        $ret = $this->db->get_results("SELECT * FROM {$this->db->prefix}wpb2d_processed_{$this->getTableName()}");
        if (is_array($ret)) {
            $this->processed = $ret;
        }
    }

    abstract protected function getTableName();

    abstract protected function getId();

    protected function getVar($val)
    {
        return $this->db->get_var(
            $this->db->prepare("SELECT * FROM {$this->db->prefix}wpb2d_processed_{$this->getTableName()} WHERE {$this->getId()} = %s", $val)
        );
    }

    protected function upsert($data)
    {
        $exists = $this->db->get_var(
            $this->db->prepare("SELECT * FROM {$this->db->prefix}wpb2d_processed_{$this->getTableName()} WHERE {$this->getId()} = %s", $data[$this->getId()])
        );

        if (is_null($exists)) {
            $this->db->insert("{$this->db->prefix}wpb2d_processed_{$this->getTableName()}", $data);

            $this->processed[] = (object)$data;
        } else {
            $this->db->update(
                "{$this->db->prefix}wpb2d_processed_{$this->getTableName()}",
                $data,
                array($this->getId() => $data[$this->getId()])
            );

            $i = 0;
            foreach ($this->processed as $p) {
                $id = $this->getId();
                if ($p->$id == $data[$this->getId()]) {
                    break;
                }
                $i++;
            }

            $this->processed[$i] = (object)$data;
        }
    }

    public function truncate()
    {
        $this->db->query("TRUNCATE {$this->db->prefix}wpb2d_processed_{$this->getTableName()}");
    }
}
