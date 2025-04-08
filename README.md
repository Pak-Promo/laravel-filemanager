# File Manager for Laravel

[![PHP Composer](https://github.com/Pak-Promo/laravel-filemanager/actions/workflows/php.yml/badge.svg)](https://github.com/Pak-Promo/laravel-filemanager/actions/workflows/php.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/pakpromo/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/pakpromo/laravel-filemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/pakpromo/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/pakpromo/laravel-filemanager)

Manage Files, Images, Docs with Eloquent models.

## Getting Started

### 1. Install

Run the following command:

```bash
composer require pakpromo/laravel-filemanager
```

### 2. Publish

Publish config file.

```bash
php artisan vendor:publish --provider="PakPromo\FileManager\FileManagerServiceProvider" --tag=filemanager-config
```

### 3. Preparing the database

You need to publish the migration to create the file table:

```bash
php artisan vendor:publish --provider="PakPromo\FileManager\FileManagerServiceProvider" --tag=filemanager-migration
```

After that, you need to run migrations.

```bash
php artisan migrate
```

## Usage

Your Eloquent models should use the `PakPromo\FileManager\Traits\HasFile` trait.

Use blade component to add file uploader in your form.

```php
<x-filemanager-file-upload name="image" />
```

For display old image in edit page.

```php
<x-filemanager-file-upload name="image" :model="$model" />
```

## Upload

```php
$model = Model::find(1);
$model->handleFileFromRequest()->toFileCollection();
```

If your file input name is not `image` then define second param.

```php
$model->handleFileFromRequest('banner')->toFileCollection();
```

Upload to specific collection.

```php
$model->handleFileFromRequest()->toFileCollection('images');
```

You can define default collection at eloquent level. Add below function in your model.

```php
public function defaultCollection(): string
{
    return 'promo_images';
}
```

Upload to specific disk.

```php
$model->handleFileFromRequest()->useDisk('s3')->toFileCollection();
```

### Register File Conversions

```php
public function registerFileConversions()
{
    $this->addFileConversion('promo_image')
        ->width(420)
        ->height(350);
}
```

You can register as many file conversions as you want

```php
public function registerFileConversions()
{
    $this->addFileConversion('promo_image')
        ->width(420)
        ->height(350);

    $this->addFileConversion('banner')
        ->width(700)
        ->height(550);
}
```

Default force crop is disabled, but you can enable it

```php
$this->addFileConversion('promo_image')
    ->width(420)
    ->height(350)
    ->crop();
```

### Disable Conversions

If you want to disable registered conversions on some files

```php
$model->handleFileFromRequest()->withoutConversions()->toFileCollection();
```

## Configuration

Define your layout stack in config file.

```bash
'stack' => 'footer',
```

Or you can use our blade directive.

```bash
@filemanagerScript
```

## Gallery with Dropzone

```php
<x-filemanager-dropzone name="gallery" />
```

Attach gallery to model using blelow code.

```php
$model->attachGalleryToModelFromRequest('gallery')->toFileCollection();
```

You can also define collection for gallery.

```php
<x-filemanager-dropzone name="gallery" collection="dropzone" />
```

You can define model to dropzone component as well.
When you define model to component all images are automatically attached to model.

```php
<x-filemanager-dropzone name="gallery" :model="$model" />
```

You can also change the default dropzone message.

```php
<x-filemanager-dropzone name="gallery" message="Drop files here" />
```

## Add File from Url

```php
$model->addFileFromUrl($url, 'image')->toFileCollection();
```

## Implements with Laravel Settings

Install settings package

```bash
composer require pakpromo/laravel-settings
```

Blade component to display old file

```php
<x-filemanager-file-upload name="image" setting="{{ setting()->get('name') }}" />
```

To upload file

```php
setting()->upload($request->toArray(), 'file_name');
```

By default we expect file name is your option name, but you can define your option name as well

```php
setting()->upload($request->toArray(), 'file_name', 'option_name');
```

### Get Uploaded File Url

```php
setting()->getFile('name');
```

## Changelog

Please see [Releases](../../releases) for more information what has changed recently.

## Contributing

Pull requests are more than welcome. You must follow the PSR coding standards.

## Security

If you discover any security related issues, please email snippetcms@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
