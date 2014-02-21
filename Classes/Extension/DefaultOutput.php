<?php
class WPB2D_Extension_DefaultOutput extends WPB2D_Extension_Base
{
    const MAX_ERRORS = 10;

    private
        $error_count,
        $root
        ;

    public function set_root($root)
    {
        $this->root = $root;

        return $this;
    }

    public function out($source, $file, $processed_file = null)
    {
        if ($this->error_count > self::MAX_ERRORS)
            throw new Exception(sprintf(__('The backup is having trouble uploading files to bitcasa, it has failed %s times and is aborting the backup.', 'wpbtd'), self::MAX_ERRORS));

        if (!$this->bitcasa)
            throw new Exception(__("bitcasa API not set"));

        $bitcasa_path = $this->config->get_bitcasa_path($source, $file, $this->root);

        try {
            $directory_contents = $this->bitcasa->get_directory_contents($bitcasa_path);

            if (!in_array(basename($file), $directory_contents) || filemtime($file) > $this->config->get_option('last_backup_time')) {
                $file_size = filesize($file);
                if ($file_size > $this->get_chunked_upload_threashold()) {

                    $msg = __("Uploading large file '%s' (%sMB) in chunks", 'wpbtd');
                    if ($processed_file && $processed_file->offset > 0)
                        $msg = __("Resuming upload of large file '%s'", 'wpbtd');

                    WPB2D_Factory::get('logger')->log(sprintf(
                        $msg,
                        basename($file),
                        round($file_size / 1048576, 1)
                    ));

                    return $this->bitcasa->chunk_upload_file($bitcasa_path, $file, $processed_file);
                } else {
                    return $this->bitcasa->upload_file($bitcasa_path, $file);
                }
            }

        } catch (Exception $e) {
            WPB2D_Factory::get('logger')->log(sprintf(__("Error uploading '%s' to bitcasa: %s", 'wpbtd'), $file, strip_tags($e->getMessage())));
            $this->error_count++;
        }
    }

    public function start()
    {
        return true;
    }

    public function end() {}
    public function complete() {}
    public function failure() {}

    public function get_menu() {}
    public function get_type() {}

    public function is_enabled() {}
    public function set_enabled($bool) {}
    public function clean_up() {}
}
