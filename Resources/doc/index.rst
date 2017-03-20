Zk2SpsBundle
============

The **Zk2SpsBundle** is implemented as a service that get Doctrine\DBAL\Query\QueryBuilder
or Doctrine\ORM\QueryBuilder and returned form of filters and array with data
for use in your view layer.

Installation
------------

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require zk2/sps-bundle

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the ``app/AppKernel.php`` file of your project:

.. code-block:: php

    <?php
    // app/AppKernel.php

    // ...
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                // ...
                new Zk2\SpsBundle\Zk2SpsBundle(),
            );

            // ...
        }

        // ...
    }

Step 3: Install assets
----------------------

.. code-block:: bash

    $ php bin/console assets:install --symlink

Usage
-----

Your controller ``AppBundle:DefaultController``

.. code-block:: php

    <?php
    // src/AppBundle/Controller/DefaultController.php

    class DefaultController extends Controller
    {
        public function indexAction()
        {
            $sps = $this->get('zk2.sps');

            if ($url = $sps->isReset()) {
                return $this->redirect($url);
            }

            $qb = $this->getDoctrine()->getManager()->createQueryBuilder()
                ->select('country, continent')
                ->from('AppBundle:Country', 'country')
                ->leftJoin('country.continent', 'continent');

            $sps
                ->addColumn('country.name', 'string', [
                    'link' => ['route' => 'country_view', 'route_params' => ['id' => 'id']]
                ])
                ->addColumn('country.code', 'string', ['sort' => false])
                ->addColumn('continent.name', 'string', ['label' => 'Continent'])
                ->addColumn('country.surfaceArea', 'numeric', ['label' => 'Surface area'])
                ->addColumn('country.lastDate', 'datetime', ['label' => 'Date']);

            $sps
                ->addFilter('country.name', 'string', ['quantity' => 3])
                ->addFilter('region.id', 'choice', [
                    'label' => 'Region',
                    'choices' => $this->getDoctrine()->getManager()->createQuery(
                        "SELECT i.id,i.name FROM AppBundle:Region i ORDER BY i.name"
                    )->getResult(),
                ])
                ->addFilter('country.lastDate', 'dateRange');

            $sps
                ->setQueryBuilder($qb)
                ->setDefaultSort(['country.name' => 'asc']);

            $result = $sps->buildResult(); //['filter' => ..., 'paginator' => ...]

            return $this->render('default/country.html.twig', $result);
        }
    }

Your template ``default/country.html.twig``

.. code-block:: twig

    {% extends '......html.twig' %}

    {% block stylesheets %}
        <link href="{{ asset('bundles/zk2sps/css/bootstrap.min.css') }}" rel="stylesheet" media="screen">
        <link href="{{ asset('bundles/zk2sps/datepicker/css/bootstrap-datepicker3.standalone.min.css') }}" rel="stylesheet" />
        <link href="{{ asset('bundles/zk2sps/css/sps-style.css') }}" rel="stylesheet"/>
    {% endblock %}

    {% block body %}

        {{ sps_filter_form(filter) }}

        {{ sps_filter_table(paginator) }}

    {% endblock %}

    {% block javascripts %}
        <script src="{{ asset('bundles/zk2sps/js/jquery.min.js') }}"></script>
        <script src="{{ asset('bundles/zk2sps/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('bundles/zk2sps/datepicker/js/bootstrap-datepicker.min.js') }}"></script>
        <script src="{{ asset('bundles/zk2sps/js/sps.js') }}"></script>
    {% endblock %}


`Demo`_


.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md
.. _Demo: https://sf.zeka.pp.ua