# GAMP Bundle
Google Analytics Measurement Protocol Package for Symfony2. Supports all GA Measurement Protocol API methods.

[![Total Downloads](https://poser.pugx.org/fourlabs/gamp-bundle/downloads)](https://packagist.org/packages/fourlabs/gamp-bundle)
[![License](https://poser.pugx.org/fourlabs/gamp-bundle/license)](https://packagist.org/packages/fourlabs/gamp-bundle)

## Installation
### Download the Bundle
Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

``` bash
$ composer require fourlabs/gamp-bundle dev-master
```

This command requires you to have Composer installed globally, as explained in the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Enable the Bundle

Then, enable the bundle by adding the following line in the *app/AppKernel.php* file of your project:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new FourLabs\GampBundle\FourLabsGampBundle(),
    );
}
```

### Config Overview

Set your Google Analytics Tracking / Web Property ID in `tracking_id` key **[REQUIRED]**

See: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#tid

``` yaml
tracking_id: 'UA-XXXX-Y'
```


*All other configuration options are optional, use as per your requirements.*


The Protocol version. The current value is '1'.

See: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#v

``` yaml
protocol_version: 1
```


To send data over SSL, set `use_ssl` to true.

See: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#tid

``` yaml
use_ssl: true
```


To Anonymize IP, set `anonymize_ip` to true.

See: https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters#aip

``` yaml
anonymize_ip: true
```


To Make Async Requests, set `async_requests` to true.

See: When set to True, All the requests would be made non-blocking (Async).


``` yaml
async_requests: true
```

...

Refer [the library's documentation][2] for other remaining methods and examples, they all work.

> **Note:** You don't have to use the protocol version, tracking id, anonymize ip and async request (non-blocking) methods from the original library as they're automatically set in Service Provider when the package is initialized based on your config file.

[2]: https://github.com/theiconic/php-ga-measurement-protocol#usage

## Credits

This package is a wrapper around the GA Measurement Protocol PHP Library. Thanks to the guys @ [THE ICONIC][1] who developed the library!

[1]: https://github.com/theiconic/php-ga-measurement-protocol
[2]: https://github.com/theiconic/php-ga-measurement-protocol#usage
