<?php
class WPB2D_BackupController
{
    private
        $bitcasa,
        $config,
        $output,
        $processed_file_count
        ;

    public static function construct()
    {
        return new self();
    }

    public function __construct($output = null)
    {
        $this->config = WPB2D_Factory::get('config');
        $this->bitcasa = WPB2D_Factory::get('bitcasa');
        $this->output = $output ? $output : WPB2D_Extension_Manager::construct()->get_output();
    }

    public function backup_path($path, $bitcasa_path = null, $always_include = null)
    {
        if (!$this->config->get_option('in_progress')) {
            return;
        }

        if (!$bitcasa_path) {
            $bitcasa_path = get_sanitized_home_path();
        }

        $file_list = WPB2D_Factory::get('fileList');

        $current_processed_files = $uploaded_files = array();

        $next_check = time() + 5;
        $total_files = $this->config->get_option('total_file_count');

        $processed_files = WPB2D_Factory::get('processed-files');

        $this->processed_file_count = $processed_files->get_file_count();

        $last_percent = 0;

        if (file_exists($path)) {
            $source = realpath($path);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
            foreach ($files as $file_info) {
                $file = $file_info->getPathname();

                if (time() > $next_check) {
                    $this->config->die_if_stopped();

                    $processed_files->add_files($current_processed_files);
                    $msg = null;

                    if ($this->processed_file_count > 0) {
                        $msg = _n(__('Processed 1 file.', 'wpbtd'), sprintf(__('Processed %s files.', 'wpbtd'), $this->processed_file_count), $this->processed_file_count, 'wpbtd');
                    }

                    if ($total_files > 0) {
                        $percent_done = round(($this->processed_file_count / $total_files) * 100, 0);
                        if ($percent_done < 100) {
                            if ($percent_done < 1) {
                                $percent_done = 1;
                            }

                            if ($percent_done > $last_percent) {
                                $msg .= ' ' . sprintf(__('Approximately %s%% complete.', 'wpbtd'), $percent_done);
                                $last_percent = $percent_done;
                            }
                        }
                    }

                    if ($msg) {
                        WPB2D_Factory::get('logger')->log($msg, $uploaded_files);
                    }

                    $next_check = time() + 5;
                    $uploaded_files = $current_processed_files = array();
                }

                if ($file != $always_include && $file_list->is_excluded($file)) {
                    continue;
                }

                if ($file_list->in_ignore_list($file)) {
                    continue;
                }

                if (is_file($file)) {
                    $processed_file = $processed_files->get_file($file);
                    if ($processed_file && $processed_file->offset == 0) {
                        continue;
                    }

                    if (dirname($file) == $this->config->get_backup_dir() && $file != $always_include) {
                        continue;
                    }

                    if ($this->output->out($bitcasa_path, $file, $processed_file)) {
                        $uploaded_files[] = array(
                            'file' => str_replace($bitcasa_path . DIRECTORY_SEPARATOR, '', WPB2D_BitcasaFacade::remove_secret($file)),
                            'mtime' => filemtime($file),
                        );

                        if ($processed_file && $processed_file->offset > 0) {
                            $processed_files->file_complete($file);
                        }
                    }

                    $current_processed_files[] = $file;
                    $this->processed_file_count++;
                }
            }
        }
    }

    public function execute()
    {
        $manager = WPB2D_Extension_Manager::construct();
        $logger = WPB2D_Factory::get('logger');
        $dbBackup = WPB2D_Factory::get('databaseBackup');

        $this->config->set_time_limit();
        $this->config->set_memory_limit();

        try {

             //Create the SQL backups
            $dbStatus = $dbBackup->get_status();
            if ($dbStatus == WPB2D_DatabaseBackup::NOT_STARTED) {
                if ($dbStatus == WPB2D_DatabaseBackup::IN_PROGRESS) {
                    $logger->log(__('Resuming SQL backup.', 'wpbtd'));
                } else {
                    $logger->log(__('Starting SQL backup.', 'wpbtd'));
                }

                $dbBackup->execute();

                $logger->log(__('SQL backup complete. Starting file backup.', 'wpbtd'));
				return;
				
            }

           

        } catch (Exception $e) { 
		
		
				$logger->log(__('bbbbbbbbbbbbbbbbbbbbbbbb', 'wpbtd'));
		
            	$this->stop();
        }
    }

    public function backup_now()
    {
        if (defined('WPB2D_TEST_MODE')) {
            execute_drobox_backup();
        } else {
            wp_schedule_single_event(time(), 'execute_instant_drobox_backup');
        }
    }

    public function stop()
    {
        $this->config->complete();
        $this->clean_up();
    }

    private function clean_up()
    {
        WPB2D_Factory::get('databaseBackup')->clean_up();
        WPB2D_Extension_Manager::construct()->get_output()->clean_up();
    }

    private static function create_silence_file()
    {
        $silence = WPB2D_Factory::get('config')->get_backup_dir() . DIRECTORY_SEPARATOR . 'index.php';
        if (!file_exists($silence)) {
            $fh = @fopen($silence, 'w');
            if (!$fh) {
                throw new Exception(
                    sprintf(
                        __("WordPress does not have write access to '%s'. Please grant it write privileges before using this plugin."),
                        WPB2D_Factory::get('config')->get_backup_dir()
                    )
                );
            }
            fwrite($fh, "<?php\n// Silence is golden.\n");
            fclose($fh);
        }
    }

    public static function create_dump_dir()
    {
        $dump_dir = WPB2D_Factory::get('config')->get_backup_dir();
        $error_message  = sprintf(__("WordPress Backup to Bitcasa requires write access to '%s', please ensure it exists and has write permissions.", 'wpbtd'), $dump_dir);

        if (!file_exists($dump_dir)) {
            //It really pains me to use the error suppressor here but PHP error handling sucks :-(
            if (!@mkdir($dump_dir)) {
                throw new Exception($error_message);
            }
        } elseif (!is_writable($dump_dir)) {
            throw new Exception($error_message);
        }

        self::create_silence_file();
    }
}
