<?php
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["title"] !== 'l'){
  header("location: permissions.php");
  exit;
}

$link = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
mysqli_select_db($link, 'cwurl');
 
// Define variables and initialize with empty values
$cust_id = $isbn = "";
$cust_id_err = $isbn_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate customer ID
    $input_cust_id = trim($_POST["cust_id"]);
    if(empty($input_cust_id)){
        $cust_id_err = "Please enter a customer ID.";
    } elseif(!ctype_digit($input_cust_id)){
        $cust_id_err = "Please enter a positive integer value.";
    } else{
        // Prepare a select statement
        $sql = "SELECT id FROM customers WHERE id = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_cust_id);
            
            // Set parameters
            $param_cust_id = $input_cust_id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $cust_id = $input_cust_id;
                } else{
                    $cust_id_err = "This customer ID does not exist.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    $input_isbn = trim($_POST["isbn"]);
    if(empty($input_isbn)){
        $isbn_err = "Please enter a ISBN.";
    } elseif(!ctype_digit($input_isbn)){
        $isbn_err = "Please enter a positive integer value.";
    } else{
        // Prepare a select statement
        $sql = "SELECT isbn FROM books3 WHERE isbn = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "i", $param_isbn);
            
            // Set parameters
            $param_isbn = $input_isbn;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                /* store result */
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $isbn = $input_isbn;
                } else{
                    $isbn_err = "This ISBN does not exist.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Check input errors before inserting in database
    if(empty($cust_id_err) && empty($isbn_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO checkouts (cust_id, isbn, lib_id, time) VALUES (?, ?, ?, '" . date('Y-m-d') . "')";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "iii", $param_cust_id, $param_isbn, $param_lib_id);
            
            // Set parameters
            $param_cust_id = $cust_id;
            $param_isbn = $isbn;
            $param_lib_id = $_SESSION["id"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: lib_checkouts.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Checkout</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <h2 class="mt-5 mb-3">Add Checkout</h2>
                    <p>Please fill this form to create a checkout transaction.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Customer ID</label>
                            <input type="text" name="cust_id" class="form-control <?php echo (!empty($cust_id_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $cust_id; ?>">
                            <span class="invalid-feedback"><?php echo $cust_id_err; ?></span>
                        </div>    
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control <?php echo (!empty($isbn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $isbn; ?>">
                            <span class="invalid-feedback"><?php echo $isbn_err; ?></span>
                        </div> 
                        <div class="form-group">
                            <input type="submit" class="btn btn-primary" value="Submit">
                            <a href="lib_checkouts.php" class="btn btn-danger ml-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>    
</body>
</html>
