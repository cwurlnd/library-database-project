<?php 
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["title"] !== 'c' && $_SESSION["title"] !== 'l'){
  header("location: permissions.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Top Rated by Year</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Helvetica", sans-serif;
        }

        text {
            text-anchor: middle;
        }

        .chartTitle {
            font-family: "Times New Roman", serif;
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

    <div id="dropdown_container"></div>
    <button id="AnimateButton" onclick="style.display = 'none'" type="button">Animate</button>
    <button id="PauseButton" type="button">Pause</button>
    <script src="https://d3js.org/d3.v4.min.js"></script>
    <script>


        d3.json("getalldata.php", function (error, rawData) {
            var currentYear = '1900';

            var allYears = [];
            var yearsDict = {};
            var years = [];

            var paused = 1;
            var packMargin = 25;

            rawData.forEach(function (d) {
                d.year = parseInt(d.PublishYear);
                if (!yearsDict[d.year])
                    yearsDict[d.year] = 1;
                else
                    yearsDict[d.year] += 1;
                d.RatingDist5 = parseInt(d.RatingDist5);
            });

            for (const [key, value] of Object.entries(yearsDict)) {
                if (value >= 5)
                    years.push(parseInt(key));
            }

            years.sort();

            var width = window.innerWidth, height = window.innerHeight;

            var maxRating5 = d3.max(rawData, d => +d.RatingDist5);

            // size bubbles based on area

            var newScale = d3.scaleLinear()
                .domain([0, maxRating5])
                .range([1, 120]);

            var radiusScale = d3.scaleSqrt()
                .domain([0, maxRating5])
                .range([.1, 120])

            var fillColor = ["#61B87E", "#E5813C", "#3E97B6", "#DD82CB", "#9DC842"];

            var svg = d3.select("body").append("svg").attr("width", width).attr("height", height);

            var pack = d3.pack()
                .size([width, height - 3 * packMargin])
                .padding(1.5);

            redraw(filteredData());

            function animate() {
                var i = years.indexOf(currentYear);
                paused = 0;
                document.querySelector('#PauseButton').innerHTML = 'Pause';
                d3.interval(function () {
                    if (paused)
                        i = years.indexOf(currentYear);
                    else if (i < years.length) {
                        i++;
                        currentYear = years[i];
                        options.property("selected", function (d) { return d === currentYear });
                        redraw(filteredData());
                    }
                    else
                        stop();
                }, 2000);
            }

            function pauseAnimation() {
                if (!paused) {
                    paused = 1;
                    document.querySelector('#PauseButton').innerHTML = 'Resume';
                }
                else {
                    paused = 0;
                    document.querySelector('#PauseButton').innerHTML = 'Pause';
                }
            }

            function redraw(classes) {
                Object.keys(classes).forEach(function (k) {
                    classes[k].groupid = k;
                });

                var names = [];
                classes.forEach(function (d) {
                    names.push(d.Name);
                });

                // transition
                var t = d3.transition()
                    .duration(750);

                // hierarchy
                var h = d3.hierarchy({ children: classes })
                    .sum(function (d) { return newScale(+d.RatingDist5); })

                //JOIN
                var circle = svg.selectAll(".primaryCircle")
                    .data(pack(h).leaves(), function (d) { return d.data.Name; });

                var text = svg.selectAll(".circleLabel")
                    .data(pack(h).leaves(), function (d) { return d.data.Name; });

                var title = svg.selectAll(".chartTitle")
                    .data(pack(h).leaves(), function (d) { return d.data.Name; });

                var legendCircle = svg.selectAll(".legendCircle")
                    .data(pack(h).leaves(), function (d) { return d.data.Name; });

                var legendText = svg.selectAll(".legendText")
                    .data(pack(h).leaves(), function (d) { return d.data.Name; });

                //EXIT
                circle.exit()
                    .style("fill", "#808080")
                    .transition(t)
                    .attr("r", 1e-6)
                    .remove();

                text.exit()
                    .transition(t)
                    .attr("opacity", 1e-6)
                    .remove();

                title.exit()
                    .transition(t)
                    .attr("opacity", 1e-6)
                    .remove();

                legendCircle.exit()
                    .style("fill", "#808080")
                    .transition(t)
                    .attr("r", 1e-6)
                    .remove();

                legendText.exit()
                    .transition(t)
                    .attr("opacity", 1e-6)
                    .remove();

                //UPDATE
                circle
                    .transition(t)
                    .style("fill", "#3a403d")
                    .attr("r", function (d) { return d.r })
                    .attr("cx", function (d) { return d.x; })
                    .attr("cy", function (d) { return d.y + packMargin; })
                    .style("fill", function (d) { return fillColor[d.data.groupid] })

                text
                    .transition(t)
                    .attr("x", function (d) { return d.x; })
                    .attr("y", function (d) { return d.y + packMargin; });

                title
                    .transition(t)
                    .text("Top 5 Books with Most 5-Star Ratings, " + currentYear);

                legendCircle
                    .transition(t)
                    .style("fill", "#3a403d")
                    .attr('cx', function (d) { return 1 * width / 16; })
                    .attr('cy', function (d) { return d.data.groupid * 35 + 50; })
                    .style("fill", function (d) { return fillColor[d.data.groupid] })

                legendText
                    .transition(t)
                    .attr("x", function (d) { return 1 * width / 16 + 15; })
                    .attr("y", function (d) { return d.data.groupid * 35 + 55; })

                //ENTER
                circle.enter().append("circle")
                    .attr("class", "primaryCircle")
                    .attr("r", 1e-6)
                    .attr("cx", function (d) { return d.x; })
                    .attr("cy", function (d) { return d.y + packMargin; })
                    .style("fill", "#dddddd")
                    .transition(t)
                    .style("fill", function (d) { return fillColor[d.data.groupid] })
                    .attr("r", function (d) { return d.r });

                text.enter().append("text")
                    .attr("class", "circleLabel")
                    .attr("opacity", 1e-6)
                    .attr("x", function (d) { return d.x; })
                    .attr("y", function (d) { return d.y + packMargin; })
                    .text(function (d) {
                        if (d.r > d.data.Name.length * 4) { return d.data.Name; }
                        else return d.data.Name.substring(0, d.r / 4).concat("...");
                    })
                    .transition(t)
                    .attr("opacity", 1);

                title.enter().append("text")
                    .attr("class", "chartTitle")
                    .style("text-anchor", "middle")
                    .attr('x', window.innerWidth / 2)
                    .attr('y', 25)
                    .attr('font-weight', "bold")
                    .text("Top 5 Books with Most 5-Star Ratings, " + currentYear);

                legendCircle.enter().append("circle")
                    .attr("class", "legendCircle")
                    .attr('cx', function (d) { return 1 * width / 16; })
                    .attr('cy', function (d) { return d.data.groupid * 35 + 50; })
                    .style("fill", function (d) { return fillColor[d.data.groupid] })
                    .attr("r", 10);

                legendText.enter().append("text")
                    .attr("class", "legendText")
                    .attr("opacity", 1e-6)
                    .attr("x", function (d) { return 1 * width / 16 + 15; })
                    .attr("y", function (d) { return d.data.groupid * 35 + 55; })
                    .style("text-anchor", "start")
                    .text(function (d) {
                        if (d.data.Name.length > 40)
                            return d.data.Name.substring(0, 40).concat("...");
                        else
                            return d.data.Name;
                    })
                    .transition(t)
                    .attr("opacity", 1);
            }

            function filterYear(item) {
                if (item.year == currentYear)
                    return true;
                return false;
            }

            function filteredData() {
                rawData.sort(function (a, b) {
                    return d3.descending(a.RatingDist5, b.RatingDist5);
                });
                filtered = rawData.filter(filterYear).slice(0, 5);
                return filtered;
            }

            var dropDown = d3.select("#dropdown_container")
                .append("select")
                .attr("class", "selection")
                .attr("name", "years")

                .on('change', function () {
                    currentYear = eval(d3.select(this).property('value'));
                    redraw(filteredData());
                });
            var options = dropDown.selectAll("option")
                .data(years)
                .enter()
                .append("option");
            options.text(function (d) {
                return d;
            })
                .attr("value", function (d) {
                    return d;
                })
                .property("selected", function (d) {
                    return d === '1899'
                });

            d3.select('#AnimateButton').on('click', animate);
            d3.select('#PauseButton').on('click', pauseAnimation);
        });

    </script>
</body>

</html>
