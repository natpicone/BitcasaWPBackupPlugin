<?php
$v = phpversion();
if ($pos = strpos($v, '-'))
    $v = substr($v, 0, $pos);
?>
<div class="wrap" id="wpb2b">
     
    <h2><?php _e('WordPress Backup to Bitcasa', 'wpbtd'); ?></h2>
    <p class="description"><?php printf(__('Version %s', 'wpbtd'), BACKUP_TO_BITCASA_VERSION) ?></p>
    <p>
        <?php _e(sprintf('
            <p>Gday,</p>
            <p>WordPress Backup to Bitcasa is striving to be the #1 backup solution for WordPress and, in order to do so, it needs to use the latest technologies available.</p>
            <p>So, unfortunately your version of PHP (%s) is below version 5.2.16 that is the minimum required version to perform a reliable and successful backup.
            It is <em>STRONGLY</em> recommended that you upgrade to PHP 5.3 or higher because, <a href="%s">as of December 2010</a>, version 5.2 is no longer supported by the PHP community.
            Or, alternatively PHP >= 5.2.16.
            <p>If this is not possible, BACKUP 1.3 supports PHP < 5.2.16 and can be <a href="%s">downloaded here</a> and installed using the WordPress plugin uploader.
            Although this version works 100%%, and has the same premium extensions, it will only be supported with bug fix releases.</p>
            <p>Cheers,<br />Mikey</p>
            ',
            $v,
            'http://www.php.net/archive/2010.php#id2010-12-16-1',
            ''
        ), 'wpbtd'); ?>
    </p>
</div>
