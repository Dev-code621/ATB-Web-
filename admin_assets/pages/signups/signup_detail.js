var pageviews = [
    // ['2019-06-01', 1100],
    // ['2019-06-02', 1300],
    // ['2019-06-03', 1800],
    // ['2019-06-04', 2000],
    // ['2019-06-05', 1150],
    // ['2019-06-06', 1123],
    // ['2019-07-06', 1623],
    // ['2019-07-02', 1123],
    // ['2019-07-07', 1610],
    // ['2019-06-08', 1050],
    // ['2019-06-09', 1380],
    // ['2019-06-10', 1560],
    // ['2019-06-11', 1600],
    // ['2019-06-12', 1790],
    // ['2019-06-13', 1710],
    // ['2019-06-14', 1750],
    // ['2019-06-15', 1850],
    // ['2019-06-16', 2250],
    // ['2019-06-17', 1450],
    // ['2019-06-18', 2150],
    // ['2019-06-19', 1750],



];

var initAjax = function(startTimeUnixValue, endTimeUnixValue) {

    App.blockUI({target:'#chart_portlet'});
    // App.startPageLoading({target:'#chart_portlet'});
    $.ajax({
        type: "POST",
        data: {
            userId:userId,
            start:startTimeUnixValue,
            end:endTimeUnixValue
        },
        cache: false,
        url: '../ajax_get_login_chart',
        success: function (res) {
            App.unblockUI('#chart_portlet');
            // App.stopPageLoading();
            pageviews  = JSON.parse(res);
            initChart();
        },
        error: function (res, ajaxOptions, thrownError) {
            // App.stopPageLoading();
            App.unblockUI('#chart_portlet');
        }
    });

    //

};
var initDashboardDaterange = function() {
    if (!jQuery().daterangepicker) {
        return;
    }

    var datePicker = $('#login_interaction_date_range');
    datePicker.daterangepicker({
        "ranges": {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'Last 30 Days': [moment().subtract('days', 29), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
        },
        "locale": {
            "format": "MM/DD/YYYY",
            "separator": " - ",
            "applyLabel": "Apply",
            "cancelLabel": "Cancel",
            "fromLabel": "From",
            "toLabel": "To",
            "customRangeLabel": "Custom",
            "daysOfWeek": [
                "Su",
                "Mo",
                "Tu",
                "We",
                "Th",
                "Fr",
                "Sa"
            ],
            "monthNames": [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December"
            ],
            "firstDay": 1
        },
        //"startDate": "11/08/2015",
        //"endDate": "11/14/2015",
        opens: (App.isRTL() ? 'right' : 'left'),
    }, function(start, end, label) {
        if (datePicker.attr('data-display-range') != '0') {
            datePicker.html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            initAjax(start.unix(), end.unix());
        }
    });
     if (datePicker.attr('data-display-range') != '0') {
        datePicker.html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
    }

    datePicker.show();

    // do ajax with timestamp
    initAjax(moment().subtract('days', 29).unix(), moment().unix());
};

var initChart = function() {

         var plot = $.plot($("#chart_4"),

             [{
            data: pageviews,
            label: "Login Access",
            lines: {
                lineWidth: 1
            },
            shadowSize: 0

        }, {
            data: pageviews,
            points: {
                show: true,
                fill: true,
                radius: 5,
                fillColor: "#A3A0FB",
                lineWidth: 3
            },
            color: '#fff',
            shadowSize: 0
        }], {
            series: {
                lines: {
                    show: true,
                    lineWidth: 1,
                    fill: true,
                    fillColor: {
                        colors: [{
                            opacity: 0.1
                        }, {
                            opacity: 1.5
                        }]
                    }
                },
                points: {
                    show: true,
                    radius: 4,
                    lineWidth: 1
                },
                shadowSize: 2
            },
            grid: {
                hoverable: true,
                clickable: true,
                tickColor: "#eee",
                borderColor: "#eee",
                borderWidth: 1
            },
            colors: ["#A3A0FB"],
            xaxis: {

                tickDecimals: 0,
                mode: "categories",
                font: {
                    lineHeight: 10,
                    style: "normal",
                    variant: "small-caps",
                    color: "#6F7B8A"
                }
            },
            yaxis: {
                ticks: 4,
                tickDecimals: 0,
                tickColor: "#eee",
                font: {
                    lineHeight: 10,
                    style: "normal",
                    variant: "small-caps",
                    color: "#6F7B8A"
                }
            },
        });

        function showTooltip(x, y, xVal, yVal) {
            $('<div id="tooltip" class="chart-tooltip">' + xVal + " : " + yVal + '<\/div>').css({
                position: 'absolute',
                display: 'none',
                top: y - 40,
                left: x - 40,
                border: '0px solid #ccc',
                padding: '2px 6px',
                'background-color': '#fff'
            }).appendTo("body").fadeIn(200);
        }

        var previousPoint = null;
        $("#chart_4").bind("plothover", function(event, pos, item) {
            $("#x").text(pos.x.toFixed(2));
            $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint != item.dataIndex) {
                    previousPoint = item.dataIndex;

                    $("#tooltip").remove();
                    var x = item.datapoint[0].toFixed(2),
                        y = item.datapoint[1].toFixed(2);

                    showTooltip(item.pageX, item.pageY, pageviews[item.datapoint[0]][0], item.datapoint[1] + ' visits');
                }
            } else {
                $("#tooltip").remove();
                previousPoint = null;
            }
        });
    };

if (App.isAngularJsApp() === false) {

    initDashboardDaterange();
    initChart();
}