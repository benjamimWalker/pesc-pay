<p align="center">
  <img src="https://raw.githubusercontent.com/benjamimWalker/pesc-pay/master/assets/logo.png" alt="Project logo" />
</p>
<p align="center">
  <a href="https://github.com/benjamimWalker/pesc-pay/actions/workflows/main.yml">
    <img src="https://github.com/benjamimWalker/pesc-pay/actions/workflows/main.yml/badge.svg" alt="Tests" />
  </a>
</p>


## Overview

Pesc Pay is a Laravel API designed for managing payment records in a clean and scalable way.
It provides a backend service for handling payment creation.

Key Technologies used:

* PHP
* Laravel
* MySQL
* Nginx
* Docker + Docker Compose
* PestPHP

## Getting started

> [!IMPORTANT]  
> You must have Docker and Docker Compose installed on your machine.

* Clone the repository:
```sh
git clone https://github.com/benjamimWalker/pesc-pay.git
```

* Go to the project folder:
```sh
cd pesc-pay
```

* Prepare environment files:
```sh
cp .env.example .env
```

* Build the containers:
```sh
docker compose up -d
```

* Install composer dependencies:
```sh
docker compose exec app composer install
```

* Run the migrations:
```sh
docker compose exec app php artisan migrate
```

* You can now execute the tests:
```sh
docker compose exec app php artisan test
```

* And access the documentation at:
```sh
http://localhost/docs/api
```

## How to use

### 1 - Make a transfer

Send a `POST` request to `/api/transfer` with the transfer data:

![Content creation image](https://raw.githubusercontent.com/benjamimWalker/pesc-pay/master/assets/transfer.png)

[Benjamim] - [benjamim.sousamelo@gmail.com]<br>
Github: <a href="https://github.com/benjamimWalker">@benjamimWalker</a>

