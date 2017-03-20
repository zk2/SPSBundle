Column options
==============

Supported types
---------------

    - **string** - common strings
    - **numeric** - numeric data (formatting is available)
    - **boolean** - "Yes" or "No"
    - **datetime** - date(time) (formatting is available)
    - **image** - any icon/image

Common options
--------------

    - **class** - html attribute ``class`` to tag ``<td/>``
    - **style** - html attribute ``style`` to tag ``<td/>``
    - **link** - allows to make the current value as the html link
    
        - **route** - the name of symfony Route
        - **route_params** - array of Route parameters (if exist). Key - RouteParameterName, value - сan be a string-constant, otherwise, if we work with objects - this is the name of the property of this object, and if we work with an array - this is the alias of the desired value (e.g. ``['id' => 'id'] or ['id' => 'country_id']``)
        - **link_class** - html attribute ``class`` to tag ``<a/>``
        - **link_style** -html attribute ``style`` to tag ``<a/>``
        - **text** - if defined - it's override current value
        - **on_click** - javascript property (e.g. ``return confirm("Are you sure?")``)
        - **link_javascript** - if defined - attribute ``href`` will consist of ``javascript:void(0)`` and will be added attribute ``data-id`` with JS-object ``json_encode($linkRouteParameters)``

Options for numeric collumn
---------------------------

    - **number_format** - array (the same of PHP function: see http://php.net/manual/en/function.number-format.php)
    - **autosum** - unique string (e.g. number_of_cities_sum)


Options for datetime collumn
---------------------------

    - **format** - string (the same of PHP calss DateTime::format)
    - **timezone** - string (the same of PHP calss DateTimeZone::__construct)


Options for boolean collumn
---------------------------

    - **boolean_view** - default "icon" - the values will be ``<i class="glyphicon glyphicon-{check/unchecked}"></i>``
    - **revers** - if defined - the true values will be "No" and the false - "Yes" (by default vice versa)


Options for image collumn
---------------------------

    - **image_web_path** - the prefix to which the value is contacted to get the full web path to the image
    - **image_by_default** - If image does not exist
    - **image_width** - The image is compressed by the browser to the specified width
    - **image_title** - The attribute ``title`` for tag ``<img/>``
    - **image_alt** - The attribute ``alt`` for tag ``<img/>``
