Required environment
========

Need PHP version not less than 5.3, just need to install PHP Cli, no need to install PHP-FPM, nginx, apache


Example
========
[Live Demo](http://www.workerman.net:55757/)

Installation
=========
1、Download or run ```git clone https://github.com/walkor/workerman-statistics```

2、Run ```composer install```

Starting and Stopping
=========

Take ubuntu as an example

start up 
`php start.php start -d`

Restart boot
`php start.php restart`

Smooth restart/reload configuration
`php start.php reload`

View service status
`php start.php status`

stop 
`php start.php stop`

Running on a Windows system
======
1、The Windows platform needs to replace the Workerman directory with the [Windows version of Workerman](https://github.com/walkor/workerman-for-win)

2, run start_for_win.bat

[For Windows version of Workerman, see here](http://www.workerman.net/windows)

Permission Validation
=======

* The administrator username and password are empty by default, that is, you can view the monitoring data without logging in.
* If login authentication is required, set the administrator password in applications/Statistics/Config/Config.php


[More please visit www.workerman.net](http://www.workerman.net/workerman-statistics)
