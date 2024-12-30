<?php
    // Name of Git branch you want to pull
    $git_branch = 'main';
    // How much output do you want to return?
    // Options are none, status, and all
    $verbose_output = 'none';

    $my_path = dirname( __FILE__ );
    $web_root_path = dirname( dirname( __FILE__ ) );

    // Prevent accidental XSS
    header("Content-type: text/plain"); 

    // Get headers
    $allHeaders = getallheaders();
    // Get Content-Type from headers
    $contentType = $allHeaders['Content-Type'];

    // Takes raw data from the request
    $json = file_get_contents('php://input');
    // Converts it into a PHP object
    $data = json_decode($json, true);

    // create empty array for debug output
    $log = array();

    if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Update started"); }
    // If $data is set and header content type = application/json
    if ( $data && $contentType == 'application/json' && $data['ref'] == 'refs/heads/' . $git_branch ) {

        if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Fetch started"); }
        $fetch_command = shell_exec('git -C ' . $web_root_path . '/ fetch origin');
        if ( $fetch_command === false) {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Fetch failed"); }
            if ($verbose_output == 'all' ) { array_push($log, $fetch_command); }
            exit;
        } else {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Fetch complete"); }
            if ($verbose_output == 'all' ) {
                array_push($log, "Fetch results: ");
                array_push($log, $fetch_command);
            }
        }

        if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Reset started"); }
        $reset_command = shell_exec('git -C ' . $web_root_path . '/ reset --hard origin/' . $git_branch);
        if ( $reset_command === false || $reset_command === null ) {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Reset failed"); }
            if ($verbose_output == 'all' ) { array_push($log, $reset_command); }
            exit;
        } else {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Reset complete"); }
            if ($verbose_output == 'all' ) {
                array_push($log, "Reset results: ");
                array_push($log, $reset_command);
            }
        }

        if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Pull started"); }
        $pull_command = shell_exec('git -C ' . $web_root_path . '/ pull --rebase origin ' . $git_branch . ' --depth=1');
        if ( $pull_command === false ) {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Pull failed"); }
            if ($verbose_output == 'all' ) { array_push($log, $pull_command); }
            exit;
        } elseif ( $pull_command !== null ) {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Pull complete"); }
            if ($verbose_output == 'all' ) {
                array_push($log, "Pull results: ");
                array_push($log, $pull_command);
            }
        } else {
            if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Pull complete"); }
            if ($verbose_output == 'all' ) { array_push($log, $pull_command); }
        }

    } else {
        if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "Nothing to do"); }
    }
    
    if ($verbose_output == 'all' || $verbose_output == 'status') { array_push($log, "END"); }

    // If there are any entries in the log, print them.
    if ( count($log) !== 0 ) {
        print_r($log);
    }
?>
