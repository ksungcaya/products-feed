## Products Feed

A simple app made with [Laravel Framework](http://laravel.com) that parses an XML products feed from an external source with minimum CPU and memory consumptions.

### Features

- A form that accepts a valid feed url
- URL validation
- Can process large XML files
- Products feed pagination (load more...)
- User friendly error messages
- Single product modal view

### Installation

1. Clone the Products Feed repository.

    ```bash
    git clone https://github.com/ksungcaya/products-feed.git products-feed
    ```
2. Navigate through the folder.

    ```bash
    cd products-feed
    ```
3. Rename the `.env.example` file to `.env`
4. Please make sure to have [composer](https://getcomposer.org/) installed on your machine and do a `composer install` inside the directory.
5. Generate an app key.

    ```bash
    php artisan key:generate
    ```
6. Finally, run `php artisan serve` and navigate through the url that will be shown after running the command.

### Tests
If you want to run the tests, it's very easy. From your terminal, navigate through the app's root directory and run
```bash
vendor/bin/phpunit
```

and let's hope it passes all the tests. :)

### Todo list

- Add Docker Image


### License

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
