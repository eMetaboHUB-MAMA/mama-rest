<?php
$app->get('/projects', function ($request, $response, $args) {

    // init response
    $data = [];

    if (isAdmin() || isProjectManager()) {
        // ALL PROJECTS
        $userToFilter = null;
        $userFilter = null;
        if (isset($_GET['userFilter']) && $_GET['userFilter'] != "" && $_GET['userFilter'] != "undefined") {
            $userToFilter = TokenManagementService::getUserFromToken(getToken())->getId();
            $userFilter = $_GET['userFilter'];
        }

        $data = ProjectManagementService::getProjects($userToFilter, $userFilter);
    } else if (TokenManagementService::isValide(getToken())) {
        // CURRENT USER PROJECTS
        $data = ProjectManagementService::getProjects(TokenManagementService::getUserFromToken(getToken())->getId());
        foreach ($data as $key => $value) {
            ($value->setProjectExtraData(null));
        }
    } else {
        // return emtpty array
        $data = [];
    }

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "projects" => $data
        ];
    } else if (in_array("application/xls", $headerValueArray) || getFormat() == "xls") {
        return getXLSfile("projects", $data);
    }

    return formatResponse($request, $response, $args, $data);
});

$app->get('/projects-stats', function ($request, $response, $args) {

    // init response
    $data = Array();

    if (TokenManagementService::isValide(getToken())) {
        $user = TokenManagementService::getUserFromToken(getToken());
        $data = Array(
            'userRight' => $user->getRight()
        );
        // $data ['userStatus'] = $user->getStatus ();
        //
        $data['userProjectsRejected'] = ProjectManagementService::countProjects($user->getId(), "rejected", "owner");
        $data['userProjectsWaiting'] = ProjectManagementService::countProjects($user->getId(), "waiting", "owner");
        $data['userProjectsCompleted'] = ProjectManagementService::countProjects($user->getId(), "completed", "owner");
        $data['userProjectsAccepted'] = ProjectManagementService::countProjects($user->getId(), "accepted", "owner");
        $data['userProjectsAssigned'] = ProjectManagementService::countProjects($user->getId(), "assigned", "owner");
        $data['userProjectsRunning'] = ProjectManagementService::countProjects($user->getId(), "running", "owner");
        $data['userProjectsBlocked'] = ProjectManagementService::countProjects($user->getId(), "blocked", "owner");
        $data['userProjectsArchived'] = ProjectManagementService::countProjects($user->getId(), "archived", "owner");
        if (isAdmin() || isProjectManager()) {
            $userToFilter = null;
            $userFilter = null;
            if (isset($_GET['userFilter']) && $_GET['userFilter'] != "" && $_GET['userFilter'] != "undefined") {
                $userFilter = $_GET['userFilter'];
                $userToFilter = $user->getId();
            }

            $data['allProjectsRejected'] = ProjectManagementService::countProjects($userToFilter, "rejected", $userFilter);
            $data['allProjectsWaiting'] = ProjectManagementService::countProjects($userToFilter, "waiting", $userFilter);
            $data['allProjectsCompleted'] = ProjectManagementService::countProjects($userToFilter, "completed", $userFilter);
            $data['allProjectsAccepted'] = ProjectManagementService::countProjects($userToFilter, "accepted", $userFilter);
            $data['allProjectsAssigned'] = ProjectManagementService::countProjects($userToFilter, "assigned", $userFilter);
            $data['allProjectsRunning'] = ProjectManagementService::countProjects($userToFilter, "running", $userFilter);
            $data['allProjectsBlocked'] = ProjectManagementService::countProjects($userToFilter, "blocked", $userFilter);
            $data['allProjectsArchived'] = ProjectManagementService::countProjects($userToFilter, "archived", $userFilter);

            // $data ['allProjectsRejected'] = ProjectManagementService::countProjects ( null, "rejected", "inCharge");
            // $data ['allProjectsWaiting'] = ProjectManagementService::countProjects ( null, "waiting", "inCharge");
            $data['inChargeProjectsCompleted'] = ProjectManagementService::countProjects($user->getId(), "completed", "inCharge");
            $data['inChargeProjectsAccepted'] = ProjectManagementService::countProjects($user->getId(), "accepted", "inCharge");
            $data['inChargeProjectsAssigned'] = ProjectManagementService::countProjects($user->getId(), "assigned", "inCharge");
            $data['inChargeProjectsRunning'] = ProjectManagementService::countProjects($user->getId(), "running", "inCharge");
            $data['inChargeProjectsBlocked'] = ProjectManagementService::countProjects($user->getId(), "blocked", "inCharge");
            // $data ['allProjectsArchived'] = ProjectManagementService::countProjects ( null, "archived", "inCharge" );
        }
    }

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "statistics" => $data
        ];
    }

    return formatResponse($request, $response, $args, $data);
});

