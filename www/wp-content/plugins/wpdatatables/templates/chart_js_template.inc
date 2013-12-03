<script type="text/javascript" src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            [<?php echo $series_headers ?>],
            <?php echo $series_values ?>
        ]);

        var options = {
            title: "<?php echo $chart_title ?>"
            <?php if($hor_axis) { ?>, hAxis: {title: "<?php echo $hor_axis ?>", titleTextStyle: {color: 'red'}}<?php } ?>
            <?php if($ver_axis) { ?>, vAxis: {title: "<?php echo $ver_axis ?>", titleTextStyle: {color: 'red'}}<?php } ?>
        };

        var chart = new google.visualization.<?php echo $chart_type ?>Chart(document.getElementById('<?php echo $chart_container ?>'));
        chart.draw(data, options);
    }
</script>