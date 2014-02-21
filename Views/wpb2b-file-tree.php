<?php
BACKUP_Factory::get('config')->set_time_limit();
BACKUP_Factory::get('config')->set_memory_limit();

try {
    $file_list = new BACKUP_FileList();

    if (isset($_POST['dir'])) {

        //Convert to the os' directiry separator
        $_POST['dir'] = str_replace('/', DIRECTORY_SEPARATOR, urldecode($_POST['dir']));

        if (file_exists($_POST['dir']) && is_readable($_POST['dir'])) {
            $files = scandir($_POST['dir']);
            natcasesort($files);
            if (count($files) > 2) { /* The 2 accounts for . and .. */
                echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
                // All dirs
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && file_exists($_POST['dir'] . $file) && is_dir($_POST['dir'] . $file)) {

                        if (!is_readable($_POST['dir']) || $_POST['dir'] == dirname(get_sanitized_home_path()) && !strstr($file, basename(get_sanitized_home_path()))) {
                            continue;
                        }

                        if ($file_list->in_ignore_list($file)) {
                            continue;
                        }

                        $full_path = htmlentities($_POST['dir'] . $file);
                        $file = htmlentities($file);
                        $class = $file_list->get_checkbox_class($full_path);

                        echo "<li class='directory collapsed'>";
                        echo "<a href='#' rel='" . str_replace('\\', '/', $full_path) . '/' . "' class='tree'>$file</a>";
                        echo "<a href='#' rel='" . str_replace('\\', '/', $full_path) . '/' . "' class='checkbox directory $class'></a>";
                        echo "</li>";
                    }
                }
                // All files
                foreach ($files as $file) {

                    if ($file != '.' && $file != '..' && file_exists($_POST['dir'] . $file) && !is_dir($_POST['dir'] . $file)) {

                        if ($_POST['dir'] == dirname(get_sanitized_home_path()) && !strstr($file, basename(get_sanitized_home_path()))) {
                            continue;
                        }

                        if ($file_list->in_ignore_list($file)) {
                            continue;
                        }

                        $full_path = htmlentities($_POST['dir'] . $file);
                        $file = htmlentities($file);
                        $class = $file_list->get_checkbox_class($full_path);
                        $ext = preg_replace('/^.*\./', '', $file);

                        echo "<li class='file ext_$ext'>";
                        echo "<a href='#' rel='" . str_replace('\\', '/', $full_path) . "' class='tree'>$file</a>";
                        if (strstr($_POST['dir'] . $file, DB_NAME . '-backup.sql') === false) {
                            echo "<a href='#' rel='" . str_replace('\\', '/', $full_path) . "' class='checkbox $class'></a>";
                        }
                        echo "</li>";
                    }
                }
                echo "</ul>";
            }
        }
    } elseif ($_POST['exclude'] && $_POST['path']) {

        //Convert to the os' directiry separator
        $path = str_replace('/', DIRECTORY_SEPARATOR, urldecode($_POST['path']));

        if ($_POST['exclude'] == 'true')
            $file_list->set_excluded($path);
        else
            $file_list->set_included($path);
    }
} catch (Exception $e) {
    echo '<p class="error">' . $e->getMessage() . '</p>';
}
