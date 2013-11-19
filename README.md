#TTNC's PHP API Client

A simple PHP library that handles connection, authentication, requests and parsing of responses to and from TTNC's API. For more information on TTNC's API visit [TTNC's API Pages](http://www.ttnc.co.uk/myttnc/ttnc-api/) or [TTNC's Developer Centre](http://developer.ttnc.co.uk)

A list of function requests available via the API can be found [here](http://developer.ttnc.co.uk/functions/)

## Requirements

- PHP5 or greater.
- Use of file\_get\_contents to access remote URLs.
- An account with [TTNC](http://www.ttnc.co.uk).
- A VKey (Application) created via [myTTNC](https://www.myttnc.co.uk).

## Usage

The API can be constructed as follows;
```php
	$Api = new TTNCApi('<username>', '<password>', '<VKey>');
```

Requests can then be 'spooled' in the object until the *MakeRequests()* method is called. While not required, each request should be given an ID which can be used to retrieve the response later on;

```php
	$Request = $API->NewRequest('NoveroNumbers', 'ListNumbers', 'Request1Id');
```

### Basic Usage
```php
<?php
	require_once('TTNCApi.php');
	$Api = new TTNCApi('<username>', '<password>', '<VKey>');
	$Request = $API->NewRequest('NoveroNumbers', 'ListNumbers', 'Request1Id');
	$Api->MakeRequests();
	$Response = $Api->GetResponseFromId('Request1Id');
?>
	
```

In order to send data in a request - the *SetData()* method can  be called on the *Request* object;

```php
<?php
	require_once('TTNCApi.php');
	$Api = new TTNCApi('<username>', '<password>', '<VKey>');
	$Request = $API->NewRequest('NoveroNumbers', 'SetDestination', 'Request1Id');
	$Request->SetData('Number', '02031511000');
	$Request->SetData('Destination', '07512312312');
	$Api->MakeRequests();
	$Response = $Api->GetResponseFromId('Request1Id');
?>
```

### Parsing Responses

As long as each *Request* has been given an ID, after *MakeRequests()* is called, it can then be retrieved from the API object using the same ID.
```php
	$Response = $Api->GetResponseFromId('Request1Id');
	print_r($Response);
```

### Advanced Usage

The client deals automatically with the *Auth* requests for you, however, in order to perform some more advanced actions on the API (such as ordering numbers via the *AddToBasket* request) you may need to save the session state between script executions. In order to do this it's necessary to access the *SessionRequest* Response and retrieve the returned SessionId. This can then be stored in your own code (such as $_SESSION or $_COOKIE) for use on the next request;

```php
<?php
	require_once('TTNCApi.php');
    $Api = new TTNCApi('<username>', '<password>', '<VKey>');
    $Request = $API->NewRequest('Order', 'AddToBasket', 'Request1Id');
    $Request->SetData('number', '02031231231');
    $Request->SetData('type', 'number');
    $Api->MakeRequests();
    $Response = $Api->GetResponseFromId('SessionRequest');
	
	// Store $Response['SessionId'] in your own code.

?>
```

Then on repeat requests, to retrieve the same basket you can construct the object without authentication and then parse in the SessionId to use on Requests;

```php
<?
	require_once('TTNCApi.php');
    $Api = new TTNCApi();
    $Api->UseSession($SessionId); // From the previous request, stored in your own code.
    $Request = $API->NewRequest('Order', 'ViewBasket', 'Request1Id');
    $Api->MakeRequests();
    $Response = $Api->GetResponseFromId('Request1Id');
    // Response now contains a representation of your basket.
?>
```

## Getting Support

If you have any questions or support queries then first please read the [Developers Site](http://developer.ttnc.co.uk) and then email support@ttnc.co.uk.

