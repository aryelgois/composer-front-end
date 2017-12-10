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

[getcomposer]: https://getcomposer.org/
