# Ensamacademy
username : user
mdp : 123456
site title : ensamacademy
Database name : test


#temp

  add the lines:
  php_value upload_max_filesize 256M
php_value post_max_size 256M
php_value max_execution_time 300
php_value max_input_time 300

at the end of /.htaccess


add  the script:

define( 'WP_MAX_MEMORY_LIMIT', '512M' );
define( 'WP_MEMORY_LIMIT', '256M' );

at the end wp-config.php

