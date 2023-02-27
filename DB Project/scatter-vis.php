<?php 
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || ($_SESSION["title"] !== 'c' && $_SESSION["title"] !== 'l')){
  header("location: permissions.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<meta charset="utf-8">

<head>
    <title>Rating v Page Count</title>
    <script src="https://d3js.org/d3.v4.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        .primaryCircle:hover {
            filter: brightness(70%);
        }

        .myTooltip {
            position: absolute;
            min-width: 70px;
            border: 1px solid #6F257F;
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
        }

        .btn-secondary:focus,
        .btn-secondary.focus {
            border-color: #6265e4 !important;
            background-color: #3498DB !important;
            box-shadow: 0 0 0 0 rgba(91, 194, 194, 0.5)
        }

        .btn-secondary.disabled,
        .btn-secondary:disabled {
            color: #212529;
            background-color: #7cc;
            border-color: #5bc2c2
        }
    </style>

</head>

<body>
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
			<?php
			if ($_SESSION["title"] == 'c'){
				echo '<a class="navbar-brand" href="cust_books.php">Return</a>';
			} else{
				echo '<a class="navbar-brand" href="lib_books.php">Return</a>';
			}
			?>
			</div>
		</nav>

    <script>
        // set the dimensions and margins of the graph
        var margin = { top: 45, right: 130, bottom: 220, left: 80 },
            width = window.innerWidth - margin.left - margin.right,
            height = window.innerHeight - margin.top - margin.bottom;

        //if (window.innerHeight < 1500)
        //    height = 400;

        var barPadding = 3;

        // set the ranges
        var x = d3.scaleLinear()
            .range([0, width]);
        var y = d3.scaleLinear()
            .range([height, 0]);

        // append the svg object to the body of the page
        // append a 'group' element to 'svg'
        // moves the 'group' element to the top left margin
        var svg = d3.select("body")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        var allYears = [];
        var yearsDict = {};
        var years = [];

        var currentMonth = 1;
        var monthsDict = {
            "January": 1,
            "February": 2,
            "March": 3,
            "April": 4,
            "May": 5,
            "June": 6,
            "July": 7,
            "August": 8,
            "September": 9,
            "October": 10,
            "November": 11,
            "December": 12
        }

        var maxPages;
        var maxRatings;

        function getObjKey(obj, value) {
            return Object.keys(obj).find(key => obj[key] === value);
        }

        d3.json("getalldata.php", function (error, ratingData) {
            if (error) throw error;

            const values = [
                "RatingDist1",
                "RatingDist2",
                "RatingDist3",
                "RatingDist4",
                "RatingDist5"
            ];
            const percentTotals = {};

            ratingData.forEach(function (d) {
                d.Rating = parseFloat(d.Rating);

                d.year = parseInt(d.PublishYear);
                if (!yearsDict[d.year])
                    yearsDict[d.year] = 1;
                else
                    yearsDict[d.year] += 1;

                d.month = parseInt(d.PublishMonth);
                d.day = parseInt(d.PublishDay);

                d.RatingDist1 = parseInt(d.RatingDist1);
                d.RatingDist2 = parseInt(d.RatingDist2);
                d.RatingDist3 = parseInt(d.RatingDist3);
                d.RatingDist4 = parseInt(d.RatingDist4);
                d.RatingDist5 = parseInt(d.RatingDist5);
                d.RatingDistTotal = parseInt(d.RatingDistTotal);

                d.PagesNumber = parseInt(d.PagesNumber);
            });

            // append the rectangles for the bar chart
            var makeChart = function (data, isDistribution) {
                // transition
                var t = d3.transition()
                    .duration(750);

                maxPages = d3.max(data, d => +d.PagesNumber);
                x.domain([0, maxPages]);

                maxRatings = d3.max(data, d => +d.RatingDistTotal);
                y.domain([0, 5]);

                //ENTER
                svg.append("g")
                    .selectAll("dot")
                    .data(data)
                    .enter()
                    .append("circle")
                    .attr("class", "primaryCircle")
                    .attr("cx", function (d) { return x(d.PagesNumber); })
                    .attr("cy", function (d) { return y(d.Rating); })
                    .style("fill", function (d) { return "#3498DB" })
                    .attr("r", function (d) { return 3 })
                    .on("mouseover", function (d) {
                        myTooltip.style("display", null);
                        d3.select(this).transition()
                            .duration(1)
                            .attr("r", 8);
                    })
                    .on("mouseout", function () {
                        myTooltip.style("display", "none");
                        d3.select(this).transition()
                            .duration(1)
                            .attr("r", 3);
                    })
                    .on("mousemove", function (d) {
                        myTooltip
                            .style("left", d3.event.pageX + 10 + "px")
                            .style("top", d3.event.pageY - 30 + "px")
                            .style("display", "inline-block")
                            .html(function () {
                                if (d.Name.length > 45)
                                    return ((d.Name.substring(0, 45).concat("...")) + "<br>" 
                                    + "by " + d.Authors + "<br>" 
                                    + d.PagesNumber + " Pages" + "<br>" 
                                    + d.Rating.toFixed(2) + " Rating");
                                else
                                    return ((d.Name) + "<br>" 
                                    + "by " + d.Authors + "<br>" 
                                    + d.PagesNumber + " Pages" + "<br>" 
                                    + d.Rating.toFixed(2) + " Rating");
                            });
                    });
            }

            function filterLimit(item) {
                if (item.PagesNumber > 20 && 
                (item.RatingDistTotal > 300000 || (item.RatingDistTotal > 100000 && item.Rating < 3.8) || 
                (item.RatingDistTotal > 10000 && item.Rating < 3.5) || 
                (item.RatingDistTotal > 1000 && item.Rating < 3.2)))
                    return true;
                return false;
            }

            function filteredData() {
                filtered = ratingData.filter(filterLimit)
                return filtered;
            }

            makeChart(filteredData(), true);

            svg.append("g")
                .call(d3.axisLeft(y).tickFormat(d3.format(".1f")))
                .transition().duration(2000)
                .attr("class", "y-axis")
                .attr("transform", "translate(0,0)");

            svg.append("g")
                .attr("class", "x-axis")
                .attr("transform", "translate(0," + height + ")")
                .call(d3.axisBottom(x));

            svg.append("text")
                .attr("x", 0 - 2 * height / 3)
                .attr("y", 0 - 2 * margin.left / 3)
                .attr("class", "y-label")
                .style("font-size", "14px")
                .text("Rating")
                .attr("transform", "rotate(-90)");

            svg.append("text")
                .attr("class", "chartTitle")
                .attr("text-anchor", "middle")
                .attr('x', window.innerWidth / 2 - 60)
                .attr('y', 0)
                .attr('font-weight', "bold")
                .text("Page Number vs. Rating in Books");

            svg.append("text")
                .attr("x", (width / 2))
                .attr("y", height + margin.bottom / 3)
                .attr("class", "x-label")
                .attr("text-anchor", "middle")
                .style("font-size", "14px")
                .text("Pages");

        });

        var myTooltip = d3.select("body")
            .append("div")
            .attr("id", "myTooltip")
            .attr("class", "myTooltip")
            .style("display", "none")

        myTooltip.append("rect")
            .attr("width", 80)
            .attr("height", 20)
            .attr("fill", "purple")
            .style("opacity", 0.5);

        myTooltip.append("text")
            .attr("x", 40)
            .attr("dy", "1.2em")
            .style("text-anchor", "middle")
            .attr("font-size", "12px")
            .attr("font-weight", "bold");

    </script>

</body>
</html>
