<div style="margin: 0 auto;border: 1px solid #D2D2D2;">
    <div style="color: #333333;padding: 25px;font-size: 14px;padding-top: 10px;min-height:150px">
        <?php
        echo 'Project => ' . $project_key . ' - ' . $api_env . '<br>';
        echo 'Error in => ' . $location . '<br>';
        echo 'Host => ' . $ip_address . '<br>';
        echo 'Time => ' . $time . '<br>';
        echo 'Error Title => ' . $error_title . '<br>';
        echo 'Error Info => <br>';
        echo $error_info;
        ?>
    </div>
</div>