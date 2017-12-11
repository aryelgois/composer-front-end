> A utility to install front-end files with [Composer][getcomposer]

# Intro

By default Composer installs all package files under the vendor directory.  
It is ok for Composer's main purpose: a tool for dependency management in PHP.

But often, PHP projects involve other languages, like those used in front-end
(JavaScript, CSS and their derivatives).

You could use different package managers to deal with each kind of file, but it
would be too much work, and I think it is good to have a unified tool.

This is where this package comes in. You will use Composer to fetch repositories
containing front-end assets, and this package will symlink them to their
appropriate directory.


# Install

Run this inside your project:

`composer require aryelgois/composer-front-end`

> In order to avoid Composer warnings during a fresh install, keep the package
> entry at the beginning of composer.json `require` directive.


# Setup

Add this to your composer.json:

```json
{
    "scripts": {
        "post-package-install": "aryelgois\\Composer\\FrontEnd::postPackageInstall",
        "front-end-refresh": "aryelgois\\Composer\\FrontEnd::refresh"
    },
}
```

Now, whenever you install a new package with front-end files, they will be
symlinked.

If you already installed some front-end packages, run
`composer front-end-refresh` to look in every vendor package.


# Config files

These JSON files are used to tell which files should be symlinked and where.

You could use any key you like, but it needs to be the same in the dependency
config and in your project.


## frontend.json

Used in vendor packages.

It contains a map of `'file_group': ['file']` of files to be symlinked into your
package.  
Paths are relative to the vendor package directory.

> not `vendor/`, but `vendor/some/package/`

### Example:

```json
{
    "css": "assets/superduperstyle.css",
    "js": [
        "main.js",
        "src/myawesomescript.js"
    ]
}
```

It is possible to specify a single file without an array.


## frontend-config.json

Used in your project.

It can include the following keys:

#### directories

_(required)_

A map of `'file_group': 'directory'` to contain symlinks from other packages.  
Paths are relative to the root directory.

#### packages

_(optional)_

A map of `'package/name': files` to be symlinked. The content would be the same
as in frontend.json.

Useful when a vendor does not include the frontend.json.

#### structure

_(optional)_

Defines how the symlinks are placed in the directories:

* **nest**: Symlinks will be created at `vendor/package/`, inside the specified
  directories. It helps with files with same name.
* **flat**: Symlinks will stay directly inside the defined directories.


#### structure_default

_(optional)_

Defines the default structure for all directories. If not specified, `nest` is
used.

### Example:

```json
{
    "directories": {
        "css": "public/css",
        "js": "public/js"
    },
    "structure": {
        "css": "flat",        
    }
}
```


# Notes

* You still need to manually add the markup to use the symlinked files in your
  webpage:

  ```html
<head>
    <link rel="stylesheet" href="/css/superduperstyle.css" />
</head>
<body>
    <script src="/js/some/package/myawesomescript.js"></script>
    <script src="/js/some/package/main.js"></script>
</head>
  ```

* Even with `nest`, all symlinks are placed flattened, i.e., the file structure
  in the vendor package is not preserved.


# TODO

* [ ] Add path expansion for frontend.json


[getcomposer]: https://getcomposer.org/
