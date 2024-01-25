<?php
$app->post('/contact-message', function ($request, $response, $args) {

    // return 201
    http_response_code(201);
    $response = $response->withStatus(201);

    // results
    $data = null;

    // get user / check if authentificated 
    $currentUser = TokenManagementService::getUserFromToken(getToken());
    if (!isset($currentUser) || $currentUser == null) {
        return formatResponse400($request, $response);
    }

    // get message
    if (!(isset($_POST['message']) && $_POST['message'] != "")) {
        return formatResponse400($request, $response);
    }

    // set tag whitelist
    $message = strip_tags($_POST['message'], '<b><i><u><br>');

    $data = EmailManagementService::sendContactMessageEmail($currentUser, $message);

    // special case: XML lists
    $headerValueArray = $request->getHeader('Accept');
    if (in_array("application/xml", $headerValueArray) || getFormat() == "xml") {
        $data = [
            "result" => $data
        ];
    }

    return formatResponse($request, $response, $args, $data);
});
