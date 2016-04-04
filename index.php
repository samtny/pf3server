<?php

require_once 'bootstrap.php';

require 'routes/login.php';
require 'routes/admin.php';
require 'routes/venue.php';
require 'routes/comment.php';
require 'routes/game.php';

$app->run();
