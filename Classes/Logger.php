<?php
class WPB2D_Logger
{
    const LOGFILE = 'wpb2d-backup-log.txt';

    private $logFile = null;

    public function log($msg, $files = null)
    {
        $fh = fopen($this->get_log_file(), 'a');

        $msg = iconv('UTF-8', 'UTF-8//IGNORE', $msg);
        $log = sprintf("%s: %s", date('H:i:s', strtotime(current_time('mysql'))), $msg) . "\n";

        if (!empty($files)) {
            $log .= "Uploaded Files:" . json_encode($files) . "\n";
        }

        if (@fwrite($fh, $log) === false || @fclose($fh) === false) {
            throw new Exception('Error writing to log file.');
        }
    }

    public function get_log()
    {
        $file = $this->get_log_file();
        if (!file_exists($file)) {
            return false;
        }

        $contents = trim(file_get_contents($file));
        if (strlen($contents) < 1) {
            return false;
        }

        return explode("\n", $contents);
    }

    public function delete_log()
    {
        $this->logFile = null;
        @unlink($this->get_log_file());
    }

    public function get_log_file()
    {
        if (!$this->logFile) {
            WPB2D_BackupController::create_dump_dir();

            $path = WPB2D_Factory::get('config')->get_backup_dir() . DIRECTORY_SEPARATOR . self::LOGFILE;

            $files = glob($path . '.*');
            if (isset($files[0])) {
                $this->logFile = $files[0];
            } else {
                $this->logFile = $path . '.' . WPB2D_Factory::secret(self::LOGFILE);
            }
        }

        return $this->logFile;
    }
}