$app->get('/project[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);
    $data = [];

    $user = TokenManagementService::getUserFromToken(getToken());
    if (is_null($user))
        return formatResponse401($request, $response);

    $dataSec = ProjectManagementService::get($id);
    if (isAdmin() || isProjectManager()) {
        $data = $dataSec;
    } else if ($user->getId() == $dataSec->getOwner()
        ->getId()) {
        $data = $dataSec;
        // user need extra data if pj stopped / blocked!
        if ($dataSec->getProjectExtraData() != null) {
            $newExtraData = $dataSec->getProjectExtraData()
                ->cleanPrivateData();
            $data->setProjectExtraData($newExtraData);
        } else {
            $data->setProjectExtraData(null);
        }
    } else {
        return formatResponse401($request, $response);
    }

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "project" => $data
        ];
    }

    return formatResponse($request, $response, $args, $data);
})
    ->setArgument('id', null);

$app->post('/project', function ($request, $response, $args) {
    if (! (isset($_POST['projectTitle']) && $_POST['projectTitle'] != "")) {
        return formatResponse400($request, $response);
    }

    $user = TokenManagementService::getUserFromToken(getToken());
    if (is_null($user)) {
        return formatResponse401($request, $response);
    }

    $title = $_POST['projectTitle'];

    // $interestInMthCollaboration,
    (isset($_POST["interestInMthCollaboration"]) && $_POST['interestInMthCollaboration'] != "") ? $interestInMthCollaboration = $_POST["interestInMthCollaboration"] : $interestInMthCollaboration = null;
    // $demandTypeEqProvisioning,
    (isset($_POST["demandTypeEqProvisioning"]) && $_POST['demandTypeEqProvisioning'] == "true") ? $demandTypeEqProvisioning = true : $demandTypeEqProvisioning = false;
    // $demandTypeCatalogAllowance,
    (isset($_POST["demandTypeCatalogAllowance"]) && $_POST['demandTypeCatalogAllowance'] == "true") ? $demandTypeCatalogAllowance = true : $demandTypeCatalogAllowance = false;
    // $demandTypeFeasibilityStudy,
    (isset($_POST["demandTypeFeasibilityStudy"]) && $_POST['demandTypeFeasibilityStudy'] == "true") ? $demandTypeFeasibilityStudy = true : $demandTypeFeasibilityStudy = false;
    // $demandTypeTraining,
    (isset($_POST["demandTypeTraining"]) && $_POST['demandTypeTraining'] == "true") ? $demandTypeTraining = true : $demandTypeTraining = false;
    // $demandTypeDataProcessing,
    (isset($_POST["demandTypeDataProcessing"]) && $_POST['demandTypeDataProcessing'] == "true") ? $demandTypeDataProcessing = true : $demandTypeDataProcessing = false;
    // $demandTypeOther,
    (isset($_POST["demandTypeOther"]) && $_POST['demandTypeOther'] == "true") ? $demandTypeOther = true : $demandTypeOther = false;
    // $samplesNumber,
    (isset($_POST["samplesNumber"]) && $_POST['samplesNumber'] != "") ? $samplesNumber = $_POST["samplesNumber"] : $samplesNumber = null;
    // $thematicWords,
    $thematicWords = null;
    (isset($_POST["cloudWords"]) && $_POST['cloudWords'] != "") ? $thematicWords = $_POST["cloudWords"] : $thematicWords = null;
    if (! is_null($thematicWords)) {
        $converted = Array();
        foreach (explode(",", $thematicWords) as $k => $v) {
            array_push($converted, intval($v));
        }
        $thematicWords = $converted;
    }
    // $subThematicWords,
    $subThematicWords = null;
    (isset($_POST["subCloudWords"]) && $_POST['subCloudWords'] != "") ? $subThematicWords = $_POST["subCloudWords"] : $subThematicWords = null;
    if (! is_null($subThematicWords)) {
        $converted = Array();
        foreach (explode(",", $subThematicWords) as $k => $v) {
            array_push($converted, intval($v));
        }
        $subThematicWords = $converted;
    }
    // $targeted,
    $targeted = null;
    if (isset($_POST["targeted"]) && $_POST['targeted'] != "") {
        if ($_POST["targeted"] == "true") {
            $targeted = true;
        } else if ($_POST["targeted"] == "false") {
            $targeted = false;
        }
    }
    // $mthPlatforms,
    $mthPlatforms = null;
    // (isset ( $_POST ["mthPlatforms"] ) && $_POST ['mthPlatforms'] != "") ? $mthPlatforms = $_POST ["mthPlatforms"] : $mthPlatforms = null;
    (isset($_POST["mthPlatforms"]) && $_POST['mthPlatforms'] != "") ? $mthPlatforms = $_POST["mthPlatforms"] : $mthPlatforms = null;
    if (! is_null($mthPlatforms)) {
        $converted = Array();
        foreach (explode(",", $mthPlatforms) as $k => $v) {
            array_push($converted, intval($v));
        }
        $mthPlatforms = $converted;
    }
    // $canBeForwardedToCoPartner,
    $canBeForwardedToCoPartner = null;
    if (isset($_POST["copartner"]) && $_POST['copartner'] != "") {
        if ($_POST["copartner"] == "true") {
            $canBeForwardedToCoPartner = true;
        } else if ($_POST["copartner"] == "false") {
            $canBeForwardedToCoPartner = false;
        }
    }
    // $scientificContext,
    (isset($_POST["scientificContext"]) && $_POST['scientificContext'] != "") ? $scientificContext = $_POST["scientificContext"] : $scientificContext = null;
    // $scientificContextFile,
    (isset($_POST["scientificContextFile"]) && $_POST['scientificContextFile'] != "") ? $scientificContextFile = $_POST["scientificContextFile"] : $scientificContextFile = null;

    // financialContext: project_financed,project_inProvisioning,project_onOwnSupply,project_notFinanced
    $financialContextIsProjectFinanced = false;
    $financialContextIsProjectInProvisioning = false;
    $financialContextIsProjectOnOwnSupply = false;
    $financialContextIsProjectNotFinanced = false;
    if (isset($_POST["financialContext"]) && $_POST['financialContext'] != "") {
        $listFinancial = explode(",", $_POST["financialContext"]);
        foreach ($listFinancial as $context) {
            if ($context == "project_financed") {
                $financialContextIsProjectFinanced = true;
            } else if ($context == "project_inProvisioning") {
                $financialContextIsProjectInProvisioning = true;
            } else if ($context == "project_onOwnSupply") {
                $financialContextIsProjectOnOwnSupply = true;
            } else if ($context == "project_notFinanced") {
                $financialContextIsProjectNotFinanced = true;
            }
        }
    }

    // financialContextBis: project_financedEU,project_financedANR,project_financedNational,project_financedRegional,project_financedCompanyTutorship,project_financedOther
    $financialContextIsProjectEU = false;
    $financialContextIsProjectANR = false;
    $financialContextIsProjectNational = false;
    $financialContextIsProjectRegional = false;
    $financialContextIsProjectCompagnyTutorship = false;
    $financialContextIsProjectOwnResourcesLaboratory = false;
    $financialContextIsProjectInternationalOutsideEU = false;
    $financialContextIsProjectOther = false;
    if (isset($_POST["financialContextBis"]) && $_POST['financialContextBis'] != "") {
        $listFinancial = explode(",", $_POST["financialContextBis"]);
        foreach ($listFinancial as $context) {
            if ($context == "project_financedEU") {
                $financialContextIsProjectEU = true;
            } else if ($context == "project_financedANR") {
                $financialContextIsProjectANR = true;
            } elseif ($context == "project_financedNational") {
                $financialContextIsProjectNational = true;
            } elseif ($context == "project_financedRegional") {
                $financialContextIsProjectRegional = true;
            } else if ($context == "project_financedCompanyTutorship") {
                $financialContextIsProjectCompagnyTutorship = true;
            } else if ($context == "project_financedOwnResourcesLaboratory") {
                $financialContextIsProjectOwnResourcesLaboratory = true;
            } else if ($context == "project_financedInternationalOutsideEU") {
                $financialContextIsProjectInternationalOutsideEU = true;
            } else if ($context == "project_financedOther") {
                $financialContextIsProjectOther = true;
            }
        }
    }

    // $financialContextIsProjectOtherValue
    (isset($_POST["financialContextOther"]) && $_POST['financialContextOther'] != "") ? $financialContextIsProjectOtherValue = $_POST["financialContextOther"] : $financialContextIsProjectOtherValue = null;

    // financialContext:null
    // financialContextBis:null

    // return 201
    http_response_code(201);
    $response = $response->withStatus(201);

    $data = ProjectManagementService::create($title, $user, $interestInMthCollaboration, $demandTypeEqProvisioning, $demandTypeCatalogAllowance, $demandTypeFeasibilityStudy, $demandTypeTraining, $demandTypeDataProcessing, $demandTypeOther, $samplesNumber, $thematicWords, $subThematicWords, $targeted, $mthPlatforms, $canBeForwardedToCoPartner, $scientificContext, $scientificContextFile, $financialContextIsProjectFinanced, $financialContextIsProjectInProvisioning, $financialContextIsProjectOnOwnSupply, $financialContextIsProjectNotFinanced, $financialContextIsProjectEU, $financialContextIsProjectANR, $financialContextIsProjectNational, $financialContextIsProjectRegional, $financialContextIsProjectCompagnyTutorship, $financialContextIsProjectOwnResourcesLaboratory, $financialContextIsProjectInternationalOutsideEU, $financialContextIsProjectOther, $financialContextIsProjectOtherValue);

    if (! is_null($data) && $data > 0) {
        SpecialEventMailler::sendEmailNewProject($data, $title, $user);
    }

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "result" => $data
        ];
    }

    return formatResponse($request, $response, $args, $data);
});

