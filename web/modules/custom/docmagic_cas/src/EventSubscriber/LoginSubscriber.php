<?php

namespace Drupal\docmagic_cas\EventSubscriber;

use Drupal\cas\Event\CasPreLoginEvent;
use Drupal\cas\Event\CasPreRedirectEvent;
use Drupal\cas\Event\CasPreUserLoadEvent;
use Drupal\cas\Service\CasHelper;
use Drupal\docmagic_cas\Service\CasValidator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoginSubscriber implements EventSubscriberInterface
{
    public function onPreUserLoad(CasPreUserLoadEvent $event)
    {
        $propertyBag = $event->getCasPropertyBag();
        $attributes = $propertyBag->getAttributes();

        $emailAttribute = \Drupal::configFactory()->get('cas.settings')->get('user_accounts.email_attribute') ?: 'mail';

        if ($emailAttribute
            && (!isset($attributes[$emailAttribute])
                || !$attributes[$emailAttribute]
            )
            && false !== strpos($propertyBag->getUsername(), '@')
        ) {
            // the username is also the email address
            $attributes[$emailAttribute] = $propertyBag->getUsername();
            $propertyBag->setAttributes($attributes);
        }
    }

    public function onPreLogin(CasPreLoginEvent $event)
    {
        $account = $event->getAccount();
        $propertyBag = $event->getCasPropertyBag();
        $attributes = $propertyBag->getAttributes();

        if (isset($attributes[CasValidator::PERMISSIONS_FROM_GRANTED_AUTHORITY])
            && is_array($attributes[CasValidator::PERMISSIONS_FROM_GRANTED_AUTHORITY])
        ) {
            $config = \Drupal::configFactory()->get('cas.settings');

            foreach ($attributes[CasValidator::PERMISSIONS_FROM_GRANTED_AUTHORITY] as $permission) {
                $account->addRole($permission);

                if (CasValidator::ROLE_TRIAL == $permission) {
                    $cas_trial_roles = $config->get('user_accounts.cas_trial_roles') ?: array('dsi_trial');
                    foreach ($cas_trial_roles as $role) {
                        $account->addRole($role);
                    }
                } elseif (CasValidator::ROLE_COMPLIANCE == $permission) {
                    $cas_compliance_roles = $config->get('user_accounts.cas_compliance_roles') ?: array('dsi_compliance_premium');
                    foreach ($cas_compliance_roles as $role) {
                        $account->addRole($role);
                    }
                }

                if (CasValidator::ROLE_COMPLIANCE_BASIC == $permission) {
                    $cas_compliance_basic_roles = $config->get('user_accounts.cas_compliance_basic_roles') ?: array('dsi_compliance_basic');
                    foreach ($cas_compliance_basic_roles as $role) {
                        $account->addRole($role);
                    }
                }
            }
        }
    }

    public function onPreRedirect(CasPreRedirectEvent $event)
    {
        $redirectData = $event->getCasRedirectData();

        // Forced Login Excluded Pages
        /** @var \Drupal\Core\Condition\ConditionManager $conditionManager */
        $conditionManager = \Drupal::service('plugin.manager.condition');
        $condition = $conditionManager->createInstance('request_path');
        $condition->setConfiguration(array(
            'pages' => implode("\n", array(
                '/compliance/integrated-disclosures',
                '/compliance/regulatory-announcements/*',
                '/services/*',
                '*/feed',
                '<front>',
            )),
        ));

        // if the request matches one of the excluded pages, prevent the CAS login redirection
        if ($conditionManager->execute($condition)) {
            $redirectData->preventRedirection();
        }
    }

    public static function getSubscribedEvents()
    {
        $events[CasHelper::EVENT_PRE_USER_LOAD][] = array('onPreUserLoad');
        $events[CasHelper::EVENT_PRE_LOGIN][] = array('onPreLogin');
        $events[CasHelper::EVENT_PRE_REDIRECT][] = array('onPreRedirect');
        return $events;
    }
}
