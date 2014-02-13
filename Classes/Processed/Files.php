<?php
class WPB2D_Processed_Files extends WPB2D_Processed_Base
{
    protected function getTableName()
    {
        return 'files';
    }

    protected function getId()
    {
        return 'file';
    }

    public function get_file_count()
    {
        return count($this->processed);
    }

    public function get_file($file_name)
    {
        foreach ($this->processed as $file) {
            if ($file->file == $file_name){
                return $file;
            }
        }
    }

    public function file_complete($file)
    {
        $this->update_file($file, 0, 0);
    }

    public function update_file($file, $upload_id, $offset)
    {
        $this->upsert(array(
            'file' => $file,
            'uploadid' => $upload_id,
            'offset' => $offset,
        ));
    }

    public function add_files($new_files)
    {
        foreach ($new_files as $file) {

            if ($this->get_file($file)) {
                continue;
            }

            $this->upsert(array(
                'file' => $file,
                'uploadid' => null,
                'offset' => null,
            ));
        }

        return $this;
    }
}
