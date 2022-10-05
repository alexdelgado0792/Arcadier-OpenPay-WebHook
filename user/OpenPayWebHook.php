<?php

require_once('ArcadierApi.php');

$clientId = '';
$secretClient = '';

$json = file_get_contents('php://input');
$data = json_decode($json);

try {
    //For more type of hooks please see https://documents.openpay.mx/docs/api/#objeto-webhook
    //For each payload please see how it looks in the Openpay dashboard
    switch ($data->type) {
        case "verification":
            //This is only execute one time in order to activate the webhook in OpenPay portal
            $api = new ArcadierApi($clientId, $clientSecret);
            $api->SendEmail('verification@openpay.com', 'to email', $data->verification_code, 'Verification WebHook');

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
            echo "No hay opcion configurada pero llego al webhook de Arcadier.";
            //http_response_code(400);
            break;
    }
}
catch (Exception $ex) {
    //echo $ex->getMessage();
}

?>