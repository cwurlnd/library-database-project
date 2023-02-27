<?php 
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["title"] !== 'l'){
  header("location: permissions.php");
  exit;
}

$link = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
mysqli_select_db($link, 'cwurl');

if (isset($_GET['page_no']) && $_GET['page_no']!="") {
    $page_no = $_GET['page_no'];
} else {
    $page_no = 1;
    }

$total_records_per_page = 10;
$offset = ($page_no-1) * $total_records_per_page;
$previous_page = $page_no - 1;
$next_page = $page_no + 1;
$adjacents = "2";
$result_count = mysqli_query($link, "SELECT COUNT(*) AS total_records FROM checkouts, books3 where checkouts.isbn = books3.isbn");
$total_records = mysqli_fetch_array($result_count);
$total_records = $total_records['total_records'];
$total_no_of_pages = ceil($total_records / $total_records_per_page);
$second_last = $total_no_of_pages - 1;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Librarian Tools</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">


    <style>
        .wrapper{
            width: 1000px;
            margin: 0 auto;
        }
    </style>
    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();   
        });
    </script>
</head>
<body>

<nav class="navbar navbar-inverse" style="margin-left: 10px; margin-right: 10px; margin-top: 10px;">
<div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="lib_welcome.php">Librarian Tools</a>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="lib_welcome.php">Home</a></li>
      <li><a href="lib_customers.php">Customers</a></li>
      <li><a href="lib_books.php">Books</a></li>
      <li class="active"><a href="lib_checkouts.php">Checkouts</a></li>
    </ul>
    <ul class="nav navbar-nav navbar-right">
      <li><a href="signout.php"></span>Sign Out</a></li>
    </ul>
  </div>
</nav>
  
<div class="container">
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xl-12">
                    <div class="mt-5 mb-3 clearfix">
                        <h2 class="pull-left">Checkout Details</h2>
                        <a href="check_create.php" class="btn btn-success pull-right"><i class="fa fa-plus"></i> Add New Checkout</a>
                    </div>
                    <?php

                    // Attempt select query execution
                    $sql = "SELECT * FROM checkouts, books3 where checkouts.isbn = books3.isbn LIMIT $offset, $total_records_per_page";
                    if($result = mysqli_query($link, $sql)){
                        if(mysqli_num_rows($result) > 0){
                            echo '<table class="table table-bordered table-striped">';
                                echo "<thead>";
                                    echo "<tr>";
                                        echo "<th>#</th>";
                                        echo "<th>Customer ID</th>";
                                        echo "<th>ISBN</th>";
                                        echo "<th>Librarian ID</th>";
																				echo "<th>Title</th>";
																				echo "<th>Author</th>";
																				echo "<th>Checkout Date</th>";
                                        echo "<th>Action</th>";
                                    echo "</tr>";
                                echo "</thead>";
                                echo "<tbody>";
                                while($row = mysqli_fetch_array($result)){
                                    echo "<tr>";
                                        echo "<td>" . $row['id'] . "</td>";
                                        echo "<td>" . $row['cust_id'] . "</td>";
                                        echo "<td>" . $row['isbn'] . "</td>";
                                        echo "<td>" . $row['lib_id'] . "</td>";
																				echo "<td>" . $row['title'] . "</td>";
			                                  echo "<td>" . $row['author'] . "</td>";
																				echo "<td>" . $row['time'] . "</td>";
                                      	echo "<td>";
                                            echo '<a href="check_update.php?id='. $row['id'] .'" class="mr-4" title="Update Record" data-toggle="tooltip"><span class="fa fa-pencil"></span></a>';
                                            echo '<a href="check_delete.php?id='. $row['id'] .'" title="Delete Record" data-toggle="tooltip"><span class="fa fa-trash"></span></a>';
                                        echo "</td>";
                                    echo "</tr>";
                                }
                                echo "</tbody>";                            
                            echo "</table>";
                            // Free result set
                            mysqli_free_result($result);
                        } else{
                            echo '<div class="alert alert-danger"><em>No records were found.</em></div>';
                        }
                    } else{
                        echo "Oops! Something went wrong. Please try again later.";
                    }
 
                    // Close connection
                    mysqli_close($link);
                    ?>
                </div>
                <div style='padding: 10px 20px 0px; border-top: dotted 1px #CCC;'>
                    <strong>Page <?php echo $page_no." of ".$total_no_of_pages; ?></strong>
                </div>
                <ul class="pagination">
                    <?php if($page_no > 1){
                    echo "<li><a href='?page_no=1'>First Page</a></li>";
                    } ?>
                        
                    <li <?php if($page_no <= 1){ echo "class='disabled'"; } ?>>
                    <a <?php if($page_no > 1){
                    echo "href='?page_no=$previous_page'";
                    } ?>>Previous</a>
                    </li>
                        
                    <li <?php if($page_no >= $total_no_of_pages){
                    echo "class='disabled'";
                    } ?>>
                    <a <?php if($page_no < $total_no_of_pages) {
                    echo "href='?page_no=$next_page'";
                    } ?>>Next</a>
                    </li>

                    <?php if($page_no < $total_no_of_pages){
                    echo "<li><a href='?page_no=$total_no_of_pages'>Last &rsaquo;&rsaquo;</a></li>";
                    } ?>
                </ul>
            </div>      
            </div>        
        </div>
    </div>
</div>

</body>
</html>
