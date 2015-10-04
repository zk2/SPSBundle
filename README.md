#SPSBundle
I present you the bundle for Symfony2, designed to facilitate the work with large sets of table data. SPS is an acronym Search Pagination Sort (Search pagination sorting), that is the bundle can apply filters to ensure pagination, sorting data.

The demonstration can be found [**here**][1], Описание на русском [**здесь**][2].

At the entrance, we have one of: array, Doctrine\ORM\Query, Doctrine\ORM\QueryBuilder, Doctrine\Common\Collection\ArrayCollection
At the exit:

    Fields selected from within the desired order.
    A certain number of lines per page, with the ability to navigate through the pages.
    Filters (filter combination (AND / OR), with operators (=,! =, LIKE, etc ...)).
    Sort by certain fields

This is not something new, but compared with other implementations (eg Sonata), the bundle does not try to grasp the immensity, and performs only what is written above.

I will begin immediately with an example of use -

Controller:

    namespace AppBundle\Controller;

    use .....

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            $sps = $this->get('zk2.sps.doctrine')
                ->setRoot('AppBundle\Entity\Model', 'm');

            if ($url = $sps->isReset()) {
                return $this->redirectToRoute($url);
            }

            $sps
                ->buildQuery()
                ->getQuery()
                ->select('m')
            ;

            $sps
                ->addColumn('m','name','string')
                ->addColumn('m','status','string')
            ;

            $sps
                ->addFilter('m', 'id', 'numeric')
                ->addFilter('m','name','text')
            ;

            $result = $sps->buildResult();
            return $this->render('default/index.html.twig',array(
                'result' => $result,
            ));
        }

template:

    {% extends 'base.html.twig' %}

    {% block zk2_content %}

    <div class="sps-area">
    
        {% include 'Zk2SPSBundle:Form:filter.html.twig' with {
            'filter_form': result.filter_form,
        } %}
    
        {% include 'Zk2SPSBundle:Form:table.html.twig' with {
            'columns': result.columns,
            'paginator': result.paginator,
        } %}
    
    </div>
    
    {% endblock %}

This example will display a table with the name and status fields and filters in the fields id and name from the collection 'AppBundle \ Entity \ Model'

Now everything in more detail

Installation and Setup

    composer require "zk2/sps-bundle" "1.0.*"

Bundle uses knplabs/knp-paginator-bundle, braincrafted/bootstrap-bundle и oro/doctrine-extensions. If they are not in your application, it will be installed.

In app/AppKernel.php:

    public function registerBundles()
    {
        return array(
            // ...
            // If you do not have these bundles - prescribe
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),
            // Our bundle
            new Zk2\SPSBundle\Zk2SPSBundle(),
            // ...
        );
    }

If you do not have BraincraftedBootstrapBundle, after installation, you must configure. Adjusting well described [**here**][3] if quick, then:

    # app/config/config.yml
    .......
    # Assetic Configuration
    assetic:
        debug:          "%kernel.debug%"
        use_controller: false
        bundles:        [ ]
        filters: # using a node
            less:
                node: /usr/bin/node # way to learn you can do $ whereis node
                node_paths: [/usr/lib/node_modules] # $ whereis node_modules
                apply_to: "\.less$"
            cssrewrite: ~
    braincrafted_bootstrap:
        less_filter: less
        jquery_path: %kernel.root_dir%/../web/js/jquery-1.11.1.js # path to jQuery

We continue to carry out:

    php app/console braincrafted:bootstrap:install 
    php app/console assetic:dump

SPSBundle has the following default settings:

    timezone_db - the time zone in which to store datetime in the database (the default output of the PHP function date_default_timezone_get ())
    pagination_template - navigation template (default Zk2SPSBundle:Form:pagination.html.twig)
    sortable_template - the pattern of the first (header) row of the table (the default Zk2SPSBundle:Form:sortable.html.twig)

If necessary, they can be overridden in the app/config/config.yml

    zk2_sps:
        timezone_db: UTC
        pagination_template: YouBundle:SPS:pagination.html.twig
        sortable_template: YouBundle:SPS:sortable.html.twig

Using

SPS - is a service. Bundle has two pre-configured service subsidiaries:

     zk2.sps.doctrine - support for working with ORM Doctrine (DoctrineQueryBuilder)
     zk2.sps.doctrine.native - Service to work with native SQL statements in the Doctrine (NativeQuery)

For the first case can be two uses: a collection of objects -> select ('a, b') or an array -> select ('a.id, a.name, b.title')
For the second - only the array
For example it will be clearer. Small application "Cars" The structure of classical - Country -> Brand -> Model. Do not judge strictly for the completed data - all "from the lantern" .:

