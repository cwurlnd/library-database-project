<?php
    $username = ""; 
    $password = "";   

    $server = mysqli_connect('localhost', 'cwurl', 'pwpwpwpw');
    $connection = mysqli_select_db($server, 'cwurl');

		ini_set('memory_limit', '-1');
    $myquery = "
    SELECT Authors, ISBN, Name, PagesNumber, PublishMonth, PublishDay, 
    PublishYear, Rating, RatingDist1, RatingDist2, RatingDist3, RatingDist4,
    RatingDist5, RatingDistTotal FROM books5
    ";

    $query = mysqli_query($server, $myquery);

    if ( ! $query ) {
        echo mysqli_error();
        die;
    }

    $data = array();

    for ($x = 0; $x < mysqli_num_rows($query); $x++) {
        $data[] = mysqli_fetch_assoc($query);
    }

    echo json_encode($data);

    mysqli_close($server);
?>
