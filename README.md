<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Courier Service

Coding challenge by Everest Engineering

### Challenge 1 - Cost delivery estimation

-   [CostEstimationCommand.php](https://github.com/tgzhafri/courier-service/blob/main/app/Console/Commands/CostEstimationCommand.php)
-   To run this command, open your console/terminal

```bash
# run test command
$ php artisan courier:cost-estimate

# to insert input [pkg_id weight distance offer_code]
$ php artisan courier:cost-estimate
    "100 3
    PKG1 5 5 OFR001
    PKG2 15 5 OFR002
    PKG3 10 100 OFR003"
```

### Challenge 2 - Cost and Time delivery estimation

-   [TimeEstimationCommand.php](https://github.com/tgzhafri/courier-service/blob/main/app/Console/Commands/TimeEstimationCommand.php)
-   To run this command, open your console/terminal

```bash
# run test command
$ php artisan courier:delivery-estimate

# run test command for multiple combined packages delivery
$ php artisan courier:delivery-estimate test-multiple

# run test command for multiple missing input data
$ php artisan courier:delivery-estimate test-missing
```

### Tests

-   [CourierServiceEstimationTest.php](https://github.com/tgzhafri/courier-service/blob/main/tests/Feature/CourierServiceEstimationTest.php)

## Installation

```bash
# clone the repo
$ git clone https://github.com/tgzhafri/courier-service.git courier-service

# go into app's directory
$ cd courier-service

# install app's dependencies
$ composer install

# run test to see if it's working
$ php artisan test

```
