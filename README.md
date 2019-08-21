# Instamojo PHP API [![Latest Stable Version](https://poser.pugx.org/instamojo/instamojo-php/v/stable)](https://packagist.org/packages/instamojo/instamojo-php) [![License](https://poser.pugx.org/instamojo/instamojo-php/license)](https://opensource.org/licenses/MIT)

Assists you to programmatically create, edit and delete Links on Instamojo in PHP.

**Note**: If you're using this wrapper with our sandbox environment `https://test.instamojo.com/` then you should pass `true` as third argument to the `Instamojo` class while initializing it. `client_id` and `client_secret` token for the same can be obtained from https://test.instamojo.com/developers/ (Details: [Test Or Sandbox Account](https://instamojo.zendesk.com/hc/en-us/articles/208485675-Test-or-Sandbox-Account)).

```php
$authType = "app/user" /**Depend on app or user based authentication**/

$api = Instamojo\Instamojo::init($authType,[
        "client_id" =>  'XXXXXQAZ',
        "client_secret" => 'XXXXQWE',
        "username" => 'FOO', /** In case of user based authentication**/
        "password" => 'XXXXXXXX' /** In case of user based authentication**/
       
    ],true); /** true for sandbox enviorment**/

```

## Installing via [Composer](https://getcomposer.org/)

```bash
$ php composer.phar require instamojo/instamojo-php
```

**Note**: If you're not using Composer then directly include the contents of `src` directory in your project.


## Usage

```php
$api = Instamojo\Instamojo::init($authType,[
        "client_id" =>  'XXXXXQAZ',
        "client_secret" => 'XXXXQWE',
        "username" => 'FOO', /** In case of user based authentication**/
        "password" => 'XXXXXXXX' /** In case of user based authentication**/
       
    ]);
```


### Create a new Payment Request

```php
try {
    $response = $api->createPaymentRequest(array(
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
```

This will give you JSON object containing details of the Payment Request that was just created.


### Get the status or details of a Payment Request

```php
try {
    $response = $api->getPaymentRequestDetails(['PAYMENT REQUEST ID']);
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```


This will give you JSON object containing details of the Payment Request and the payments related to it.
Key for payments is `'payments'`.

Here `['PAYMENT REQUEST ID']` is the value of `'id'` key returned by the `createPaymentRequest()` query.

### Get a list of all Payment Requests

```php
try {
    $response = $api->getPaymentRequests(); 
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you an array containing Payment Requests created so far. Note that the payments related to individual Payment Request are not returned with this query.

getPaymentRequests() also accepts optional parameters for pagination.

```php
getPaymentRequests($limit=null, $page=null)
```

For example:

```php
$response = $api->getPaymentRequests(50, 1);
```

### Get a list of all Payments

```php
try {
    $response = $api->getPayments(); 
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you an array containing Payments details so far.

getPayments() also accepts optional parameters for pagination.

```php
getPayments($limit=null, $page=null)
```

For example:

```php
$response = $api->getPayments(50, 1);
```

### Get the  details of a Payment

```php
try {
    $response = $api->getPaymentDetails(['PAYMENT ID']);
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the Payment.

Here `['PAYMENT ID']` is the value of `'id'` key returned by the `getPayments()` query.


### Create a Gateway Order

```php
try {
    $response = $api->createGatewayOrder(array(
      "name" => "XYZ",
      "email" => "abc@foo.com",
      "phone" => "99XXXXXXXX",
      "amount" => "200",
      "transaction_id" => 'TXN_ID', /**transaction_id is unique Id**/
      "currency" => "INR"
    ));
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the order in `order` key and payments options in `payment_options` key.


### Create a Gateway Order For payment request

```php
try {
    $response = $api->createGatewayOrderForPaymentRequest($payment_request_id, array(
      "name" => "XYZ",
      "email" => "abc@foo.com",
      "phone" => "99XXXXXXXX",
    ));
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing with created `order_id` key.


### Get the  details of a Gateway Order

```php
try {
    $response = $api->getGatewayOrder(['ORDER ID']);
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the  Gateway Order.

Here `['ORDER ID']` is the value of `'id'` key returned by the `createGatewayOrder()` query.


### Get a list of all Gateway Order

```php
try {
    $response = $api->getGatewayOrders(); 
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you an array containing Gateway Orders details so far.

getGatewayOrders() also accepts optional parameters for pagination.

```php
getGatewayOrders($limit=null, $page=null)
```

For example:

```php
$response = $api->getGatewayOrders(50, 1);
```

### Create a Refund for a payment

```php
try {
    $response = $api->createRefundForPayment($payment_id, array(
      "type" => "RFD",
      "body" => "XYZ reason of refund",
      "refund_amount" => "10",
      "transaction_id" => "TNX_XYZ"
    ));
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing refund details in `refund` key.


### Get the details of a Refund

```php
try {
    $response = $api->getRefundDetails(['REFUND ID']);
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you JSON object containing details of the  Refund.




### Get a list of all Refunds

```php
try {
    $response = $api->getRefunds(); 
    print_r($response);
}
catch (Exception $e) {
    print('Error: ' . $e->getMessage());
}
```

This will give you an array containing Refunds  details so far.

getRefunds() also accepts optional parameters for pagination.

```php
getRefunds($limit=null, $page=null)
```

For example:

```php
$response = $api->getRefunds(50, 1);
```

Further documentation is available at https://docs.instamojo.com/v2/docs

