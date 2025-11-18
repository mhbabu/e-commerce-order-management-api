<?php

use Illuminate\Support\Facades\DB;

phpinfo();
ini_set('mysqli.allow_local_infile', 1);

// Then connect to DB
DB::connection()->getPdo();