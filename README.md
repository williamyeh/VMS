# VMS

[![StyleCI](https://styleci.io/repos/38728555/shield)](https://styleci.io/repos/38728555)
[![Build Status](https://travis-ci.org/OpenISDM/VMS.svg)](https://travis-ci.org/OpenISDM/VMS)
[![Coverage Status](https://coveralls.io/repos/OpenISDM/VMS/badge.svg?branch=develop&service=github)](https://coveralls.io/github/OpenISDM/VMS?branch=develop)

## Introduction
This is the backend repository of the Volunteer management system (VMS).

VMS is a website service for volunteers and project managers of all kinds.

Volunteers can provide their information to VMS and choose to participate in projects that ineterest him/her. 

Project managers can create projects for the volunteers to participate, and can use the contact information they provide to contact them.

We have a up and running VMS site here: http://openisdm.github.io/volunteer/.

Fell free to try out our features.
You can also set it up and manage your own site.

Here are our instructions:

Frontend: https://github.com/OpenISDM/VMS-frontend

Backend: https://github.com/OpenISDM/VMS/wiki/1_1_Setup-development-environment

## Architecture

Volunteer management system (VMS) implements in Model-View-Controller architecture.

### Models
The models are data entities and also working with database. For example, models allows to manipulate database, including insertion, selection, updating, deleting and relation without writing any SQL commands.

### View
The view component is in [VMS-frontend](https://github.com/OpenISDM/VMS-frontend) repo.

### Controllers
Each REST API request is routed to a controller by [Dingo API router](https://github.com/dingo/api/wiki/Creating-API-Endpoints). Besides that, in the router, it also provides to inject HTTP middleware for filtering HTTP requests entering application. After HTTP requests entering, controllers process input and generate JSON response by transformers.

### Request and response flow

The following figure illustrates how VMS process client's request and response to client.

![architecture flow](http://i.imgur.com/iKl32e3.png)

The client (view) sends a request to application, **HTTP Router** routes the request to a corresponding method in a controller by HTTP method and URL. If any **HTTP Middleware** is registered on a routing, the middleware will be invoked to filter request. For example, there is a middleware `\Tymon\JWTAuth\Middleware\GetUserFromToken` for checking the authentication token in a request.

Next, before entering a controller, if there is a **HTTP request** dependency injection on a controller, the request will be created and called. Since the request may contain request validation logic and authorization control, it avoids fat controllers and duplicated code in controllers. A request allows to implement `authorize()`<sup>[1](#references)</sup> and `rules()`<sup>[2](#references)</sup> methods. The `authorize()` returns `true` or `false` for determining whether the request is authorized or not. The `rules()` returns the validation rules <sup>[3](#references)</sup> in `array` type.

After that, a controller processes the request and manipulate **Models**. If the model data should be sent a response to view, it will be passed to **Transformers** in order to transform from object to `array` as JSON response.


### Code organization

| Directory | Description |
|-----------|-------------|
| app/Http/routes.php | HTTP router |
| app/Http/Middleware | HTTP middleware |
| app/Http/Requests | HTTP requests |
| app/Http/Controllers | HTTP controllers |
| app/Transformers | Transformers |
| app/*.php | Models |
| database/migrations | Manipulate database schema |
| tests | Testing program |

## Coding style

### PHP code

PHP code in VMS follows the [PSR-2](http://www.php-fig.org/psr/psr-2/) coding standard and the [PSR-4](http://www.php-fig.org/psr/psr-4/) autoloading standard.

After pushing to VMS repository, the StyleCI is triggered to check the repository.

### HTML & CSS

HTML & CSS in VMS follows the [Code Guide](http://codeguide.co/)   

## Contribution
Please see [Contribution](https://github.com/OpenISDM/VMS/wiki/Contribution) link on wiki

## References
1. [Laravel 5.1 Authorization - Within Form  Requests](https://laravel.com/docs/5.1/authorization#within-form-requests)
2. [Laravel 5.1 Validation - Form Request Validation](https://laravel.com/docs/5.1/validation#form-request-validation)
3. [Laravel 5.1 Validation - Available Validation Rules](https://laravel.com/docs/5.1/validation#available-validation-rules)