$app->put('/project[/{id}]',
    function ($request, $response, $args) {
        $id = intval($args['id']);

        $user = TokenManagementService::getUserFromToken(getToken());
        $data = true;
        if (is_null($user))
            return formatResponse401($request, $response);

        $dataSec = ProjectManagementService::get($id);
        if (is_null($dataSec)) {
            return formatResponse400($request, $response);
        }
        $dataSec = clone $dataSec;

        $isAdmin = false;
        if (isAdmin() || isProjectManager()) {
            $isAdmin = true;
        } else if ($user->getId() == $dataSec->getOwner()
            ->getId()) {
            $isAdmin = false;
        } else {
            // user if neither admin/pm/owner of the project
            return formatResponse401($request, $response);
        }

        // update object
        $putData = $GLOBALS['putData'];
        if (isset($putData['analystInCharge']) && $putData['analystInCharge'] != "" && is_null($dataSec->getAnalystInCharge()) && $dataSec->getStatus() == "waiting") {
            // new status: assigned
            $dataSec->setStatus("assigned");
        }
        if (isset($putData['analystInCharge']) && $putData['analystInCharge'] != "") {
            // new analyst in charge
            $idNewInCharge = intval($putData['analystInCharge']);
            $dataSec->setAnalystInCharge(UserManagementService::get($idNewInCharge));
        }
        if (isset($putData['analystsInvolved']) && $putData['analystsInvolved'] != "") {
            // new analyst in charge
            $arrayAnalystsInvolved = Array();
            foreach (explode(",", $putData['analystsInvolved']) as $k => $v) {
                $userT = UserManagementService::get(intval($v));
                array_push($arrayAnalystsInvolved, $userT);
            }
            $dataSec->setAnalystsInvolved($arrayAnalystsInvolved);
        }
        if (isset($putData['status']) && $putData['status'] != "") {
            // new analyst in charge
            $dataSec->setStatus($putData['status']);
        }

        // BASIC DATA
        // title -> NOPE!
        // checkboxs
        if (isset($putData['demandTypeEqProvisioning']) && $putData['demandTypeEqProvisioning'] != "" && $putData['demandTypeEqProvisioning'] != "undefined") {
            if ($putData['demandTypeEqProvisioning'] == "true") {
                $dataSec->setDemandTypeEqProvisioning(true);
            } else if ($putData['demandTypeEqProvisioning'] == "false") {
                $dataSec->setDemandTypeEqProvisioning(false);
            }
        }
        if (isset($putData['demandTypeCatalogAllowance']) && $putData['demandTypeCatalogAllowance'] != "" && $putData['demandTypeCatalogAllowance'] != "undefined") {
            if ($putData['demandTypeCatalogAllowance'] == "true") {
                $dataSec->setDemandTypeCatalogAllowance(true);
            } else if ($putData['demandTypeCatalogAllowance'] == "false") {
                $dataSec->setDemandTypeCatalogAllowance(false);
            }
        }
        if (isset($putData['demandTypeFeasibilityStudy']) && $putData['demandTypeFeasibilityStudy'] != "" && $putData['demandTypeFeasibilityStudy'] != "undefined") {
            if ($putData['demandTypeFeasibilityStudy'] == "true") {
                $dataSec->setDemandTypeFeasibilityStudy(true);
            } else if ($putData['demandTypeFeasibilityStudy'] == "false") {
                $dataSec->setDemandTypeFeasibilityStudy(false);
            }
        }
        if (isset($putData['demandTypeTraining']) && $putData['demandTypeTraining'] != "" && $putData['demandTypeTraining'] != "undefined") {
            if ($putData['demandTypeTraining'] == "true") {
                $dataSec->setDemandTypeTraining(true);
            } else if ($putData['demandTypeTraining'] == "false") {
                $dataSec->setDemandTypeTraining(false);
            }
        }
        if (isset($putData['demandTypeDataProcessing']) && $putData['demandTypeDataProcessing'] != "" && $putData['demandTypeDataProcessing'] != "undefined") {
            if ($putData['demandTypeDataProcessing'] == "true") {
                $dataSec->setDemandTypeDataProcessing(true);
            } else if ($putData['demandTypeDataProcessing'] == "false") {
                $dataSec->setDemandTypeDataProcessing(false);
            }
        }
        if (isset($putData['demandTypeOther']) && $putData['demandTypeOther'] != "" && $putData['demandTypeOther'] != "undefined") {
            if ($putData['demandTypeOther'] == "true") {
                $dataSec->setDemandTypeOther(true);
            } else if ($putData['demandTypeOther'] == "false") {
                $dataSec->setDemandTypeOther(false);
            }
        }

        // radio
        if (isset($putData['copartner']) && $putData['copartner'] != "" && $putData['copartner'] != "undefined") {
            if ($putData['copartner'] == "true") {
                $dataSec->setCanBeForwardedToCoPartner(true);
            } else if ($putData['copartner'] == "false") {
                $dataSec->setCanBeForwardedToCoPartner(false);
            } else {
                $dataSec->setCanBeForwardedToCoPartner(null);
            }
        }
        if (isset($putData['targeted']) && $putData['targeted'] != "" && $putData['targeted'] != "undefined") {
            if ($putData['targeted'] == "true") {
                $dataSec->setTargeted(true);
            } else if ($putData['targeted'] == "false") {
                $dataSec->setTargeted(false);
            } else {
                $dataSec->setTargeted(null);
            }
        }
        // textarea
        if (isset($putData['interestInMthCollaboration']) && $putData['interestInMthCollaboration'] != "") {
            $dataSec->setInterestInMthCollaboration($putData['interestInMthCollaboration']);
        }
        if (isset($putData['scientificContext']) && $putData['scientificContext'] != "") {
            $dataSec->setScientificContext($putData['scientificContext']);
        }
        // file
        if (isset($putData['scientificContextFile']) && $putData['scientificContextFile'] != "") {
            $dataSec->setScientificContextFile($putData['scientificContextFile']);
        }
        // select single
        if (isset($putData['samplesNumber']) && $putData['samplesNumber'] != "") {
            $dataSec->setSamplesNumber($putData['samplesNumber']);
        }
        // text
        if (isset($putData['financialContextIsProjectOtherValue']) && $putData['financialContextIsProjectOtherValue'] != "") {
            $dataSec->setFinancialContextIsProjectOtherValue($putData['financialContextIsProjectOtherValue']);
        }
        // select multiple
        $mthPlatforms = null;
        (isset($putData["mthPlatforms"]) && $putData['mthPlatforms'] != "") ? $mthPlatforms = $putData["mthPlatforms"] : $mthPlatforms = null;
        if (! is_null($mthPlatforms)) {
            $converted = Array();
            foreach (explode(",", $mthPlatforms) as $k => $v) {
                array_push($converted, intval($v));
            }
            $dataSec->setMthPlatforms(MTHPlatformManagementService::getMTHPlatformsByIDs($converted));
        }
        // financialContext
        $financialContextIsProjectFinanced = false;
        $financialContextIsProjectInProvisioning = false;
        $financialContextIsProjectOnOwnSupply = false;
        $financialContextIsProjectNotFinanced = false;
        if (isset($putData["financialContext"]) && $putData['financialContext'] != "") {
            $listFinancial = explode(",", $putData["financialContext"]);
            foreach ($listFinancial as $context) {
                if ($context == "project_financed") {
                    $financialContextIsProjectFinanced = true;
                } else if ($context == "project_inProvisioning") {
                    $financialContextIsProjectInProvisioning = true;
                } else if ($context == "project_onOwnSupply") {
                    $financialContextIsProjectOnOwnSupply = true;
                } else if ($context == "project_notFinanced") {
                    $financialContextIsProjectNotFinanced = true;
                }
            }
            $dataSec->setFinancialContextIsProjectFinanced($financialContextIsProjectFinanced);
            $dataSec->setFinancialContextIsProjectInProvisioning($financialContextIsProjectInProvisioning);
            $dataSec->setFinancialContextIsProjectOnOwnSupply($financialContextIsProjectOnOwnSupply);
            $dataSec->setFinancialContextIsProjectNotFinanced($financialContextIsProjectNotFinanced);
        }
        // financialContextBis
        $financialContextIsProjectEU = false;
        $financialContextIsProjectANR = false;
        $financialContextIsProjectNational = false;
        $financialContextIsProjectRegional = false;
        $financialContextIsProjectCompagnyTutorship = false;
        $financialContextIsProjectInternationalOutsideEU = false;
        $financialContextIsProjectOwnResourcesLaboratory = false;
        $financialContextIsProjectOther = false;
        if (isset($putData["financialContextBis"]) && $putData['financialContextBis'] != "") {
            $listFinancial = explode(",", $putData["financialContextBis"]);
            foreach ($listFinancial as $context) {
                if ($context == "project_financedEU") {
                    $financialContextIsProjectEU = true;
                } else if ($context == "project_financedANR") {
                    $financialContextIsProjectANR = true;
                } elseif ($context == "project_financedNational") {
                    $financialContextIsProjectNational = true;
                } elseif ($context == "project_financedRegional") {
                    $financialContextIsProjectRegional = true;
                } else if ($context == "project_financedCompanyTutorship") {
                    $financialContextIsProjectCompagnyTutorship = true;
                } else if ($context == "project_financedOwnResourcesLaboratory") {
                    $financialContextIsProjectOwnResourcesLaboratory = true;
                } else if ($context == "project_financedInternationalOutsideEU") {
                    $financialContextIsProjectInternationalOutsideEU = true;
                } else if ($context == "project_financedOther") {
                    $financialContextIsProjectOther = true;
                }
            }
            $dataSec->setFinancialContextIsProjectEU($financialContextIsProjectEU);
            $dataSec->setFinancialContextIsProjectANR($financialContextIsProjectANR);
            $dataSec->setFinancialContextIsProjectNational($financialContextIsProjectNational);
            $dataSec->setFinancialContextIsProjectRegional($financialContextIsProjectRegional);
            $dataSec->setFinancialContextIsProjectCompagnyTutorship($financialContextIsProjectCompagnyTutorship);
            $dataSec->setFinancialContextIsProjectOwnResourcesLaboratory($financialContextIsProjectOwnResourcesLaboratory);
            $dataSec->setFinancialContextIsProjectInternationalOutsideEU($financialContextIsProjectInternationalOutsideEU);
            $dataSec->setFinancialContextIsProjectOther($financialContextIsProjectOther);
        }
        // cloud words
        $thematicWords = null;
        (isset($putData["cloudWords"]) && $putData['cloudWords'] != "") ? $thematicWords = $putData["cloudWords"] : $thematicWords = null;
        if (! is_null($thematicWords)) {
            $converted = Array();
            foreach (explode(",", $thematicWords) as $k => $v) {
                array_push($converted, intval($v));
            }
            $dataSec->setThematicWords(KeywordManagementService::getKeywordsByIDs($converted));
        }
        // sub cloud words
        $subThematicWords = null;
        (isset($putData["subCloudWords"]) && $putData['subCloudWords'] != "") ? $subThematicWords = $putData["subCloudWords"] : $subThematicWords = null;
        if (! is_null($subThematicWords)) {
            $converted = Array();
            foreach (explode(",", $subThematicWords) as $k => $v) {
                array_push($converted, intval($v));
            }
            $dataSec->setSubThematicWords(KeywordManagementService::getSubKeywordsByIDs($converted));
        }

        // update in database
        $projectID = $dataSec->getId();
        $data = ProjectManagementService::updateObject($dataSec, $isAdmin, $user);

        // send email
        SpecialEventMailler::sendEmailProjectUpdate($dataSec);

        // extra data
        if ($data && $isAdmin) {
            $projectUpdated = ProjectManagementService::get($id);
            $extraDataSec = $projectUpdated->getProjectExtraData();
            if (is_null($extraDataSec)) {
                $idExtraData = ProjectExtraDataManagementService::create($projectUpdated);
                $extraDataSec = ProjectExtraDataManagementService::get($idExtraData);
            }
            $extraDataSec = clone $extraDataSec;
            // fetch INPUT::TEXT data
            // extra_adminContext
            if (isset($putData['extra_adminContext']) && $putData['extra_adminContext'] != "") {
                $extraDataSec->setAdministrativeContext($putData['extra_adminContext']);
            }
            // extra_geoContext
            if (isset($putData['extra_geoContext']) && $putData['extra_geoContext'] != "") {
                $extraDataSec->setGeographicContext($putData['extra_geoContext']);
            }
            // extra_projectMaturity
            if (isset($putData['extra_projectMaturity']) && $putData['extra_projectMaturity'] != "") {
                $extraDataSec->setProjectMaturity($putData['extra_projectMaturity']);
            }
            // extra_deadline
            if (isset($putData['extra_deadline']) && $putData['extra_deadline'] != "") {
                $extraDataSec->setDeadline($putData['extra_deadline']);
            }
            // extra_budget
            if (isset($putData['extra_budget']) && $putData['extra_budget'] != "") {
                $extraDataSec->setBudgetConstraint($putData['extra_budget']);
            }
            // fetch INPUT::RADIO data
            // extra_laboType
            if (isset($putData['extra_laboType']) && $putData['extra_laboType'] != "" && $putData['extra_laboType'] != "undefined") {
                if ($putData['extra_laboType'] == "public") {
                    $extraDataSec->setLaboType(ProjectExtraData::$LABO_TYPE_PUBLIC);
                } else if ($putData['extra_laboType'] == "private") {
                    $extraDataSec->setLaboType(ProjectExtraData::$LABO_TYPE_PRIVATE);
                } else if ($putData['extra_laboType'] == "privatepublic") {
                    $extraDataSec->setLaboType(ProjectExtraData::$LABO_TYPE_PRIVATE_PUBLIC);
                } else {
                    $extraDataSec->setLaboType(null);
                }
            }
            // fetch INPUT::CHECKBOX
            // extra_hdykm_friend
            if (isset($putData['extra_hdykm_friend']) && $putData['extra_hdykm_friend'] != "" && $putData['extra_hdykm_friend'] != "undefined") {
                if ($putData['extra_hdykm_friend'] == "true") {
                    $extraDataSec->setKnowMTHviaCoworkerOrFriend(true);
                } else if ($putData['extra_hdykm_friend'] == "false") {
                    $extraDataSec->setKnowMTHviaCoworkerOrFriend(false);
                }
            }
            // extra_hdykm_publication
            if (isset($putData['extra_hdykm_publication']) && $putData['extra_hdykm_publication'] != "" && $putData['extra_hdykm_publication'] != "undefined") {
                if ($putData['extra_hdykm_publication'] == "true") {
                    $extraDataSec->setKnowMTHviaPublication(true);
                } else if ($putData['extra_hdykm_publication'] == "false") {
                    $extraDataSec->setKnowMTHviaPublication(false);
                }
            }
            // extra_hdykm_website
            if (isset($putData['extra_hdykm_website']) && $putData['extra_hdykm_website'] != "" && $putData['extra_hdykm_website'] != "undefined") {
                if ($putData['extra_hdykm_website'] == "true") {
                    $extraDataSec->setKnowMTHviaWebsite(true);
                } else if ($putData['extra_hdykm_website'] == "false") {
                    $extraDataSec->setKnowMTHviaWebsite(false);
                }
            }
            // extra_hdykm_searchEngine
            if (isset($putData['extra_hdykm_searchEngine']) && $putData['extra_hdykm_searchEngine'] != "" && $putData['extra_hdykm_searchEngine'] != "undefined") {
                if ($putData['extra_hdykm_searchEngine'] == "true") {
                    $extraDataSec->setKnowMTHviaSearchEngine(true);
                } else if ($putData['extra_hdykm_searchEngine'] == "false") {
                    $extraDataSec->setKnowMTHviaSearchEngine(false);
                }
            }
            // update TEXTAREA data
            if (isset($putData['extra_userNeeds']) && $putData['extra_userNeeds'] != "") {
                $extraDataSec->setSyntheticUserNeeds($putData['extra_userNeeds']);
            }
            // new 1.0.3 - dialog box
            // dialog box val
            if (isset($putData['extra_dialogBoxVal']) && $putData['extra_dialogBoxVal'] != "" && $putData['extra_dialogBoxVal'] != "undefined") {
                $extraDataSec->setDialogBoxVal($putData['extra_dialogBoxVal']);
            }
            // dialog box txt
            if (isset($putData['extra_dialogBoxTxt']) && $putData['extra_dialogBoxTxt'] != "") {
                $extraDataSec->setDialogBoxTxt($putData['extra_dialogBoxTxt']);
            }

            // update in database
            // $projectUpdated->setProjectExtraData ( $extraDataSec );
            $data = ProjectExtraDataManagementService::updateObject($extraDataSec, $isAdmin);
        }

        return formatResponse($request, $response, $args, $data);
    })
    ->setArgument('id', null);

