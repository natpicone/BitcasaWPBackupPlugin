<?php
class BACKUP_UploadTracker
{
    private $processed_files;

    public function __construct()
    {
        $this->processed_files = new BACKUP_Processed_Files();
    }

    public function track_upload($file, $upload_id, $offset)
    {
        BACKUP_Factory::get('config')->die_if_stopped();

        $this->processed_files->update_file($file, $upload_id, $offset);

        BACKUP_Factory::get('logger')->log(sprintf(
            __("Uploaded %sMB of %sMB", 'wpbtd'),
            round($offset / 1048576, 1),
            round(filesize($file) / 1048576, 1)
        ));
    }
}
