<?php

define('URL', 'http://localhost/');

define('CONFIG', __DIR__.'/');

define('CORE', __DIR__.'/../../truvu/');

define('CONTROLLER', __DIR__.'/../mvc/controllers/');

define('VIEW', __DIR__.'/../mvc/views/');

define('ASSET', __DIR__.'/../asset/'); // workspace for javascript and css source

define('COMPRESS', 0); // compress javascript, css

define('TEST', 1); // if = 1: write code to \app\asset\finish.js|css

require CORE.'loader.php';
