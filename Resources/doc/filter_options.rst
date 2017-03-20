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
    - **single_field** - default false. Used to display only the main filter field, without the possibility of specifying comparison_operator (the default comparison_operator is "=").
    - **comparison_operator_hidden** - default "=". Used with ``single_field`` option for override default comparison_operator.

Options for date and dateRange filters
--------------

    - **model_timezone** - default PHP ``date_default_timezone_get()``. This parameter pass to the Symfony DateTimeType.
    - **view_timezone** - default PHP ``date_default_timezone_get()``. This parameter pass to the Symfony DateTimeType.

Available comparison operators
-----------------------------

    - **eq** - equals
    - **not_eq** - not equals
    - **_like_** - contains
    - **not__like_** - not contains
    - **like_** - begins with
    - **_like** - ends with
    - **not_like_** - not begins with
    - **not__like** - not ends with
    - **less** - less than
    - **less_eq** - less than or equal
    - **more** - greater than
    - **more_eq** - greater than or equal
    - **is_empty** - is empty
    - **is_not_empty** - is not empty
    - **bool_** - equals
    - **between** - between
    - **not_between** - not between

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
