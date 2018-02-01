<?php

require_once 'Database.php';

$test = Database::getInstance();

$test->setTable('users');

$users = $test->select()->where('username', '=', 'users1')
          ->orWhere('username', '=', 'users2')
          ->where('username', '=', 'users3')
          ->all();