## Wrapper for all the examples below

    class DefaultController extends Controller
    {
        /**
         * @Route("/", name="homepage")
         */
        public function indexAction()
        {
            $sps = $this->get('нужный сервис')
                ->setRoot('AppBundle\Entity\Model', 'm'); // The main entity and alias

            // Reset all filters
            if ($url = $sps->isReset()) {
                return $this->redirectToRoute($url);
            }

            $sps->buildQuery()->getQuery()
                ->select(/* here query body */)
                ->leftJoin('m.brand', 'b')
                ->leftJoin('b.country', 'c');

            $sps->addColumn(/* column of the table */);
        
            $sps->addFilter(/* filters */);
            
            $result = $sps->buildResult(); // Initialize the result
    
            $sps->setPaginatorLimit(30); // Elements on the page
    
            return $this->render('default/index.html.twig',array(
            'result' => $result, // in template
        ));
    }

### method setRoot

    $sps->setRoot(
        // The main entity of the (relevant only for service 'zk2.sps.doctrine')
        'AppBundle\Entity\Model',
        // Main alias
        'm'
    );

### Method isReset intercepts pressing the "Reset filters"

    if ($url = $sps->isReset()) {
        return $this->redirectToRoute($url);
    }

### Method ->select() builds a query

    # for the zk2.sps.doctrine query can be of two types: for working with objects and working with arrays
    # To work with objects
    $sps->buildQuery()->getQuery()
        ->select('m,b,c') 
        ->leftJoin('m.brand', 'b') 
        ->leftJoin('b.country', 'c');

    # Work with arrays
    $sps->buildQuery()->getQuery()
        ->select(
            'b.id AS brand_id,b.name AS brand_name,c.name AS country_name,b.logo,m.id AS id,m.name,'
            .'m.color,m.airbag,m.sales,m.speed,m.price,m.dateView'
        )
        ->leftJoin('m.brand', 'b')
        ->leftJoin('b.country', 'c');

    # for the zk2.sps.doctrine.native - normal query (MySQL, PostgreSQL)
    $sps->buildQuery(
        "SELECT m.id, m.name, b.name AS brand_name FROM model m JOIN brand b ON m.brand_id=b.id",
        array('id','name','brand_name',...)
    ); // In the array should be listed column (aliases) of the block select

### Method ->addColumn()

    $sps->addColumn(
        'b', // alias.
        'name', // the property of an entity or a key (the field) array
        'string', // type of column ('string','numeric','boolean','image','datetime','button')
        array( // array parameters
            // The default is the root property entity, and if there were m.name can not prescribe the method parameter
            // In this case, this property is the essence of "Brand",
            // Query for object here is 'getBrandName' - a technique that must be defined in essence Model
            // Array will be here 'brand_name'
            'method' => 'brand_name',
            // Other options ......
        )
    );

options for the type column:

    string - value is interpreted as a string
    numeric - a numeric value. If this float, the value is wrapped in number_format function with parameters (0, '.', ''). Override the format can be in an array of parameters 'number_format' => array (2, ',', '.')
    boolean - is interpreted as "Yes" / "No". You can invert, this parameter is added to the array 'revers' => true,
    image - wrapped in a tag "img". The value should be the name of the image file. Extra options:
        - 'image_root_path' - a web path to the image
        - 'image_width', 'image_title', 'image_alt' - paramerrov titles speak for themselves
        - If the value of the match to the 'http' - 'image_root_path' ignored
    datetime - date. Additional parameters: format ('Y-m-d H:i:s' by default) and the timezone (date_default_timezone_get() by default)
    button - the button will be printed with the words, certain parameter 'button_value' ('Button' by default)

Options addColumn:

In addition to the above-described specific paramertov for the types of speakers, there are common parameters

    method - described above in Example
    link - 'link' => array ('route' => 'app_model_edit') --- the value will turn to tag <a/>, formed with the href attribute of the Route named 'app_model_edit'. By default, the parameter name Route - 'id', value - id root entity. To link to edit the adjacent entities ('link' => array ('route' => 'app_brand_edit')) is determined by the parameter 'lid' => 'brand_id'. Parameter 'click' may take a value of type 'onclick = "return confirm (' Are you sure? ')"'. Also supported parameters and link_class link_style, speak for themselves
    link_javascript - link to process javascript. Options: link_javascript_class - class attribute references, link_javascript_lid - id entity (the default value - id root essence. It is possible to determine how brand_id, then it will be id value adjacent entities). The href attribute in these ssylon - "javascript:void(0)" attribute "data-id" - the value of link_javascript_lid, for data-id and written handler.
    zk_list - normal mapping of the key in the value. This option must be an array of all possible values ​​(array (0 => 'value1', 1 => 'value2', 2 => value3 ', ...))
    label - the name of the column
    sort - whether the ability to sort by field (default - true)


# Method ->addFilter()

    $sps->addFilter(
        'b', // alias
        'id', // the property of an entity or a key (the field) array
        'choice',  // filter type ('text', 'numeric', 'choice', 'boolean', 'date')
        array(
            'quantity' => 2, // number of filters for the field
            // Other parameters .....
        )
    );

options for filter type:

    text - the value is interpreted as a string
    numeric - a numeric value. If the input is not the number of work validator
    choice - a drop-down list (elements are defined in the parameter "choices")
    boolean - drop-down list with the elements of "Yes" / "No"
    date - a date picker widget

Options addFilter:

    quantity - number of filters for the field
    choices - an array of choices. There is a method to facilitate the formation of an array of nature: 'choices' => $sps->getEm ()->createQuery("SELECT b.id, b.name FROM AppBundle: Brand b ORDER BY b.name")->getResult()
    label - the name of the filter (the default name of the column from the list)
    

[1]:  http://zk-sps.zeka.guru/
[2]:  http://zeka.kiev.ua/symfony2-spsbundle-search-pagination-sor/
[3]:  http://bootstrap.braincrafted.com/getting-started.html#configuration
