<?php

namespace Riconect\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

 /**
  *
  */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('riconect_mailer');

        $rootNode
            ->children()
                ->scalarNode('database_type')
                    ->defaultValue('mongodb')
                    ->info('Only mongodb is allowed at the moment (odm in future)')
                ->end()
                ->booleanNode('keep_sent_emails')
                    ->defaultFalse()
                    ->info('If true - do not delete sent emails from database')
                ->end()
                ->scalarNode('message_class')
                    ->defaultValue('Riconect\MailerBundle\Document\Message')
                ->end();

        return $treeBuilder;
    }
}
