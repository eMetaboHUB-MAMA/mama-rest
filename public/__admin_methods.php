<?php
// ////////////////////////////////////////////////////////////////////////////
$app->put('/admin/inactive-users', function ($request, $response, $args) {
    // init response
    $data = [];
    if (isAdmin()) {
        $putData = $GLOBALS['putData'];
        $nbWeeks = intval($putData['nbWeeks']);
        $count = 0;
        if ($nbWeeks > 0) {
            $user = TokenManagementService::getUserFromToken(getToken());
            $count = UserManagementService::setUsersToInactive($nbWeeks, $user);
        }
        $data = array(
            "nb-inactived" => $count,
            "success" => true
        );
    } else {
        // return 401
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "response" => $data
        ];
    }
    return formatResponse($request, $response, $args, $data);
});

$app->put('/admin/clean-uploaded-files', function ($request, $response, $args) {
    // init response
    $data = [];
    if (isAdmin()) {
        $putData = $GLOBALS['putData'];
        $listOfFiles = scandir(projects_files_dir);
        $listOfFiles = array_diff($listOfFiles, array(
            '..',
            '.'
        ));
        $allProjects = ProjectManagementService::getProjects(null, null);
        foreach ($allProjects as $k => $project) {
            if (!is_null($project->getScientificContextFile())) {
                if (($key = array_search($project->getScientificContextFile(), $listOfFiles)) !== false) {
                    unset($listOfFiles[$key]);
                }
            }
        }
        $count = 0;
        foreach ($listOfFiles as $k => $file) {
            if (unlink(projects_files_dir . $file)) {
                $count++;
            }
        }
        $data = array(
            "nb-deleted" => $count,
            "success" => true
        );
    } else {
        // return 401
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "response" => $data
        ];
    }
    return formatResponse($request, $response, $args, $data);
});

// $app->put ( '/admin/clean-generated-files', function ($request, $response, $args) {
// // NOPE! we are smart and just stream generated files on the fly!
// return null;
// } );

$app->delete('/admin/clean-tokens', function ($request, $response, $args) {
    // init response
    $putData = $GLOBALS['putData'];
    $data = [];
    if (isAdmin()) {
        $nbHours = intval($putData['nbHours']);
        if ($nbHours > 0) {
            TokenManagementService::clean($nbHours);
        }
        $data = array(
            "success" => true
        );
    } else {
        // return 401
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "response" => $data
        ];
    }
    return formatResponse($request, $response, $args, $data);
});

$app->put('/admin/archive-projects', function ($request, $response, $args) {
    // init response
    $putData = $GLOBALS['putData'];
    $data = [];
    if (isAdmin()) {
        $nbHours = intval($putData['nbYears']);
        if ($nbHours > 0) {
            $user = TokenManagementService::getUserFromToken(getToken());
            ProjectManagementService::archiveOlderThan($nbHours, $user);
        }
        $data = array(
            "success" => true
        );
    } else {
        // return 401
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "response" => $data
        ];
    }
    return formatResponse($request, $response, $args, $data);
});

$app->get('/admin/show-logs[/{file}]', function ($request, $response, $args) {
    $file = ($args['file']);

    if (isAdmin()) {

        switch ($file) {
            case "weekly-mailler.log":
            case "weekly-mailler":
                $file = cron_weekly_mailler_log;
                break;
            case "monthly-users_inactiver.log":
            case "monthly-users_inactiver":
                $file = cron_monthly_users_inactiver_log;
                break;
            case "daily-mailler.log":
            case "daily-mailler":
            default:
                $file = cron_daily_mailler_log;
                break;
        }
        // set file / from / to
        $from = -1;
        $to = 999;
        if (isset($_GET['from']) && $_GET['from'] != "") {
            $from = intval($_GET['from']);
        }
        if (isset($_GET['to']) && $_GET['to'] != "") {
            $to = intval($_GET['to']);
        }

        // set header
        header('Content-Type: text/plain; charset=utf-8');

        // check
        if (!file_exists("../" . $file)) {
            return null;
        }

        // display file
        $file = fopen("../" . $file, "r");
        $lineCpt = 0;
        while (!feof($file)) {
            if ($lineCpt >= $from && $lineCpt <= $to) {
                echo fgets($file);
            } else {
                fgets($file);
            }
            $lineCpt++;
        }
        fclose($file);
    } else {
        // return 401
        return formatResponse401($request, $response);
    }
    return null;
})->setArgument('id', null);
