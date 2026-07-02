* Project:	Nerdmaps for SCUM
* Author:		Jeffrey Cobb
* Email:		pobox.nerdcave@outlook.com
* Date:		June 28th, 2026
* Tools:		Notepad++ v8.9.2, Claude Sonnet 4.6
* License:	Public Domain

# Summary:
   A self-hosted web-based live map tool for SCUM dedicated servers.
   Displays player positions, supports shared map markers (vehicles, fuel, weapons, and more), location pings, sector grid overlay, and per-player follow/hide controls.
   Built with PHP, SQLite, and vanilla JS. Requires access to the SCUM server's SCUM.db file. Player positions update approximately once per minute.

![Nerdmaps for SCUM screenshot](https://nerdcave.net/assets/projects/20260630-84b4c93a482d.png)

# Description:
   The SCUM in-game map offers no player tracking or map markers, so I built this project to fill that gap.

   It tracks players in one-minute intervals because the only player coordinate data accessible is via SCUM.db,	which only updates player coordinates once a minute. Every avenue for more real-time coordinate data has been explored without success. One-minute intervals is the ceiling for now.

* You can add map markers of various types to the map, viewable by anyone with access to this map.
* You can ping any location on the map, viewable by anyone with access to this map.
* You can track a specific player by clicking the target icon next to their name in the player list.
* You can enable/disable specific markers, player blips, the sector grid, and the X/Y coordinate tooltip that follows the mouse cursor.
* Player positions, map markers, and some options are persistent across sessions.
* Zoom level ranges from 50% to 200%.

# Setup:

Requirements:
* A running web server (Apache2 recommended; PHP built-in server works for single-user/testing)
* PHP with the SQLite3 extension enabled
* Read access to the SCUM server's SCUM.db file (see "SCUM.db Access" below)

This describes how I personally use this setup. My friends and I utilize a VPN on a private SCUM server	on my home-lab server, so the web server is available on that same machine.

I use Apache2 on my home-lab server. The main document root is at /var/www/html and the SCUM map files are located at /var/www/scum, so the main site is accessible at http://localhost and the SCUM map at http://localhost/scum.

This is my current default.conf config:

```apache
<VirtualHost *:80>
	ServerAdmin webmaster@localhost
	DocumentRoot /var/www/html
	Alias /scum "/var/www/scum"
	<Directory "/var/www/scum">
		Options Indexes FollowSymLinks
		AllowOverride All
		Require all granted
	</Directory>
	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
```

If you don't have access to a full web server, you can use the PHP built-in server for testing or single-user access:
```bash
php -S 0.0.0.0:8000
```

This makes the map accessible at http://<your-server-ip>:8000. Note that the PHP built-in server is not intended for production or multi-user use.

SCUM.db Access:

On my setup the SCUM.db file was not accessible by the web server by default, so I either had to set permissions on the SaveFiles directory or use a root-level cron job to copy SCUM.db once a minute to a location the web server could access.

* NOTE: When the SCUM server receives updates, these permissions may be reset and will need to be re-applied.

To access SCUM.db directly (an occasional access error may occur due to file locking -- not a serious issue):

* Linux (using AMP server manager):
   The minimal permission needed is execute on the SaveFiles parent directory:
```bash
   sudo chmod o+x /home/amp
   sudo chmod o+x /home/amp/.ampdata/instances/<instance_name>
```

* Windows:
   (Not tested.) Ensure the web server user has read access to the SaveFiles directory and its files.

* Make sure $dbPath in coords.php is set to the SCUM.db file location:
```php
<?php
# coords.php

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 

# Set this variable to point to the SCUM.db file you are working with
$dbPath = '/<location_of_SCUM_database_file>/SCUM.db';

```

# Have Fun!
