<?php

namespace Riconect\MailerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 *
 */
class RiconectMailerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
        
        $container->setParameter('riconect_mailer.database_type', $config['database_type']);
        $container->setParameter('riconect_mailer.keep_sent_emails', $config['keep_sent_emails']);
        $container->setParameter('riconect_mailer.message_class', $config['message_class']);
        
        $manager = $container->getDefinition('riconect_mailer.spool');
        $manager->addArgument($config);
        
    }
}
