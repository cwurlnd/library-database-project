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

<head>
    <title>Rating Totals</title>
    <script src="https://d3js.org/d3.v4.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
		
    <style>
        .bar:hover {
            filter: brightness(70%);
        }

        .tooltip {
            position: absolute;
            min-width: 70px;
            border: 1px solid #6F257F;
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
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
    <script>
        // set the dimensions and margins of the graph
        var margin = { top: 45, right: 130, bottom: 220, left: 80 },
            width = window.innerWidth - margin.left - margin.right,
            height = window.innerHeight - margin.top - margin.bottom;

        //if (window.innerHeight < 1500)
        //    height = 400;

        var barPadding = 3;

        // set the ranges
        var x = d3.scaleBand()
            .range([0, width])
            .padding(0.1);
        var y = d3.scaleLinear()
            .range([height, 0]);
        var z = d3.scaleOrdinal()
            .range(["#672328", "#a50026", "#f5f8ac", "#74c364", "#006837"]);


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
                d.rating = parseFloat(d.Rating);

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
            });

            // Scale the range of the data in the domains
            z.domain(values);

            // append the rectangles for the bar chart
            var makeChart = function (data, isDistribution) {
                svg.selectAll(".bar").remove();
                svg.selectAll(".y-label").remove();
                svg.selectAll(".y-axis").remove();
                svg.selectAll(".x-axis").remove();
                svg.selectAll(".chartTitle").remove();

                var stackedData = d3.stack()
                    .keys(values)
                    (data)

                var names = [];
                data.forEach(function (d) {
                    names.push(d.Name);
                });
                x.domain(names);

                maxRatings = d3.max(data, d => +d.RatingDistTotal);

                y.domain([0, maxRatings]);

                svg.append("g")
                    .call(d3.axisLeft(y).tickFormat(d3.format(".3s")))
                    .transition().duration(2000)
                    .attr("class", "y-axis")
                    .attr("transform", "translate(0,0)");

                svg.append("g")
                    .attr("class", "x-axis")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x).tickFormat(function (d, i) {
                        if (d.length > 25)
                            return d.substring(0, 22).concat("...");
                        else
                            return d;
                    }))
                    .selectAll("text")
                    .attr("text-anchor", "end")
                    .attr("transform", "rotate(-55)");

                svg.append("text")
                    .attr("x", 0 - 2 * height / 3)
                    .attr("y", 0 - 2 * margin.left / 3)
                    .attr("class", "y-label")
                    .style("font-size", "14px")
                    .text("Ratings")
                    .attr("transform", "rotate(-90)");

                svg.append("text")
                    .attr("class", "chartTitle")
                    .attr("text-anchor", "middle")
                    .attr('x', window.innerWidth / 2 - 60)
                    .attr('y', -20)
                    .attr('font-weight', "bold")
                    .text("Most Popular Books Published in " + getObjKey(monthsDict, currentMonth));

                //append rectangles
                svg.append("g")
                    .selectAll("g")
                    // Enter in the stack data
                    .data(stackedData)
                    .enter().append("g")
                    .selectAll("rect")
                    // enter a second time
                    .data(function (d) { return d; })
                    .enter().append("rect")
                    .attr("x", function (d) { return x(d.data.Name) + barPadding / 2; })
                    .attr("width", x.bandwidth() - barPadding)
                    .attr("fill", function (d) { return z(getObjKey(d.data, d[1] - d[0])); })
                    .attr("class", "bar")
                    .on("mouseover", function () { tooltip.style("display", null); })
                    .on("mouseout", function () { tooltip.style("display", "none"); })
                    .on("mousemove", function (d) {
                        tooltip
                            .style("left", d3.event.pageX + 10 + "px")
                            .style("top", d3.event.pageY - 30 + "px")
                            .style("display", "inline-block")
                            .html(function () {
                                var stars = getObjKey(d.data, d[1] - d[0]);

                                if (d[1] - d[0] > 1000000) {
                                    if (d.data.Name.length > 45)
                                        return ((d.data.Name.substring(0, 45).concat("...")) + "<br>" 
                                        + stars.substring(stars.length - 1) + "-Star Ratings: " 
                                        + ((d[1] - d[0]) / 1000000).toFixed(2) + 'M');
                                    else
                                        return ((d.data.Name) + "<br>" 
                                        + stars.substring(stars.length - 1) + "-Star Ratings: " 
                                        + ((d[1] - d[0]) / 1000000).toFixed(2) + 'M');
                                }
                                else {
                                    if (d.data.Name.length > 45)
                                        return ((d.data.Name.substring(0, 45).concat("...")) + "<br>"
                                        + stars.substring(stars.length - 1) + "-Star Ratings: " 
                                        + ((d[1] - d[0]) / 1000).toFixed(2) + 'K');
                                    else
                                        return ((d.data.Name) 
                                        + "<br>" + stars.substring(stars.length - 1) + "-Star Ratings: " 
                                        + ((d[1] - d[0]) / 1000).toFixed(2) + 'K');
                                }
                            });
                    })
                    .attr("y", height)
                    .transition().duration(2000)
                    .attr("y", function (d) {
                        return y(d[1]);
                    })
                    .attr("height", function (d) { return y(d[0]) - y(d[1]) });


            }

            var legend = svg.append("g")
                .attr("font-family", "sans-serif")
                .attr("font-size", 10)
                .attr("text-anchor", "start")
                .selectAll("g")
                .data(values.slice().reverse())
                .enter().append("g")
                .attr("transform", function (d, i) { return "translate(60," + i * 18 + ")"; });

            legend.append("rect")
                .attr("x", width - 40)
                .attr("width", 18)
                .attr("height", 12)
                .attr("fill", z);

            legend.append("text")
                .attr("x", width - 19)
                .attr("y", 6)
                .attr("dy", "0.32em")
                .text(function (d) { return d.substring(d.length - 1).concat(" Star"); });

            function filteredData() {
                ratingData.sort(function (a, b) {
                    return d3.descending(a.RatingDistTotal, b.RatingDistTotal);
                });

                sorted = []
                var names = [];
                var count = 0;
                var start = 0;
                while (count < 10) {
                    for (var i = start; i < ratingData.length; i++) {
                        if (names.indexOf(ratingData[i].Name) < 0 && ratingData[i].PublishMonth == currentMonth) {
                            names.push(ratingData[i].Name);
                            sorted.push(ratingData[i]);
                            count++;
                            start = i + 1;
                            break;
                        }
                    }
                }

                sorted.sort(function (a, b) {
                    return d3.ascending(a.RatingDistTotal, b.RatingDistTotal);
                });
                return sorted;
            }

            makeChart(filteredData(), true);

            svg.append("text")
                .attr("x", (width / 2))
                .attr("y", height + 2 * margin.bottom / 3)
                .attr("class", "x-label")
                .attr("text-anchor", "middle")
                .style("font-size", "14px")
                .text("Title");

            var dropDown = d3.select("#dropdown_container")
                .append("select")
                .attr("class", "selection")
                .attr("name", "months")
                .on('change', function () {
                    currentMonth = monthsDict[d3.select(this).property('value')];
                    makeChart(filteredData(), true);
                });
            var options = dropDown.selectAll("option")
                .data(Object.keys(monthsDict))
                .enter()
                .append("option");
            options.text(function (d) {
                return d;
            })
                .attr("value", function (d) {
                    return d;
                })
                .property("selected", function (d) {
                    return d === '1'
                });
        });

        var tooltip = d3.select("body")
            .append("div")
            .attr("id", "tooltip")
            .attr("class", "tooltip")
            .style("display", "none")

        tooltip.append("rect")
            .attr("width", 80)
            .attr("height", 20)
            .attr("fill", "purple")
            .style("opacity", 0.5);

        tooltip.append("text")
            .attr("x", 40)
            .attr("dy", "1.2em")
            .style("text-anchor", "middle")
            .attr("font-size", "12px")
            .attr("font-weight", "bold");

    </script>

</body>
</html>