$app->put('/stop-project[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);

    $user = TokenManagementService::getUserFromToken(getToken());
    $data = true;
    if (is_null($user))
        return formatResponse401($request, $response);

    $dataSec = ProjectManagementService::get($id);
    if (is_null($dataSec)) {
        return formatResponse400($request, $response);
    }
    $dataSec = clone $dataSec;

    $isAdmin = false;
    if (isAdmin() || isProjectManager()) {
        $isAdmin = true;
    } else if ($user->getId() == $dataSec->getOwner()
        ->getId()) {
        $isAdmin = false;
    } else {
        // user if neither admin/pm/owner of the project
        return formatResponse401($request, $response);
    }

    // update object
    $putData = $GLOBALS['putData'];

    // update in database
    $projectID = $dataSec->getId();
    // $data = ProjectManagementService::updateObject ( $dataSec, $isAdmin, $user );

    // send email
    // SpecialEventMailler::sendEmailProjectUpdate ( $dataSec );

    // extra data
    if ($data && $isAdmin) {
        $projectUpdated = ProjectManagementService::get($id);
        $extraDataSec = $projectUpdated->getProjectExtraData();
        if (is_null($extraDataSec)) {
            $idExtraData = ProjectExtraDataManagementService::create($projectUpdated);
            $extraDataSec = ProjectExtraDataManagementService::get($idExtraData);
        }
        $extraDataSec = clone $extraDataSec;

        // update SELECT fields
        // "blockedCase="+($("#blockedCase_list").val()) +
        // "&rejectedCase="+($("#rejectedCase_list").val()) +
        // "&stoppedReason="+(encodeURIComponent($("#blockedOrRejectedCase_txt").val())),

        if (isset($putData['blockedCase']) && $putData['blockedCase'] != "" && $putData['blockedCase'] != "undefined") {
            $extraDataSec->setBlockedReason($putData['blockedCase']);
        }
        if (isset($putData['rejectedCase']) && $putData['rejectedCase'] != "" && $putData['rejectedCase'] != "undefined") {
            $extraDataSec->setRejectedReason($putData['rejectedCase']);
        }
        // update TEXTAREA data
        if (isset($putData['stoppedReason']) && $putData['stoppedReason'] != "") {
            $extraDataSec->setStoppedReason($putData['stoppedReason']);
        }

        // update in database
        // $projectUpdated->setProjectExtraData ( $extraDataSec );
        $data = ProjectExtraDataManagementService::updateObject($extraDataSec, $isAdmin);
    }

    return formatResponse($request, $response, $args, $data);
})
    ->setArgument('id', null);

