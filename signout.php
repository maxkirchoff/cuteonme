<?php

/**
 * The signout page clears your session data, so you can return to the login page.
 */ 

// Load and clear sessions
session_start();
session_destroy();
 
// Redirect to the signin page
header('Location: ./signin.php');
