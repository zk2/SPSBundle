Zk2SpsBundle
============

Bundle for Symfony2 Standard Edition, designed to facilitate the work with large sets
of table data. SPS is an acronym for Search, Pagination, Sort,
that is the bundle can apply filters, ensure pagination,
sort data.

Bundle is implemented as a service that get Doctrine\DBAL\Query\QueryBuilder
or Doctrine\ORM\QueryBuilder and return form of filters and array with data
for use in your view layer.

Demo
----
[/postgres/doctrine_country](https://sps.sf2.pp.ua)

Documentation
-------------

[Quick start](https://github.com/zk2/SPSBundle/blob/master/Resources/doc/index.rst)

[Custom settings](https://github.com/zk2/SPSBundle/blob/master/Resources/doc/settings.rst)

[Usage](https://github.com/zk2/SPSBundle/blob/master/Resources/doc/usage.rst)

[Column options](https://github.com/zk2/SPSBundle/blob/master/Resources/doc/column_options.rst)

[Filter options](https://github.com/zk2/SPSBundle/blob/master/Resources/doc/filter_options.rst)

Running the Tests
-----------------

Install the [Composer](http://getcomposer.org/) `dev` dependencies:

    php composer.phar install --dev

Then, run the test suite using
[PHPUnit](https://github.com/sebastianbergmann/phpunit/):

    phpunit

License
-------

This bundle is released under the MIT license. See the complete license in the bundle:

    Resources/meta/LICENSE
    
