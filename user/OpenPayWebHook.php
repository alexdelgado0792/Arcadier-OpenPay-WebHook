<?php

require_once('ArcadierApi.php');

$marketClientId = 'CLIENT ID';
$marketSecretClient = 'CLIENT SECRET ID';

$json = file_get_contents('php://input');
$data = json_decode($json);

try {
    $response = array();

    //For more type of hooks please see https://documents.openpay.mx/docs/api/#objeto-webhook
    //For each payload please see how it looks in the Openpay dashboard
    switch ($data->type) {
        case "verification":
            //This is only execute one time in order to activate the webhook in OpenPay portal
            $api = new ArcadierApi($marketClientId, $marketSecretClient);
            $api->SendEmail('verification@arcadier.com', 'To email', $data->verification_code, 'Verification WebHook');

            break;
        case "charge.succeeded":
            //SPEI
            if ($data->transaction->method == "bank_account") {
            //Do Something
            }

            //TC/TD
            if ($data->transaction->method == "card") {
            //Do Something
            }

            break;
        case "transaction.expired":
            //SPEI
            if ($data->transaction->method == "bank_account") {
            }

            break;
        case "charge.failed":
            //Do Something
            break;
        default:
            throw new InvalidArgumentException('Is not valid the specific option. Please check payload.', 400);
    }

    $response["Result"] = true;
    $response["Message"] = 'insert message to return';
    //Add more if needed
    echo json_encode($response);
    // echo '{"Result":"some message here"}';

}
catch (Exception $ex) {
    $errorMsg = array();
    $errorMsg["Result"] = False;
    $errorMsg["Message"] = ($ex->getMessage());
    $errorMsg["Code"] = $ex->getCode();
    //Add more if needed
    echo json_encode($errorMsg);
}

?>