<?php
global $bootstrap;
global $globalConfig;

// Admin default data
$bootstrap['mongo']['admins'] = array(
    array(
        'username' => 'admin',
        'password' => 'psc-admin@realmile',
        'email' => $globalConfig['projectKey'] . '.adm.' . $globalConfig['apiEnv'] . '@mailinator.com'
    ),
    array(
        'username' => 'realmile',
        'password' => 'psc-admin@realmile',
        'email' => $globalConfig['projectKey'] . '.real.' . $globalConfig['apiEnv'] . '@mailinator.com'
    ),
);

$deviceIndexes = array('platform', 'device_code', 'created_at', 'last_login_at', 'device_id');

$bootstrap['mongo']['counters'] = array(
    array('node' => 'device_player', 'id' => 0),
    array('node' => 'player_puzzle', 'id' => 0),
    array('node' => 'puzzle', 'id' => 0),
    array('node' => 'bonus_puzzle', 'id' => 0),
    array('node' => 'admin', 'id' => 0),
);

$indexOptions = array(
    'default' => array('background' => true),
    'unique' => array('unique' => true, 'dropDups' => true, 'background' => true)
);

// Mongo Database indexes
$bootstrap['mongo']['indexes'] = array(
    'ad_mediators' => array(
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'version' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'admins' => array(
        'username' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'email' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'api_analytics' => array(
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'appsflyer' => array(
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'version' => array('index_type' => -1, 'options' => $indexOptions['default']),
    ),
    'aws_settings' => array(
        'type' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'bonus_puzzles' => array(
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'puzzle_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'featured_start_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'featured_end_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'start_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'end_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'is_active' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'is_featured' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'is_deleted' => array('index_type' => 1, 'options' => $indexOptions['default']),
    ),
    'daily_average_score' => array(
        'id' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'dashboard' => array(
        'start_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'end_date' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'devices' => array(
        'device_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'device_code' => array('index_type' => 1, 'options' => $indexOptions['unique']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'player_id' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'fb_id' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'device_model' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'disputes' => array(
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'fb_id' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'is_resolved' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'guest_puzzles' => array(
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'instances' => array(
        'ip_address' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'instance_stats' => array(
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'platform_settings' => array(
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'version' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'type' => array('index_type' => 1, 'options' => $indexOptions['default']),
    ),
    'player_bonus_puzzles' => array(
        'player_puzzle_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'device_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'puzzle_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'is_completed' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'is_cleared' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'player_daily_puzzles' => array(
        'player_puzzle_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'device_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'is_completed' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'is_cleared' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'players' => array(
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'fb_id' => array('index_type' => -1, 'options' => $indexOptions['unique']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'player_references' => array(
        'referral_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'joined_at' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'is_rewarded' => array('index_type' => 1, 'options' => $indexOptions['default']),
    ),
    'purchases' => array(
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'device_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'puzzles' => array(
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'puzzle_id' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'puzzles_completed_counts' => array(
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'retention_stats' => array(
        'install_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'days' => array('index_type' => 1, 'options' => $indexOptions['default'])
    ),
    'system_errors' => array(
        'error_title' => array('index_type' => 1, 'options' => $indexOptions['default']),
    ),
    'transactions' => array(
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'device_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'platform' => array('index_type' => 1, 'options' => $indexOptions['default']),
        'created_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'weekly_tournament' => array(
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'count' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'updated_at' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'weekly_tournament_archived' => array(
        'start_timestamp' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'end_timestamp' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
    'best_times' => array(
        'for_date' => array('index_type' => -1, 'options' => $indexOptions['default']),
        'player_id' => array('index_type' => -1, 'options' => $indexOptions['default'])
    ),
);

// Migration Config
$bootstrap['migration']['onlyCmd'] = false;
$bootstrap['migration']['csvPath'] = 'temp/';
$bootstrap['migration']['playersGroupLimit'] = 10000;
$bootstrap['migration']['devicesGroupLimit'] = 10000;
$bootstrap['migration']['puzzlesGroupLimit'] = 10000;
$bootstrap['migration']['playerDevicesGroupLimit'] = 1000;
$bootstrap['migration']['playerPuzzlesGroupLimit'] = 1000;

$bootstrap['migration']['maxLimitEnv'] = array('local', 'dev');
$bootstrap['migration']['maxLimitPlayers'] = 200000;
$bootstrap['migration']['maxLimitDevices'] = 200000;
$bootstrap['migration']['maxLimitPuzzles'] = 10000;
$bootstrap['migration']['maxLimitPlayerDevices'] = 200000;
$bootstrap['migration']['maxLimitPlayerPuzzles'] = 1000000;

$bootstrap['migration']['deviceColumns'] = array(
    0 => 'device_id',
    1 => 'device_type',
    2 => 'device_code',
    3 => 'coins_to_credit',
    4 => 'amount_spent',
    5 => 'model',
    6 => 'fb_shared',
    7 => "UNIX_TIMESTAMP(STR_TO_DATE(last_login, '%Y-%m-%d %h:%i:%s')) as last_login",
    8 => 'twitted',
    9 => 'puzzles_downloaded',
    10 => 'puzzles_completed',
    11 => 'consecutive_days_count',
    12 => 'ip_address',
    13 => 'balance',
    14 => 'ads_disabled',
    15 => "UNIX_TIMESTAMP(STR_TO_DATE(joined_date, '%Y-%m-%d %h:%i:%s')) as joined_date",
);

$bootstrap['migration']['playerColumns'] = array(
    0 => 'players_player_id',
    1 => 'fb_id',
    2 => 'name',
    3 => 'email',
    4 => "UNIX_TIMESTAMP(STR_TO_DATE(birthday, '%Y-%m-%d')) as birthday",
    5 => 'gender',
);

$bootstrap['migration']['puzzleColumns'] = array(
    0 => 'game_id',
    1 => "DATE_FORMAT(for_date, '%Y%m%d') as for_date",
);

$bootstrap['migration']['playerDeviceColumns'] = array(
    0 => 'players_player_id',
    1 => 'device_id',
    2 => "UNIX_TIMESTAMP(STR_TO_DATE(joined_date, '%Y-%m-%d %h:%i:%s')) as joined_date",
);

$bootstrap['migration']['playerPuzzleColumns'] = array(
    0 => 'player_game_id',
    1 => 'players_player_id',
    2 => 'games_game_id',
    3 => 'p.show_wrong',
    4 => 'is_completed',
    5 => 'time_spent',
    6 => 'percentage_complete',
    7 => 'p.best_time',
    8 => 'is_purged',
    9 => "UNIX_TIMESTAMP(STR_TO_DATE(downloaded_on, '%Y-%m-%d %h:%i:%s')) as downloaded_on",
    10 => "UNIX_TIMESTAMP(STR_TO_DATE(last_saved, '%Y-%m-%d %h:%i:%s')) as last_saved",
);

$bootstrap['settings']['hardcoded']['4.6']['windows'] = $bootstrap['settings']['hardcoded']['4.6']['ios'] = $bootstrap['settings']['hardcoded']['4.6']['android'] =
$bootstrap['settings']['hardcoded']['4.6']['kindle'] = $bootstrap['settings']['hardcoded']['4.6']['facebook'] = array(
    'system_settings' => array(
        'first_time_user' => 15,
        'todays_puzzle_download' => 0,
        'archived_puzzle_download' => 10,
        'show_wrong' => 2,
        'ten_letter_blast' => 1,
        'reveal_word' => 1,
        'solving_puzzle' => 0,
        'solving_puzzle_under_twenty' => 0,
        'solving_puzzle_under_ten' => 0,
        'fb_login_reward' => 0,
        'weekend_download_discount' => 0,
        'download_cost' => [9, 10, 10, 10, 10, 10, 9],
        'discounted_cost' => 9,
        'in_app_notification' => false,
    ),
    'loading_texts' => array(
        'Play new puzzles daily at 10pm ET',
        'If you’re stuck on a clue, try using hints!',
        'Play 500+ more puzzles in the calendar',
        'A fun theme for every day of the week',
        'We NEVER post to Facebook on your behalf'
    ),
);
$bootstrap['settings']['hardcoded']['4.7'] = $bootstrap['settings']['hardcoded']['4.6'];
$bootstrap['settings']['hardcoded']['4.8'] = $bootstrap['settings']['hardcoded']['4.7'];

unset($bootstrap['settings']['hardcoded']['4.8']['ios']['system_settings']['weekend_download_discount']);
unset($bootstrap['settings']['hardcoded']['4.8']['android']['system_settings']['weekend_download_discount']);
unset($bootstrap['settings']['hardcoded']['4.8']['kindle']['system_settings']['weekend_download_discount']);
unset($bootstrap['settings']['hardcoded']['4.8']['facebook']['system_settings']['weekend_download_discount']);

$bootstrap['settings']['hardcoded']['4.9'] = $bootstrap['settings']['hardcoded']['4.8'];

$bootstrap['settings']['hardcoded']['4.9']['ios']['system_settings']['h2h_enabled'] = true;
$bootstrap['settings']['hardcoded']['4.9']['ios']['system_settings']['h2h_random_puzzle_download'] = 0;
$bootstrap['settings']['hardcoded']['4.9']['ios']['system_settings']['h2h_themed_puzzle_download'] = 3;
$bootstrap['settings']['hardcoded']['4.9']['ios']['system_settings']['h2h_puzzle_time_limit'] = 15;
$bootstrap['settings']['hardcoded']['4.9']['ios']['system_settings']['h2h_puzzle_reward'] = 3;

$bootstrap['settings']['hardcoded']['4.9']['windows'] = $bootstrap['settings']['hardcoded']['4.9']['ios'];

$bootstrap['settings']['hardcoded']['4.10'] = $bootstrap['settings']['hardcoded']['4.9'];

$bootstrap['appsflyer']['tabSequence'] = array('iOS', 'Android', 'Kindle', 'Windows');

$bootstrap['appsflyer']['hardcoded']['4.6']['ios'] = $bootstrap['appsflyer']['hardcoded']['4.6']['windows'] = array(
    'open_unique' => 0.10,
    'd7_open_unique' => 0.10
);

$bootstrap['appsflyer']['hardcoded']['4.6']['android'] = $bootstrap['appsflyer']['hardcoded']['4.6']['kindle'] = array(
    'puzzle_solved' => 0.07,
    'd7_puzzle_solved' => 0.07,
    'open_unique' => 0.07,
    'd7_open_unique' => 0.07
);

$bootstrap['appsflyer']['hardcoded']['4.7']['android'] = $bootstrap['appsflyer']['hardcoded']['4.7']['kindle'] = array(
    'open_unique' => 0.07,
    'd7_open_unique' => 0.07
);

$bootstrap['appsflyer']['hardcoded']['4.10'] = $bootstrap['appsflyer']['hardcoded']['4.9'] = $bootstrap['appsflyer']['hardcoded']['4.8'] = $bootstrap['appsflyer']['hardcoded']['4.7'] = $bootstrap['appsflyer']['hardcoded']['4.6'];

$bootstrap['admediator']['events'] = array(
    'APP_OPEN' => 'App Open',
    'ENDPUZZLE_FULLSCREEN' => 'End Puzzle Full Screen',
    'H2H_ENDPUZZLE_FULLSCREEN' => 'H2H End Puzzle Full Screen',
    'TODAY_RESCUE' => 'Today Rescue',
    'HINT_RESCUE' => 'Hint Rescue',
    'VIDEO_HINT' => 'Video Hint',
    'ARCHIVE_RESCUE' => 'Archive Rescue',
    'DIRECT_PLAY_VIDEO' => 'Direct Play Video',
    'OFFER_WALL' => 'Offer Wall',
    'BANNER_ADS' => 'Banner Ads',
);

$bootstrap['admediator']['vendors'] = array(
    'AdColony' => array('name' => 'AdColony'),
    'Chartboost' => array('name' => 'Chartboost'),
    'Flurry' => array('name' => 'Flurry'),
    'iAd' => array('name' => 'iAd'),
    'MediaBrix' => array('name' => 'MediaBrix'),
    'TapJoy' => array('name' => 'TapJoy'),
    'FBAudienceNetwork' => array('name' => 'FB Audience Network'),
    'Unlockable' => array('name' => 'Unlockable'),
    'Vungle' => array('name' => 'Vungle'),
    'Amazon' => array('name' => 'Amazon'),
    'Google' => array('name' => 'Google'),
    'SuperSonic' => array('name' => 'Super Sonic'),
    'AppLovin' => array('name' => 'AppLovin'),
    'TapSense' => array('name' => 'Tap Sense'),
    'FBInvite' => array('name' => 'FB Invite'),
);

$bootstrap['admediator']['tabSequence'] = array('iOS', 'Android', 'Kindle', 'Windows');

$bootstrap['admediator']['hardcoded']['4.6']['ios'] = array(
    'events' => array(
        'ENDPUZZLE_FULLSCREEN' => array(
            'vendors' => array(
                'Chartboost' => array('coin' => 0, 'order' => 1, 'retry' => 0, 'coins_editable' => false),
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'FBAudienceNetwork' => array('coin' => 0, 'order' => 2, 'retry' => 0, 'coins_editable' => false),
                'Google' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'Applovin' => array('coin' => 0, 'order' => 7, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'BONUS_ENDPUZZLE_FULLSCREEN' => array(
            'vendors' => array(
                'Chartboost' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'Applovin' => array('coin' => 0, 'order' => 7, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'ARCHIVE_RESCUE' => array(
            'vendors' => array(
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'HINT_RESCUE' => array(
            'vendors' => array(
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'DIRECT_PLAY_VIDEO' => array(
            'vendors' => array(
                'AdColony' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => true,),
                'Vungle' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => true,),
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'Chartboost' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => true,),
                'SuperSonic' => array('coin' => 0, 'order' => 5, 'retry' => 0, 'coins_editable' => false),
                'Applovin' => array('coin' => 0, 'order' => 5, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'OFFER_WALL' => array(
            'vendors' => array(
                'TapJoy' => array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false),
                'SuperSonic' => array('coin' => 0, 'order' => 1, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
        'BANNER_ADS' => array(
            'vendors' => array(
                'Flurry' => array('coin' => 0, 'order' => 1, 'retry' => 0, 'coins_editable' => false),
                'iAd' => array('coin' => 0, 'order' => 2, 'retry' => 0, 'coins_editable' => false),
                'FBAudienceNetwork' => array('coin' => 0, 'order' => 3, 'retry' => 0, 'coins_editable' => false),
            ),
        ),
    ),
);

$bootstrap['admediator']['hardcoded']['4.6']['android'] = $bootstrap['admediator']['hardcoded']['4.6']['ios'];
$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ENDPUZZLE_FULLSCREEN']['vendors']['AppLovin'] = $bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ENDPUZZLE_FULLSCREEN']['vendors']['Applovin'];
$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['BONUS_ENDPUZZLE_FULLSCREEN']['vendors']['AppLovin'] = $bootstrap['admediator']['hardcoded']['4.6']['android']['events']['BONUS_ENDPUZZLE_FULLSCREEN']['vendors']['Applovin'];
$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['DIRECT_PLAY_VIDEO']['vendors']['AppLovin'] = $bootstrap['admediator']['hardcoded']['4.6']['android']['events']['DIRECT_PLAY_VIDEO']['vendors']['Applovin'];
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ENDPUZZLE_FULLSCREEN']['vendors']['Applovin']);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['BONUS_ENDPUZZLE_FULLSCREEN']['vendors']['Applovin']);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['DIRECT_PLAY_VIDEO']['vendors']['Applovin']);

$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ARCHIVE_RESCUE']['vendors']['FBInvite'] = array('coin' => 20, 'order' => 1, 'retry' => 0, 'coins_editable' => true);
$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['HINT_RESCUE']['vendors']['FBInvite'] = array('coin' => 20, 'order' => 1, 'retry' => 0, 'coins_editable' => true);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ENDPUZZLE_FULLSCREEN']['vendors']['Vungle']);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ENDPUZZLE_FULLSCREEN']['vendors']['Applovin']);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['BANNER_ADS']['vendors']['Flurry']);
unset($bootstrap['admediator']['hardcoded']['4.6']['android']['events']['BANNER_ADS']['vendors']['iAd']);
$bootstrap['admediator']['hardcoded']['4.6']['android']['events']['ARCHIVE_RESCUE']['vendors']['Google'] = array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false);

$bootstrap['admediator']['hardcoded']['4.6']['kindle'] = $bootstrap['admediator']['hardcoded']['4.6']['android'];
$bootstrap['admediator']['hardcoded']['4.6']['kindle']['events']['ENDPUZZLE_FULLSCREE   N']['vendors']['Amazon'] = array('coin' => 0, 'order' => 6, 'retry' => 0, 'coins_editable' => false);
unset($bootstrap['admediator']['hardcoded']['4.6']['kindle']['events']['BANNER_ADS']['vendors']['Google']);
$bootstrap['admediator']['hardcoded']['4.6']['kindle']['events']['BANNER_ADS']['vendors']['Amazon'] = array('coin' => 0, 'order' => 6, 'retry' => 0, 'coins_editable' => false);

$bootstrap['admediator']['hardcoded']['4.7']['android'] = $bootstrap['admediator']['hardcoded']['4.6']['android'];
$bootstrap['admediator']['hardcoded']['4.7']['kindle'] = $bootstrap['admediator']['hardcoded']['4.6']['kindle'];
$bootstrap['admediator']['hardcoded']['4.7']['ios'] = $bootstrap['admediator']['hardcoded']['4.6']['ios'];

$bootstrap['admediator']['hardcoded']['4.8']['ios'] = $bootstrap['admediator']['hardcoded']['4.7']['ios'];

$bootstrap['admediator']['hardcoded']['4.8']['android']['events']['APP_OPEN']['vendors']['TapJoy'] = array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false);
$bootstrap['admediator']['hardcoded']['4.8']['kindle']['events']['APP_OPEN']['vendors']['TapJoy'] = array('coin' => 0, 'order' => 0, 'retry' => 0, 'coins_editable' => false);
$bootstrap['admediator']['hardcoded']['4.8']['android']['events'] = array_merge($bootstrap['admediator']['hardcoded']['4.8']['android']['events'], $bootstrap['admediator']['hardcoded']['4.7']['android']['events']);
$bootstrap['admediator']['hardcoded']['4.8']['kindle']['events'] = array_merge($bootstrap['admediator']['hardcoded']['4.8']['kindle']['events'], $bootstrap['admediator']['hardcoded']['4.7']['kindle']['events']);

$bootstrap['admediator']['hardcoded']['4.9']['android'] = $bootstrap['admediator']['hardcoded']['4.8']['android'];
$bootstrap['admediator']['hardcoded']['4.9']['kindle'] = $bootstrap['admediator']['hardcoded']['4.8']['kindle'];
$bootstrap['admediator']['hardcoded']['4.9']['ios'] = $bootstrap['admediator']['hardcoded']['4.8']['ios'];
$bootstrap['admediator']['hardcoded']['4.9']['ios']['events']['H2H_ENDPUZZLE_FULLSCREEN'] = $bootstrap['admediator']['hardcoded']['4.9']['ios']['events']['ENDPUZZLE_FULLSCREEN'];

$bootstrap['admediator']['hardcoded']['4.10'] = $bootstrap['admediator']['hardcoded']['4.9'];

$bootstrap['shop_mediator']['hardcoded']['4.10']['windows'] = $bootstrap['shop_mediator']['hardcoded']['4.10']['ios'] = $bootstrap['shop_mediator']['hardcoded']['4.10']['android'] =
$bootstrap['shop_mediator']['hardcoded']['4.10']['kindle'] = $bootstrap['shop_mediator']['hardcoded']['4.10']['facebook'] = array(
    'enable_direct_play' => true,
    'enable_offer_wall' => true
);