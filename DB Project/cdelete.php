<?php
// Process delete operation after confirmation
if(isset($_POST["isbn"]) && !empty($_POST["isbn"])){
    // Include config file
    $link = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
    mysqli_select_db($link, 'cwurl');
    
    // Prepare a delete statement
    $sql = "DELETE FROM books3 WHERE isbn = ?";
    
    if($stmt = mysqli_prepare($link, $sql)){
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "i", $param_isbn);
        
        // Set parameters
        $param_isbn = trim($_POST["isbn"]);
        
        if(mysqli_stmt_execute($stmt)){
            header("location: lib_books.php");
            exit();
        } else{
            echo "Oops! Something went wrong. Please try again later.";
        }
    }
     
    // Close statement
    mysqli_stmt_close($stmt);
    
    // Close connection
    mysqli_close($link);
} else{
    if(empty(trim($_GET["isbn"]))){
        header("location: cerror.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Record</title>
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
                    <h2 class="mt-5 mb-3">Delete Record</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="alert alert-danger">
                            <input type="hidden" name="isbn" value="<?php echo trim($_GET["isbn"]); ?>"/>
                            <p>Are you sure you want to delete this book record?</p>
                            <p>
                                <input type="submit" value="Yes" class="btn btn-danger">
                                <a href="lib_books.php" class="btn btn-secondary">No</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
