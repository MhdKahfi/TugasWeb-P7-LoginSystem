<?php
require_once 'config.php';

redirect(isLoggedIn() ? 'dashboard.php' : 'login.php');
