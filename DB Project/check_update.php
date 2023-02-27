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
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input value
    $id = $_POST["id"];

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
        // Prepare an update statement
        $sql = "UPDATE checkouts SET cust_id=?, isbn=?, lib_id=?, time='" . date("Y-m-d"). "' WHERE id=?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "iiii", $param_cust_id, $param_isbn, $param_lib_id, $param_id);
            
            // Set parameters
            $param_cust_id = $cust_id;
            $param_isbn = $isbn;
            $param_lib_id = $_SESSION["id"];
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Redirect to login page
                header("location: lib_checkouts.php");
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        mysqli_stmt_close($stmt);
    }
    // Close connection
    mysqli_close($link);
} else{
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM checkouts WHERE id = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            $param_id = $id;

            mysqli_stmt_bind_param($stmt, "i", $param_id);
                  
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    $cust_id = $row["cust_id"];
                    $isbn = $row["isbn"];
                } else{
                    header("location: check_error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Close statement
        mysqli_stmt_close($stmt);
        
        // Close connection
        mysqli_close($link);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: check_error.php");
        exit();
    }
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Record</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .wrapper{
            width: 600px;
            margin: 0 auto;
        }
    </style>
</head>
<body style="background-color:burlywood;">
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                <h2 class="mt-5 mb-3">Update Checkout</h2>
                    <p>Please fill this form to update an checkout record.</p>
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
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="lib_checkouts.php" class="btn btn-danger ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
