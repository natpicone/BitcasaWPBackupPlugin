=== WordPress Backup to Bitcasa ===

Keep your valuable WordPress website, its media and database backed up to Bitcasa in minutes with this sleek, easy to use plugin.

== Description ==

[WordPress Backup to Bitcasa] has been created to give you peace of mind that your blog is backed up on a regular basis.

Just choose a day, time and how often you wish yor backup to be performed and kick back and wait for your websites files
and a SQL dump of its database to be dropped in your Bitcasa!

You can set where you want your backup stored within Bitcasa and on your server as well as choose what files or directories,
if any, you wish to exclude from the backup.

The plugin uses [OAuth] so your Bitcasa account details are not stored for the
plugin to gain access.

Checkout the website -

= Setup =

Once installed, the authorization process is easy -

1. When you first access the plugin’s options page, it will ask you to authorize the plugin with Bitcasa.

2. A new window will open and Bitcasa will ask you to authenticate and grant the plugin access.

3. Finally, click continue to setup your backup.

= Minimum Requirements =

1. PHP 5.2.16 or higher with [cURL support]

2. [A Bitcasa account]

Note: Version 1.3 of the plugin supports PHP < 5.2.16 and can be [downloaded here.]

= Errors and Warnings =

During the backup process the plugin may experience problems that will be raised as an error or a warning depending on
its severity.

A warning will be raised if your PHP installation is running in safe mode, if you get this warning please read my blog
post on dealing with this.

If the backup encounters a file that is larger then what can be safely handheld within the memory limit of your PHP
installation, or the file fails to upload to Bitcasa it will be skipped and a warning will be raised.

The plugin attempts to recover from an error that may occur during a backup where backup process goes away for an unknown
reason. In this case the backup will be restarted from where it left off. Unfortunately, at this time, it cannot recover
from other errors, however a message should be displayed informing you of the reason for failure.