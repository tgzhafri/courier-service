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
# run command with inputs

# input formats
# base_delivery_cost no_of_packges
# pkg_id1 pkg_weight1_in_kg distance1_in_km offer_code1
# ...

# other format of input or missing input will throw an error 'Invalid input'
# example input
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
# run command with inputs

# input formats
# base_delivery_cost no_of_packges
# pkg_id1 pkg_weight1_in_kg distance1_in_km offer_code1
# ....
# no_of_vehicles max_speed max_carriable_weight

# other format of input or missing input will throw an error 'Invalid input'
# example input
$ php artisan courier:delivery-estimate
    "100 5
    PKG1 50 30 OFR001
    PKG2 75 125 OFR008
    PKG3 175 100 OFR003
    PKG4 110 60 OFR002
    PKG5 155 95 NA
    2 70 200"
```

### Tests

-   [CourierServiceCostEstimationTest.php](https://github.com/tgzhafri/courier-service/blob/main/tests/Feature/CourierServiceCostEstimationTest.php)

-   [CourierServiceTimeEstimationTest.php](https://github.com/tgzhafri/courier-service/blob/main/tests/Feature/CourierServiceTimeEstimationTest.php)

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
