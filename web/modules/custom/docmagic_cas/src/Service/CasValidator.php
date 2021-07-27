<?php

namespace Drupal\docmagic_cas\Service;

use Drupal\cas\Service\CasValidator as BaseCasValidator;

class CasValidator extends BaseCasValidator
{
    const PERMISSIONS_FROM_GRANTED_AUTHORITY = 'permissions_from_granted_authority';
    const ROLE_COMPLIANCE = 'ROLE_COMPLIANCE';
    const ROLE_COMPLIANCE_BASIC = 'ROLE_COMPLIANCE_BASIC';
    const ROLE_EMPLOYEE = 'ROLE_EMPLOYEE';
    const ROLE_TRIAL = 'ROLE_TRIAL';

    /**
     * {@inheritdoc}
     */
    public function validateTicket($ticket, array $service_params = array())
    {
        $helper = $this->casHelper;
        if ($helper instanceof CasHelper) {
            $helper->captureLogs(true);
        }

        /** @var \Drupal\cas\CasPropertyBag $propertyBag */
        $propertyBag = parent::validateTicket($ticket, $service_params);

        if ($helper instanceof CasHelper) {
            $helper->captureLogs(false);
            $logs = $helper->getLogs() ?: array();

            $responseData = null;

            foreach ($logs as $level => $messages) {
                foreach ($messages as $message => $context) {
                    if (isset($context['%data'])) {
                        $responseData = $context['%data'];
                    }
                }
            }

            if ($responseData) {
                $this->addPermissionsFromResponse($responseData, $propertyBag);
            }
        }

        return $propertyBag;
    }

    private function addPermissionsFromResponse($responseData, $propertyBag)
    {
        $permissions = $this->getGrantedAuthorityPermissionsFromResponse($responseData);

        if ($permissions) {
            /** @var \Drupal\cas\CasPropertyBag $propertyBag */
            $attributes = $propertyBag->getAttributes();
            $attributes[static::PERMISSIONS_FROM_GRANTED_AUTHORITY] = $permissions;
            $propertyBag->setAttributes($attributes);
        }
    }

    /**
     * Subscription page: /webservices/subscription/setup.htm
     *
     * Compliance permissions in a nutshell:
     *
     * ROLE_COMPLIANCE -> Compliance subscribers.
     * If the customer is in trial mode, then the attribute trial = "true" is added.
     * In Drupal, look for active = "true" and locked = "false" to ensure access should be granted
     */
    private function getGrantedAuthorityPermissionsFromResponse($responseData)
    {
        $permissionArray = array();

        if ($responseData
            && function_exists('simplexml_load_string')
        ) {
            $casXml = $responseData;

            $_SESSION[static::ROLE_TRIAL] = false;
            $_SESSION[static::ROLE_COMPLIANCE] = false;
            $_SESSION[static::ROLE_COMPLIANCE_BASIC] = false;
            $_SESSION[static::ROLE_EMPLOYEE] = false;
            $_SESSION['DSI_EMPLOYEE'] = false;

            $casXml = str_replace('cas:', '', $casXml);
            $casXml = str_replace(" xmlns:cas='http://www.yale.edu/tp/cas' ", '', $casXml);
            $dsidetails = (simplexml_load_string($casXml));
            $username =  $dsidetails->authenticationSuccess->user;
            $accountId = $dsidetails->authenticationSuccess->AccountId;
            $grantedAuthority = ($dsidetails->authenticationSuccess->GrantedAuthority);

            foreach ($grantedAuthority as $granted) {
                if ($granted->Role == static::ROLE_COMPLIANCE) {
                    //The XML will come in as "string" so can't treat it as boolean.
                    if ($this->findAttribute($granted, 'active') == 'true'
                        && $this->findAttribute($granted, 'locked') == 'false'
                    ) {
                        if ($this->findAttribute($granted, 'trial') == 'true') {
                            $_SESSION[static::ROLE_TRIAL] = true;
                            array_push($permissionArray, static::ROLE_TRIAL);
                        } else {
                            $_SESSION[static::ROLE_COMPLIANCE] = true;
                            array_push($permissionArray, static::ROLE_COMPLIANCE);
                        }
                    }
                }

                if ($granted->Role == static::ROLE_COMPLIANCE_BASIC) {
                    if ($this->findAttribute($granted, 'active') == 'true') {
                        $_SESSION[static::ROLE_COMPLIANCE_BASIC] = true;
                        array_push($permissionArray, static::ROLE_COMPLIANCE_BASIC);
                    }
                }

                if ($granted->Role == static::ROLE_EMPLOYEE) {
                    $dsiEmployee = $this->findAttribute($granted, 'active');
                    if (isset($dsiEmployee) && $dsiEmployee) {
                        $_SESSION[static::ROLE_EMPLOYEE] = true;
                        $_SESSION['DSI_EMPLOYEE'] = true;
                        array_push($permissionArray, static::ROLE_EMPLOYEE);
                    }
                }
            }
        }

        return $permissionArray;
    }

    private function findAttribute($object, $attribute)
    {
        $return = null;

        /** @var \SimpleXMLElement $object */
        foreach ($object->attributes() as $a => $b) {
            if ($a == $attribute) {
                $return = $b;
            }
        }

        if ($return) {
            return $return;
        }

        return false;
    }
}
