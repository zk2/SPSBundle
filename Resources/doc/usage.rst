Usage
=====

Initialize service in your controller class

.. code-block:: php

    $sps = $this->get('zk2.sps');

If the user presses the "Reset" button - it is necessary to reset all previously selected filters, pagination, sorting

.. code-block:: php

    if ($url = $sps->isReset()) {
        return $this->redirect($url);
    }

If the user has applied some filters, flipped to some pages, sorted some fields ,
and then, for example, moved on the link for editing / viewing,
then returning back to the list he expects to see the page on which it was
(with the selected filters, sorters, page number)
All selected parameters are serialized and saved in the session.
Parameter ``session_key`` from `settings`_ responsible for formation key session. There are three possible cases:
    - ``by_route`` - key formation by ``_route`` (by default) - in the current browser,
      opening a new tab with the same _route will result in applying the same filters as in the previous one
    - ``by_query`` - key formation by query_string parameter ``_sps_qk`` -
      you can open the same page in several tabs but with different filters.
      (In this case, you should care that links as "back to link" contain the parameter ``_sps_qk``)
    - ``null`` - no parameters are serialized or saved.
Using ``session_key: by_query`` the previous block of code should look like
(to generate a key, if it's not existed yet)

.. code-block:: php

    if ($url = $sps->isReset() or $url = $sps->isForward()) {
        returns $this->redirect($url);
    }

Next, create QueryBuilder - one of the ``Doctrine\DBAL\Query\QueryBuilder`` or ``Doctrine\ORM\QueryBuilder``
The choice should be made based on the specific situation. The differences between these QueryBuilders are as follows:
    - ``ORM QueryBuilder`` uses DQL and returns array of objects or array of strings.
      In the case of objects, you can use not only properties in the table columns, but also methods
      (used ``Symfony\Component\PropertyAccess\PropertyAccess`` component). However, it should be noted that it is not always possible to filter and sort by such a column
    - ``DBAL QueryBuilder`` use nature SQL queries. This allows you to use any SQL construct that returns data in a table view.

.. code-block:: php

    // ORM QueryBuilder (objects)
    $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
        ->select('country, continent, cities')
        ->from('AppBundle:Country', 'country')
        ->leftJoin('country.continent', 'continent')
        ->leftJoin('country.cities', 'cities');

    // ORM QueryBuilder (array)
    $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
        ->select(
            'country.id AS country_id',
            'country.name AS country_name',
            'country.lastDate AS country_last_date',
            'region.name AS region_name',
            'region.id AS region_id',
            'COUNT(cities.id) AS number_of_cities'
        )
        ->from('AppBundle:Country', 'country')
        ->leftJoin('country.continent', 'continent')
        ->leftJoin('country.cities', 'cities');

    // DBAL QueryBuilder
    $connection = $this->getDoctrine()->getManager()->getConnection();
    $qb = $connection->createQueryBuilder()
        ->select(
            'country.id AS country_id',
            'country.name AS country_name',
            'country.last_date AS country_last_date',
            'region.name AS region_name',
            'region.id AS region_id',
            'COUNT(cities.id) AS number_of_cities'
        )
        ->from('country', 'country')
        ->leftJoin('country', 'region', 'region', 'country.region_id = region.id')
        ->leftJoin('country', 'city', 'cities', 'country.id = cities.country_id')
        ->groupBy('country.id')
        ->addGroupBy('region.id');

Next, we specify the columns that we want to display in the table.
Method ``addColumn`` takes 3 arguments: displayed property, type and array of options.
More details about supported types and options can be found here `column_options`_

.. code-block:: php

    // For case with ORM QueryBuilder (objects)
    // method getCountCities() must be implemented in Entity
    $sps
        ->addColumn('country.name', 'string', ['label' => 'Continent'])
        ->addColumn('country.lastDate', 'datetime', ['label' => 'Date', 'format' => 'Y-m-d'])
        ->addColumn('country.countCities', 'numeric', ['label' => 'Number of cities'])
        // ....................
    ;

    // For case with ORM QueryBuilder (array) and DBAL QueryBuilder
    $sps
        ->addColumn('country_name', 'string', ['label' => 'Continent'])
        ->addColumn('country_last_date', 'datetime', ['label' => 'Date', 'format' => 'Y-m-d'])
        ->addColumn('number_of_cities', 'numeric', ['label' => 'Number of cities'])
        // ....................
    ;

**NOTICE**
**You can implement your own service that processes your options to meet your needs. To do this, create a class that implements the Zk2\\SpsBundle\\Model\\TdBuilderInterface, and specify it in the** `settings`_

To create a form with filters, use the ``addFilter`` method.
It also takes 3 arguments: filtered property, type and array of options.
More details about supported types and options can be found here `filter_options`_

.. code-block:: php

    $sps
        ->addFilter('country.name', 'string', ['quantity' => 2])
        ->addFilter('country.lastDate', 'dateRange') // or 'country_last_date' for ORM (array) and DBAL
        // ....................
    ;

Next

.. code-block:: php

    $sps
        ->setEmName('custom_entity_manager_name') // OPTIONAL :: only if doctrine entity_manager name != 'default'
        ->setQueryBuilder($qb)
        ->setLimitRows($limitRows) // OPTIONAL :: default 30
        ->setDefaultSort(
            ['country.name' => 'asc', 'region.name' => 'desc']
        ) // OPTIONAL :: sorting by default (if no filters are selected)
    ;

And, finally,

.. code-block:: php

    $result = $sps->buildResult(); // This return array with keys: 'filter', 'paginator', 'autosum'

    return $this->render('default/country.html.twig', $result);



.. _settings: https://github.com/zk2/SPSBundle/blob/dev/Resources/doc/settings.rst
.. _column_options: https://github.com/zk2/SPSBundle/blob/dev/Resources/doc/column_options.rst
.. _filter_options: https://github.com/zk2/SPSBundle/blob/dev/Resources/doc/filter_options.rst
