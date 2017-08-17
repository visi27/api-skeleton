Symfony API Skeleton
============

A Symfony Skeleton to jump start new API projects.

Features
========

* JWT Token Authenticator For API Interfaces
* Encryption Service (currently used to encrypt Google Auth Key)
* HATEOAS Serialiser To build standardised API
* Pagerfanta integration for beautiful pagination
* Integrated unit testing using PHPUnit
* Custom classes to handle Api problems and Exceptions
* Custom ApiTestcase to handle API testing and debugging
* Custom ResponseAsserter helper class to ease Guzzle response testing
* Symfony test environment (app_test.php)
* Doctrine Data Fixtures

Sample API Endpoints
====================

There are  several REST API endpoints implemented in `src/AppBundle/Controller/Api`


Initial configuration
=====================
1. Run `composer install` to install dependecies 
2. Make sure `parameters.yml` is filled with valid values
3. Configure test database name in `config_test.yml`
4. Run `bin/console doctrine:database:create` to create default database
5. Run `bin/console doctrine:database:create --env=test` to create test database
6. To create database tables you can use one of the following commands:
    1. `bin/console doctrine:schema:update --force` updates the database from entities. Delete migration files if you 
    use this option. If you don't you will get errors if you decide to use doctrine migrations in the future.
    2. `bin/console doctrine:migrations:migrate` executes migration files to create tables.
    3. Don't forget to run either command for test environment also (append --env=test to each command)
7. To load the sample data through doctrine data fixtures run `bin/console doctrine:fixtures:load`. You can find 
fixtures definitions in `src/AppBundle/DataFixtures/ORM`
8. Steps to generate SSH keys for JWT authenticator:
    1. Create directory `jwt` inside `var` by running `mkdir var/jwt`
    2. To generate private key execute `openssl genrsa -out var/jwt/private.pem -aes256 4096` and fill pass phrase when required
    3. To generate public key execute `openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem` and provide the pass phrase when required
9. To setup encryption:
    1. Run `vendor/bin/generate-defuse-key` to generate a cryptographically secure key
    2. Assign the genereated string to the parameter `defuse_key` in `parameters.yml`
    3. You can now use the encryption service either by injecting or by using `$this->get('app.security.encryption_service')` from the container in your controllers

API's In Action
===============
To see the API's in action you can either start the local Symfony server or use onalb.com where I have hosted a copy of this application:

First get the API authentication token by sending a POST request with Basic Authorisation headers to http://onalb.com/api/tokens like this:

```php
<?php
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://onalb.com/api/tokens",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_HTTPHEADER => array(
    "authorization: Basic ZmlsYW5maXN0ZWt1KzFAZ21haWwuY29tOmZpbGFuZmlzdGVrdQ==",
    "cache-control: no-cache",
    "postman-token: 08daa855-69bc-72d6-652c-097bf5773f6b"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```
or in a linux shell with WGET:
```bash
wget --quiet \
  --method POST \
  --header 'authorization: Basic ZmlsYW5maXN0ZWt1KzFAZ21haWwuY29tOmZpbGFuZmlzdGVrdQ==' \
  --header 'cache-control: no-cache' \
  --header 'postman-token: 7f1fda30-8677-66bd-20b3-394c998a430b' \
  --output-document \
  - http://onalb.com/api/tokens
```
the header authorisation has a value Basic base64encoded("username:password") in our case "filanfisteku+1@gmail.com:filanfisteku"

After you have extracted the token you can query the API's for informatin, ex: list blog post:

