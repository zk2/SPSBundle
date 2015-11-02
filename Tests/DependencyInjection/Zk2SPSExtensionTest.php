<?php

namespace Zk2\SPSBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Zk2\SPSBundle\DependencyInjection\Zk2SPSExtension;
use Symfony\Component\Yaml\Parser;

class Zk2SPSExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ContainerBuilder */
    protected $configuration;

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUnsetOptionsPaginationTemplate()
    {
        $loader = new Zk2SPSExtension();
        $config = $this->getFullConfig();
        unset($config['options']['pagination_template']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUnsetOptionsSortableTemplate()
    {
        $loader = new Zk2SPSExtension();
        $config = $this->getFullConfig();
        unset($config['options']['sortable_template']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testUnsetOptionsTimezoneDb()
    {
        $loader = new Zk2SPSExtension();
        $config = $this->getFullConfig();
        unset($config['options']['timezone_db']);
        $loader->load(array($config), new ContainerBuilder());
    }

    /**
     * getFullConfig
     *
     * @return array
     */
    protected function getFullConfig()
    {
        $yaml = <<<EOF
options:
    pagination_template: Zk2SPSBundle:Form:pagination.html.twig
    sortable_template: Zk2SPSBundle:Form:sortable.html.twig
    timezone_db: Europe/Helsinki
EOF;
        $parser = new Parser();

        return $parser->parse($yaml);
    }
}
