<?php
$link = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
mysqli_select_db($link, 'cwurl');
 
// Define variables and initialize with empty values
$isbn = $title = $author = $pages = "";
$isbn_err = $title_err = $author_err = $pages_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate name
    $input_isbn = trim($_POST["isbn"]);
    if(empty($input_isbn)){
        $isbn_err = "Please enter the ISBN.";     
    } elseif(!ctype_digit($input_isbn)){
        $isbn_err = "Please enter a positive integer value.";
    } else{
        $isbn = $input_isbn;
    }
    
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
    
    if(empty($isbn_err) && empty($title_err) && empty($author_err) && empty($pages_err)){
        $sql = "INSERT INTO books3 (isbn, title, author, pages) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($link, $sql)){
            $param_isbn = $isbn;
            $param_title = $title;
            $param_author = $author;
            $param_pages = $pages;

            mysqli_stmt_bind_param($stmt, "issi", $param_isbn, $param_title, $param_author, $param_pages);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records created successfully. Redirect to landing page
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
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Record</title>
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
                    <h2 class="mt-5">Create Record</h2>
                    <p>Please fill this form and submit to add book to the database.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control <?php echo (!empty($isbn_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $isbn; ?>">
                            <span class="invalid-feedback"><?php echo $isbn_err;?></span>
                        </div>
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
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="lib_books.php" class="btn btn-secondary ml-2">Cancel</a>
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
