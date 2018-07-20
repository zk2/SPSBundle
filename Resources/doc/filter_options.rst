Filter options
==============

Supported types
---------------

    - **string** - common strings
    - **numeric** - numeric data
    - **boolean** - "Yes" or "No"
    - **date** - date (by PHP DateTime::format)
    - **dateRange** - range of two dates
    - **choice** - array (html widget "choice")

Common options
--------------

    - **quantity** - default 1. Number of filters instances of this field.
    - **label** - default "humanize" of field name. Label of this filter field.
    - **comparison_operators** - array of comparison operators (see bellow)
    - **not_used** - default false. If true - the field does not participate in the construction of the SQL query (this field could be processed in controller)
    - **comparison_operator_hidden** - default null. If defined, the "Select" element will be replaced by a input-hidden field.
    - **function** - default null. Array like this: ['aggregate' => true, 'definition' => 'count({property})']

Options for date and dateRange filters
--------------

    - **model_timezone** - default PHP ``date_default_timezone_get()``. This parameter pass to the Symfony DateTimeType.
    - **view_timezone** - default PHP ``date_default_timezone_get()``. This parameter pass to the Symfony DateTimeType.

Available comparison operators
------------------------------

    - **equals** - equals
    - **notEquals** - not equals
    - **contains** - contains
    - **notContains** - not contains
    - **beginsWith** - begins with
    - **endsWith** - ends with
    - **notBeginsWith** - not begins with
    - **notEndsWith** - not ends with
    - **lessThan** - less than
    - **lessThanOrEqual** - less than or equal
    - **greaterThan** - greater than
    - **greaterThanOrEqual** - greater than or equal
    - **isNull** - is null
    - **isNotNull** - is not null
    - **between** - between
    - **notBetween** - not between

There are ready-made sets
-------------------------
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::full()`` - get all operators
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::eqNotEq()`` - equals, not equals
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::fullText()`` - contains, equals, not equals, begins with, ends with, not contains, not begins with, not ends with, is empty, is not empty
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::fullInt()`` - equals, not equals, less than, less than or equal, greater than, greater than or equal, is empty, is not empty
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::mediumText()`` - contains, equals, not equals, not contains, is empty, is not empty
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::mediumInt()`` - equals, not equals, less than, greater than, is empty, is not empty
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::smallText()`` - contains, equals, not equals, not contains
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::smallInt()`` - equals, not equals, less than, greater than
    - ``Zk2\SpsBundle\Utils\ComparisonOperator::between()`` - between, not between
