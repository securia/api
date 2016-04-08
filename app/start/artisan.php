<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

\Illuminate\Support\Facades\Artisan::add(new ResetWeeklyTournament);

\Illuminate\Support\Facades\Artisan::add(new ResetDailyAverageTimeSpent);

\Illuminate\Support\Facades\Artisan::add(new Migrate);

\Illuminate\Support\Facades\Artisan::add(new Bootstrap);

\Illuminate\Support\Facades\Artisan::add(new Indexes);

\Illuminate\Support\Facades\Artisan::add(new UpdateDashboard);

\Illuminate\Support\Facades\Artisan::add(new Patch);

\Illuminate\Support\Facades\Artisan::add(new Aws);

\Illuminate\Support\Facades\Artisan::add(new AddBestTimesCollections);

\Illuminate\Support\Facades\Artisan::add(new AddPlayerPuzzlesCollections);

\Illuminate\Support\Facades\Artisan::add(new DistributePlayerPuzzles);

\Illuminate\Support\Facades\Artisan::add(new Cron);

\Illuminate\Support\Facades\Artisan::add(new ComputeRetention);