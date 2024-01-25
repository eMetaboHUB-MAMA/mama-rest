<?php
$app->get('/test-can-user-be-anonymized[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);
    // init response
    $data = [];
    // check admin or same user
    if (isAdmin() || isSameUser($id)) { //
        $message = GdprManagementService::canUserBeAnonymized($id);
        $data['can_be_anonymized'] = str_starts_with($message, '_YES');
        $data['message'] = $message;
    } else {
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "gdpr" => $data
        ];
    }
    // format response
    return formatResponse($request, $response, $args, $data);
})->setArgument('id', null);

$app->delete('/anonymize-user[/{id}]', function ($request, $response, $args) {
    $id = intval($args['id']);
    // init response
    $data = [];
    // check admin or same user
    if (isAdmin() || isSameUser($id)) { //
        $success = GdprManagementService::anonymizeUser($id);
        $data['success'] = $success;
        $data['message'] = $success ? "user_as_been_anonymized" : "user_can_not_be_anonymized";
    } else {
        return formatResponse401($request, $response);
    }
    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "gdpr" => $data
        ];
    }
    // format response
    return formatResponse($request, $response, $args, $data);
})->setArgument('id', null);