$app->delete('/project[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);
    // TODO
})->setArgument('id', null);

// ////////////////////////////////////////////////////////////////////////////

$app->post('/project-file', function ($request, $response, $args) {
    $target_dir = projects_files_dir;
    $fileName = time() . "-" . basename($_FILES["file_contents"]["name"]);
    $target_file = $target_dir . DIRECTORY_SEPARATOR . $fileName;
    $success = FALSE;
    if (move_uploaded_file($_FILES["file_contents"]["tmp_name"], $target_file)) {
        $success = TRUE;
    }
    $data = array(
        "success" => $success,
        "file" => $fileName
    );
    return formatResponse($request, $response, $args, $data);
});

$app->get('/project-file[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);

    $user = TokenManagementService::getUserFromToken(getToken());
    $data = true;
    if (is_null($user))
        return formatResponse401($request, $response);

    $dataSec = ProjectManagementService::get($id);
    if (is_null($dataSec)) {
        return formatResponse400($request, $response);
    }
    $dataSec = clone $dataSec;

    $isAdmin = false;
    if (isAdmin() || isProjectManager()) {
        $isAdmin = true;
    } else if ($user->getId() == $dataSec->getOwner()
        ->getId()) {
        $isAdmin = false;
    } else {
        // user if neither admin/pm/owner of the project
        return formatResponse401($request, $response);
    }

    if (is_null($dataSec->getScientificContextFile())) {
        return formatResponse400($request, $response);
    }

    $file = projects_files_dir . DIRECTORY_SEPARATOR . $dataSec->getScientificContextFile();
    $fileName = (preg_replace('/^(\d+)-/i', "", $dataSec->getScientificContextFile()));

    header('Content-Type: application/octet-stream');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . basename($fileName) . "\"");
    readfile($file);
})
    ->setArgument('id', null);

/**
 * delete an uploaded file
 *
 * @since 1.0.3
 */
$app->delete('/project-file[/{id}]', function ($request, $response, $args) {

    // init user and authorizations recovery
    $user = TokenManagementService::getUserFromToken(getToken());
    $id = intval($args['id']);

    if (is_null($user)) {
        return formatResponse401($request, $response);
    }
    $dataSec = ProjectManagementService::get($id);
    if (is_null($dataSec)) {
        return formatResponse400($request, $response);
    }
    $project = clone $dataSec;

    $isAdmin = false;
    if (isAdmin() || isProjectManager()) {
        $isAdmin = true;
    } else if ($user->getId() == $project->getOwner()
        ->getId()) {
        $isAdmin = false;
    } else {
        // user if neither admin /pm / owner of the project
        return formatResponse401($request, $response);
    }

    // if we get here, we are eitheradmin /pm / owner of the project
    $project->setScientificContextFile(null);
    $success = ProjectManagementService::updateObject($project, true, $user);
    $data = array(
        "success" => $success
    );

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "response" => $data
        ];
    }
    return formatResponse($request, $response, $args, $data);
})
    ->setArgument('id', null);
