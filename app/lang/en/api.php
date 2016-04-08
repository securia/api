<?php

return array(
    404 => 'Page not found.',
    405 => "Ooops...! Looks like we don't have what you are looking for",
    406 => "Please provide valid request method",

    // System errors
    100 => '%s',
    101 => 'Please contact administrator.',
    102 => 'Something went wrong.',
    103 => 'Constructor failed.',
    104 => 'Fatal Error - Try catch.',
    105 => 'Operation could not be performed.',

    //Standard Error Codes
    1000 => '%s', //All input validation error comes into this category
    1010 => 'Please log in to continue.',
    1020 => 'Invalid %s.',
    1030 => '%s is not active.',
    1040 => '%s required.',
    1050 => '%s failed.',
    1060 => '%s fetching failed.',
    1070 => '%s updating failed.',
    1080 => '%s removing failed.',
    1090 => '%s already exist.',
    1100 => '%s does not exist.',
    1110 => '%s sending failed.',
    1120 => 'Operation Could not performed.',
    1130 => 'Invalid %s format, We are supporting only %s file types.', //First %s (Image/Video/File/Audio) Second %s will be format (jpg,png)
    1140 => 'You are not authorize to access this api.',
    1150 => '%s linking failed.',
    1160 => 'Unable to add %s relationship between %s and %s.',
    1170 => 'Allowed only from command line.',
    1180 => 'Invalid session.',
    1190 => '%s not allowed on %s env.',


    //Standard Success Codes
    2000 => '%s',
    2010 => 'User logged in successfully.',
    2020 => 'User logged out successfully.',
    2030 => '%s saved successfully.',
    2040 => '%s fetched successfully.',
    2050 => '%s updated successfully.',
    2060 => '%s removed successfully.',
    2070 => '%s validated successfully.',
    2080 => '%s has been uploaded successfully.', //%s (Image/Video/File/Audio)
    2090 => '%s has been sent successfully.',
    2100 => '%s link has been sent successfully.',
    2110 => '%s has been recorded successfully.',
    2120 => '%s has been activated successfully.',
    2130 => '%s found.',
    2140 => '%s registered successfully.',
    2150 => '%s linked successfully.',
    2160 => '%s downloaded successfully.',
    2170 => '%s completed successfully.',
    2180 => '%s reset successfully.',
    2190 => '%s applied successfully.',
    2200 => '%s converted to %s successfully.',
    2210 => '%s cleared successfully.',
    2220 => '%s verification successful.',
    2230 => '%s flushed successful.',


    //App Specific Error Codes
    3000 => '%s',
    3010 => 'Insufficient coin balance',
    3020 => 'Unable to create %s',
    3030 => 'Facebook log in is required.',
    3040 => '%s upload failed.',
    3050 => '%s already made.',
    3060 => '%s verification failed.',

    3070 => 'Player information not found',
    3080 => 'Something went wrong. Please report the issue below if this error screen continues to appear',
    3090 => 'Cannot load puzzle. Please try again in a few minutes',
    3100 => 'Upgrade now to the latest version to get access to the latest new features!',

    //App Specific Success Codes
    4000 => '%s',
    4010 => 'puzzle(s) already downloaded or there is no such puzzle(s) available.',
    4020 => '%s upload successful.',

    //Laravel common functions error codes
    5000 => '%s',
    5010 => '%s failed.',
    5020 => 'Invalid %s.',
    5030 => '%s Expired.',
    5040 => '%s not found.',

    //Laravel common functions success codes
    6000 => '%s',
    6010 => '%s successful.',

    // Scripts codes
    9000 => 'Bootstrap data saved successfully.',
    9010 => 'Indexes saved successfully.',
    9020 => 'Indexes deleted successfully.',
    9050 => 'Mongo indexes saved successfully.',
    9060 => 'Mongo indexes deleted successfully.',

    9100 => 'Migration done successfully.',

    9200 => 'Puzzle lock checked successfully.',


);