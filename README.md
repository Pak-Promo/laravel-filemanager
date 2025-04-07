# Media Library for Laravel

This package can associate images with Eloquent models.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pakpromo/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/pakpromo/laravel-filemanager)
[![Total Downloads](https://img.shields.io/packagist/dt/pakpromo/laravel-filemanager.svg?style=flat-square)](https://packagist.org/packages/pakpromo/laravel-filemanager)

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

You need to publish the migration to create the media table:

```bash
php artisan vendor:publish --provider="PakPromo\FileManager\FileManagerServiceProvider" --tag=filemanager-migration
```

After that, you need to run migrations.

```bash
php artisan migrate
```

## Usage

Your Eloquent models should use the `PakPromo\FileManager\Traits\HasMedia` trait.

Use blade component to add file uploader in your form.

```php
<x-medialibrary-file-upload name="image" />
```

For display old image in edit page.

```php
<x-medialibrary-file-upload name="image" :model="$model" />
```

## Upload

```php
$model = Model::find(1);
$model->handleMediaFromRequest()->toMediaCollection();
```

If your file input name is not `image` then define second param.

```php
$model->handleMediaFromRequest('banner')->toMediaCollection();
```

Upload to specific collection.

```php
$model->handleMediaFromRequest()->toMediaCollection('images');
```

You can define default collection at eloquent level. Add below function in your model.

```php
public function defaultCollection(): string
{
    return 'post_images';
}
```

Upload to specific disk.

```php
$model->handleMediaFromRequest()->useDisk('s3')->toMediaCollection();
```

### Register Media Conversions

```php
public function registerMediaConversions()
{
    $this->addMediaConversion('post_main')
        ->width(420)
        ->height(350);
}
```

You can register as many media conversions as you want

```php
public function registerMediaConversions()
{
    $this->addMediaConversion('post_main')
        ->width(420)
        ->height(350);

    $this->addMediaConversion('post_detail')
        ->width(700)
        ->height(550);
}
```

Default force crop is disabled, but you can enable it

```php
$this->addMediaConversion('post_main')
    ->width(420)
    ->height(350)
    ->crop();
```

### Disable Conversions

If you want to disable registered conversions on some files

```php
$model->handleMediaFromRequest()->withoutConversions()->toMediaCollection();
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
<x-medialibrary-dropzone name="gallery" />
```

Attach gallery to model using blelow code.

```php
$model->attachGalleryToModelFromRequest('gallery')->toMediaCollection();
```

You can also define collection for gallery.

```php
<x-medialibrary-dropzone name="gallery" collection="dropzone" />
```

You can define model to dropzone component as well.
When you define model to component all images are automatically attached to model.

```php
<x-medialibrary-dropzone name="gallery" :model="$model" />
```

You can also change the default dropzone message.

```php
<x-medialibrary-dropzone name="gallery" message="Drop files here" />
```

## Add Media from Url

```php
$model->addMediaFromUrl($url, 'image')->toMediaCollection();
```

## Implements with Laravel Settings

Install settings package

```bash
composer require pakpromo/laravel-settings
```

Blade component to display old file

```php
<x-medialibrary-file-upload name="image" setting="{{ setting()->get('name') }}" />
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
