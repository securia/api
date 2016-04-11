<?php
require __DIR__ . '/../config.php';
global $globalConfig;
?>
<html>
    <head>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <script type="application/javascript">
            var formatTime = function(unixTimestamp) {
                var dt = new Date(unixTimestamp * 1000);

                var date = dt.getDate();
                var month = dt.getMonth();
                var year = dt.getFullYear();

                if (date < 10)
                    date = '0' + date;

                if (month < 10)
                    month = '0' + month;

                var hours = dt.getHours();
                var minutes = dt.getMinutes();
                var seconds = dt.getSeconds();

                // the above dt.get...() functions return a single digit
                // so I prepend the zero here when needed
                if (hours < 10)
                    hours = '0' + hours;

                if (minutes < 10)
                    minutes = '0' + minutes;

                if (seconds < 10)
                    seconds = '0' + seconds;

                return year + "-" + month + "-" + date + " " + hours + ":" + minutes + ":" + seconds;
            }

            $( document ).ready(function() {
                $("#get_reports").click(function (event) {

                    // Stop form from submitting normally
                    //event.preventDefault();
                    var dataToSend = {};
                    dataToSend.user_id = $('#user_id').val();
                    dataToSend.from_date = $('#from_date').val();
                    dataToSend.to_date = $('#to_date').val();
                    $.post("<?php echo $globalConfig['apiUrl'];?>admin/1.0/call/getReports", dataToSend)
                        .done(function (data) {
                            var str = '<tr>'+
                                '<th>Sr No</th>'+
                                '<th>User Name</th>'+
                                '<th>Doctor</th>'+
                                '<th>Call Time</th>'+
                                '<th>Location Address</th>'+
                                //'<th>Comment</th>'+
                                '<th>Location</th>'+
                                '<th>Recorded At</th>'+
                                '</tr>';
                            $('#call_records').html(str);
                            $(data.data.calls).each(function( index, row ) {
                                str = '<tr><td>'+(index + 1)+'</td>'+
                                    '<td>'+row.user_name+'</td>'+
                                    '<td>'+row.doctor_name+', '+row.doctor_area+'</td>'+
                                    '<td>'+formatTime(row.call_time)+'</td>'+
                                    '<td>'+row.location_address+'</td>'+
                                    //'<td>'+row.comment+'</td>'+
                                    '<td><a target="_blank" href="https://www.google.co.in/maps/place/'+row.location[0]+','+row.location[1]+'">'+row.location[0]+','+row.location[1]+'</a></td>'+
                                    '<td>'+formatTime(row.created_at)+'</td></tr>'
                                $('#call_records').append(str);
                            });
                        });
                });
            });
        </script>
    </head>
    <body>
    <?php

        $inputs = array(
            'user_id' => '',
            'from_date' => date('Y-m-d', time()),
            'to_date' => date('Y-m-d', time())
        );
    ?>
        <h1 style="text-align: center">Securia Pharma</h1>
        <h4 style="text-align: center">Employee Call Reports</h4>
        <table align="center">
            <tr>
                <td>
                    <select name="user_id" id="user_id">
                        <option value="">All</option>
                        <option value="56f94060de97203f6d363cc3">Mr. Sachin Shevate</option>
                    </select>
                </td>
                <td>
                    <input type="date" id="from_date" placeholder="From Date" name="from_date" value="<?php echo $inputs['from_date'];?>" />
                </td>
                <td>
                    <input type="date" id="to_date" placeholder="To Date" name="to_date" value="<?php echo $inputs['to_date'];?>"/>
                </td>
                <td>
                    <input type="button" id="get_reports" name="get_reports" value="Submit" />
                </td>
            </tr>
        </table>

        <table id="call_records" align="center" border="1">

        </table>
    </body>
</html>
