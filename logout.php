<?php
// SmartGov Market - Logout Handler
// File: logout.php

require_once __DIR__ . '/includes/auth.php';

logoutUser();
setFlashMessage('info', 'You have been logged out successfully.');
redirect('login.php');
