<?php 
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["title"] !== 'l'){
  header("location: permissions.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Librarian Tools</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>
<body>

<nav class="navbar navbar-inverse" style="margin-left: 10px; margin-right: 10px; margin-top: 10px;">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="lib_welcome.php">Librarian Tools</a>
    </div>
    <ul class="nav navbar-nav">
      <li class="active"><a href="lib_welcome.php">Home</a></li>
      <li><a href="lib_customers.php">Customers</a></li>
      <li><a href="lib_books.php">Books</a></li>
      <li><a href="lib_checkouts.php">Checkouts</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><a href="signout.php"></span>Sign Out</a></li>
    </ul>
  </div>
</nav>
  
<div class="container">
  <h3>Librarian Tools</h3>
  <p>Use the navigation bar to access tools</p>
</div>

</body>
</html>
