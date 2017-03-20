Custom settings
===============

Bundle configuration in ``app/config/config.yml``

.. code-block:: yaml

    # app/config/config.yml

    # Default configuration for "Zk2SpsBundle"
    zk2_sps:
        templates:

            # Pagination template
            pagination_template:  'Zk2SpsBundle:Template:pagination.html.twig'

            # Sortable template
            sortable_template:    'Zk2SpsBundle:Template:sortable.html.twig'

            # Filter form template
            filter_template:      'Zk2SpsBundle:Template:filter.html.twig'

            # General table template
            table_template:       'Zk2SpsBundle:Template:table.html.twig'

        # Service to build HTML <td>{content}</td> (must implement TdBuilderInterface)
        td_builder_service_class: Zk2\SpsBundle\Model\TdBuilderService

        # Key for the session: "by_route", "by_query" or null
        session_key:              by_route

