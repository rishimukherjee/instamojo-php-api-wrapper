# Instamojo PHP API

Assists you to programmatically create, edit and delete Links on Instamojo in PHP.


## Usage

### Create a Link

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->linkCreate(array(
            'title'=>'Hello API',
            'description'=>'Create a new Link easily',
            'base_price'=>100,
            'cover_image'=>'/path/to/photo.jpg'
            ));
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Link that was just created.

### Edit a Link

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->linkEdit(
            'hello-api', // You must specify the slug of the Link
            array(
            'title'=>'A New Title',
            ));
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

### List all Links

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->linksList();
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

### List all Payments

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentsList();
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

### Get Details of a Payment using Payment ID

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->paymentDetail('[PAYMENT ID]');
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>


## Available Functions

You have these functions to interact with the API:

  * `linksList()` List all Links created by authenticated User.
  * `linkDetail($slug)` Get details of Link specified by its unique slug.
  * `linkCreate(array $link)` Create a new Link.
  * `linkEdit($slug, array $link)` Edit an existing Link.
  * `linkDelete($slug)` Archvive a Link - Archived Links cannot be generally accessed by the API. User can still view them on the Dashboard at instamojo.com.
  *  `paymentsList()` List all Payments linked to User's account.
  * `paymentDetail($payment_id)` Get details of a Payment specified by its unique Payment ID. You may receive the Payment ID via `paymentsList()` or via URL Redirect function or as a part of Webhook data.

## Link Creation Parameters

### Required

  * `title` - Title of the Link, be concise.
  * `description` - Describe what your customers will get, you can add terms and conditions and any other relevant information here. Markdown is supported, popular media URLs like Youtube, Flickr are auto-embedded.
  * `base_price` - Price of the Link. This may be 0, if you want to offer it for free. 

### File and Cover Image
  * `file_upload` - Full path to the file you want to sell. This file will be available only after successful payment.
  * `cover_image` - Full path to the IMAGE you want to upload as a cover image.

### Quantity
  * `quantity` - Set to 0 for unlimited sales. If you set it to say 10, a total of 10 sales will be allowed after which the Link will be made unavailable.

### Post Purchase Note
  * `note` - A post-purchase note, will only displayed after successful payment. Will also be included in the ticket/ receipt that is sent as attachment to the email sent to buyer. This will not be shown if the payment fails.

### Event
  * `start_date` - Date-time when the event is beginning. Format: `YYYY-MM-DD HH:mm`
  * `end_date` - Date-time when the event is ending. Format: `YYYY-MM-DD HH:mm`
  * `venue` - Address of the place where the event will be held.
  * `timezone` - Timezone of the venue. Example: Asia/Kolkata

### Redirects and Webhooks
  * `redirect_url` - This can be a Thank-You page on your website. Buyers will be redirected to this page after successful payment.
  * `webhook_url` - Set this to a URL that can accept POST requests made by Instamojo server after successful payment.
  * `enable_pwyw` - set this to True, if you want to enable Pay What You Want. Default is False.
  * `enable_sign` - set this to True, if you want to enable Link Signing. Default is False. For more information regarding this, and to avail this feature write to support at instamojo.com.

Further documentation is available at https://www.instamojo.com/developers/

---

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


---

## Refunds

### Create a new Refund

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->refundCreate(array(
            'payment_id'=>'MOJO5c04000J30502939',
            'type'=>'QFL',
            'body'=>'Customer is not satified.'
            ));
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Refund that was just created.


### Get the details of a Refund

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->refundDetail('[REFUND ID]');
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you JSON object containing details of the Refund.

Here `['REFUND ID']` is the value of `'id'` key returned by the `refundCreate()` query.


### Get a list of all Refunds

    <?php
    require "instamojo.php";

    $api = new Instamojo('[API_KEY]', '[AUTH_TOKEN]');

    try {
        $response = $api->refundsList();
        print_r($response);
    }
    catch (Exception $e) {
        print('Error: ' . $e->getMessage());
    }
    ?>

This will give you an array containing Refunds created so far.

## Available Refund Functions

You have these functions to interact with the Refund API:

  * `refundCreate(array $refund)` Create a new Refund.
  * `refundDetail($id)` Get details of Refund specified by its unique id.
  * `refundsList()` Get a list of all Refunds.

## Refund Creation Parameters

### Required
  * `payment_id`: Payment ID for which Refund is being requested.
  * `type`: A three letter short-code to identify the type of the refund. Check the
            REST docs for more info on the allowed values.
  * `body`: Additional explanation related to why this refund is being requested.

### Optional
  * `refund_amount`: This field can be used to specify the refund amount. For instance, you
            may want to issue a refund for an amount lesser than what was paid. If
            this field is not provided then the total transaction amount is going to
            be used.