```php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://onalb.com/api/blog",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImZpbGFuZmlzdGVrdSsxQGdtYWlsLmNvbSIsImV4cCI6MTUwMjc4Nzk0MSwiaWF0IjoxNTAyNzg0MzQxfQ.KUCP619SMIiSNFQ7Q0wUSbCzzPKRlBAghcv1i8H1Ya0g9XqdxzsC3lewL8JJlAB5kITpiG8tKTiENDrNdpkOOpamvJPtls4CKCrRxxkwkXaUzVIvDouHM-Y8V90w3mTb1ICaeT3OYnz-MCgSx5srmdQoVMLZGHo6Yr7P2n3zHjzecMfRaRDOJtQ_f9urABHdC_yC0eEAGLed5H9-_jcYqFdSM6I0UTkwf-qpSqWRBt1gPujnPwFQV2WVNUYrxs74Yh-cFi7vSkWrkW_K-5QA-uGRdTE8MnouIZm4QBr4k-PY_pjN3trBkx9tRCbzsOTAU6LzGNrAmFNJMIMYiB7Sw72qY03-ByjBWu29nTMUd7qQ6L5zp5nHWliGBsFc-NFGIIjZ1X_nUzBuvxumzG9MBsvBbEjVtivleZb85CjYEP2WRaClrosgb-2FPEQIsWdYH0uzY10ITgdYFFSlyi0sPDpHBa4zSousNe7Ut9b-JqMHdXUyBthqPFQrEfIl64rORO_zSRXwkm8Q6JvaU1I5sjkkG6k37AGKuQclvQDltHyk-CfOWaoi5vK54mBHSpdmYVIMGbx4FIUqFxy5tMZpvHJD8rO0gPRf8RUr8-4Pl09xEPeMB-eeMba44TQuhk3GKYpkvJxVEPwMyEf0owVMVwpBuFHwunWuEwRMBOXoCTM",
    "cache-control: no-cache",
    "postman-token: f3413a1f-27a2-b245-1b1b-6df6cebf1ebd"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```

Make sure the value of the token is in the key authorization after Bearer.
In a linux shel with WGET:

```bash
wget --quiet \
  --method GET \
  --header 'authorization: Bearer eyJhbGciOiJSUzI1NiJ9.eyJ1c2VybmFtZSI6ImZpbGFuZmlzdGVrdSsxQGdtYWlsLmNvbSIsImV4cCI6MTUwMjc4Nzk0MSwiaWF0IjoxNTAyNzg0MzQxfQ.KUCP619SMIiSNFQ7Q0wUSbCzzPKRlBAghcv1i8H1Ya0g9XqdxzsC3lewL8JJlAB5kITpiG8tKTiENDrNdpkOOpamvJPtls4CKCrRxxkwkXaUzVIvDouHM-Y8V90w3mTb1ICaeT3OYnz-MCgSx5srmdQoVMLZGHo6Yr7P2n3zHjzecMfRaRDOJtQ_f9urABHdC_yC0eEAGLed5H9-_jcYqFdSM6I0UTkwf-qpSqWRBt1gPujnPwFQV2WVNUYrxs74Yh-cFi7vSkWrkW_K-5QA-uGRdTE8MnouIZm4QBr4k-PY_pjN3trBkx9tRCbzsOTAU6LzGNrAmFNJMIMYiB7Sw72qY03-ByjBWu29nTMUd7qQ6L5zp5nHWliGBsFc-NFGIIjZ1X_nUzBuvxumzG9MBsvBbEjVtivleZb85CjYEP2WRaClrosgb-2FPEQIsWdYH0uzY10ITgdYFFSlyi0sPDpHBa4zSousNe7Ut9b-JqMHdXUyBthqPFQrEfIl64rORO_zSRXwkm8Q6JvaU1I5sjkkG6k37AGKuQclvQDltHyk-CfOWaoi5vK54mBHSpdmYVIMGbx4FIUqFxy5tMZpvHJD8rO0gPRf8RUr8-4Pl09xEPeMB-eeMba44TQuhk3GKYpkvJxVEPwMyEf0owVMVwpBuFHwunWuEwRMBOXoCTM' \
  --header 'cache-control: no-cache' \
  --header 'postman-token: 302acfe0-e68a-2c29-3169-41cf2b799a77' \
  --output-document \
  - http://onalb.com/api/blog
``` 

Tests
=====
* Install phpunit and execute it inside the project directory

Third Party
===========

HTML template courtesy of Blackrock Digital: https://github.com/BlackrockDigital/startbootstrap-blog-post