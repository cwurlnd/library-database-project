<?php
$link = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
mysqli_select_db($link, 'cwurl');
 
// Define variables and initialize with empty values
$isbn = $title = $author = $pages = "";
$isbn_err = $title_err = $author_err = $pages_err = "";
 
// Processing form data when form is submitted
if(isset($_POST["isbn"]) && !empty($_POST["isbn"])){
    // Get hidden input value
    $isbn = $_POST["isbn"];
    
    $input_title = trim($_POST["title"]);
    if(empty($input_title)){
        $title_err = "Please enter a title.";     
    } else{
        $title = $input_title;
    }
    
    $input_author = trim($_POST["author"]);
    if(empty($input_author)){
        $author_err = "Please enter an author.";     
    } else{
        $author = $input_author;
    }
    
    $input_pages = trim($_POST["pages"]);
    if(empty($input_pages)){
        $pages_err = "Please enter the pages.";     
    } elseif(!ctype_digit($input_pages)){
        $pages_err = "Please enter a positive integer value.";
    } else{
        $pages = $input_pages;
    }
    
    // Check input errors before inserting in database
    if(empty($title_err) && empty($author_err) && empty($pages_err)){
        // Prepare an update statement
        $sql = "UPDATE books3 SET title=?, author=?, pages=? WHERE isbn=?";
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssii", $param_title, $param_author, $param_pages, $param_isbn);
            
            // Set parameters
            $param_title = $title;
            $param_author = $author;
            $param_pages = $pages;
            $param_isbn = $isbn;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to landing page
                header("location: lib_books.php");
                exit();
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
    if(isset($_GET["isbn"]) && !empty(trim($_GET["isbn"]))){
        // Get URL parameter
        $isbn =  trim($_GET["isbn"]);
        
        // Prepare a select statement
        $sql = "SELECT * FROM books3 WHERE isbn = ?";
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            $param_isbn = $isbn;

            mysqli_stmt_bind_param($stmt, "i", $param_isbn);
                  
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);
    
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    $title = $row["title"];
                    $author = $row["author"];
                    $pages = $row["pages"];
                } else{
                    header("location: cerror.php");
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
        header("location: cerror.php");
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
                    <h2 class="mt-5">Update Record</h2>
                    <p>Please edit the input values and submit to update the book record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control <?php echo (!empty($title_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $title; ?>">
                            <span class="invalid-feedback"><?php echo $title_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Author</label>
                            <input type="text" name="author" class="form-control <?php echo (!empty($author_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $author; ?>">
                            <span class="invalid-feedback"><?php echo $author_err;?></span>
                        </div>
                        <div class="form-group">
                            <label>Pages</label>
                            <input type="text" name="pages" class="form-control <?php echo (!empty($pages_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $pages; ?>">
                            <span class="invalid-feedback"><?php echo $pages_err;?></span>
                        </div>
                        <input type="hidden" name="isbn" value="<?php echo $isbn; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="lib_books.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
