<?php
$config = BACKUP_Factory::get('config');

if (!$config->get_option('in_progress'))
    spawn_cron();

$log = BACKUP_Factory::get('logger')->get_log();

if (empty($log)): ?>
    <p><?php _e('You have not run a backup yet. When you do you will see a log of it here.'); ?></p>
<?php else: ?>
    <ul>
        <?php foreach (array_reverse($log) as $log_item): ?>
            <li>
            <?php
                if (preg_match('/^Uploaded Files:/', $log_item)) {
                    $files = json_decode(preg_replace('/^Uploaded Files:/', '', $log_item), true);
                    continue;
                }
                echo esc_attr($log_item);
            ?>
            <?php if (!empty($files)): ?>
                <a class="view-files" href="#"><?php _e('View uploaded', 'wpbtd') ?> &raquo;</a>
                <ul class="files">
                    <?php foreach ($files as $file): ?>
                        <li title="<?php echo sprintf(__('Last modified: %s', 'wpbtd'), date('F j, Y, H:i:s', $file['mtime'])) ?>"><?php echo esc_attr($file['file']) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php $files = null; endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif;
