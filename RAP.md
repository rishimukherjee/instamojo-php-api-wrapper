## Request a Payment

### Create a new Payment Request

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentRequestCreate(array(
            "purpose" => "FIFA 16",
            "amount" => "3499",
            "send_email" => true,
            "email" => "foo@example.com",
            "redirect_url" => "http://www.example.com/handle_redirect.php"
            ));
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Payment Request that was just created.


### Get the status or details of a Payment Request

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentRequestStatus(['PAYMENT REQUEST ID']);
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Payment Request and the payments related to it.
Key for payments is `'payments'`.

Here `['PAYMENT REQUEST ID']` is the value of `'id'` key returned by the `paymentRequestCreate()` query.


### Get the status of a Payment related to a Payment Request

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentRequestPaymentStatus(['PAYMENT REQUEST ID'], ['PAYMENT ID']);
        print_r($response['purpose']);  // print purpose of payment request
        print_r($response['payment']['status']);  // print status of payment
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Payment Request and the payments related to it.
Key for payments is `'payments'`.

Here `['PAYMENT REQUEST ID']` is the value of `'id'` key returned by the `paymentRequestCreate()` query and
['PAYMENT ID'] is the Payment ID received with redirection URL or webhook.


### Get a list of all Payment Requests

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentRequestsList();
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you an array containing Payment Requests created so far. Note that the payments related to individual Payment Request are not returned with this query.

`paymentRequestsList()` also accepts an optional array containing keys `'max_created_at'` , `'min_created_at'`, `'min_modified_at'` and `'max_modified_at'` for filtering the list of Payment Requests. Note that it is not required to pass all of the keys.

    $response = $api->paymentRequestsList(array(
        "max_created_at" => "2015-11-19T10:12:19Z",
        "min_created_at" => "2015-10-29T12:51:36Z"
        ));

For details related to supported datetime format check the documentation: https://www.instamojo.com/developers/request-a-payment-api/#toc-filtering-payment-requests

## Available Request a Payment Functions

You have these functions to interact with the Request a Payment API:

  * `paymentRequestCreate(array $payment_request)` Create a new Payment Request.
  * `paymentRequestStatus($id)` Get details of Payment Request specified by its unique id.
  * `paymentRequestsList(array $datetime_limits)` Get a list of all Payment Requests. The `$datetime_limits` argument is optional an can be used to filter Payment Requests by their creation and modification date.

## Payment Request Creation Parameters

### Required
  * `purpose`: Purpose of the payment request. (max-characters: 30)
  * `amount`: Amount requested (min-value: 9 ; max-value: 200000)

### Optional
  * `buyer_name`: Name of the payer. (max-characters: 100)
  * `email`: Email of the payer. (max-characters: 75)
  * `phone`: Phone number of the payer.
  * `send_email`: Set this to `true` if you want to send email to the payer if email is specified. If email is not specified then an error is raised. (default value: `false`)
  * `send_sms`: Set this to `true` if you want to send SMS to the payer if phone is specified. If phone is not specified then an error is raised. (default value: `false`)
  * `redirect_url`: set this to a thank-you page on your site. Buyers will be redirected here after successful payment.
  * `webhook`: set this to a URL that can accept POST requests made by Instamojo server after successful payment.
  * `allow_repeated_payments`: To disallow multiple successful payments on a Payment Request pass `false` for this field. If this is set to `false` then the link is not accessible publicly after first successful payment, though you can still access it using API(default value: `true`).


Further documentation is available at instamojo.com/developers/request-a-payment-api/