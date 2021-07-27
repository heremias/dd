<?php

namespace Drupal\docmagic_cas\Service;

use Drupal\cas\Service\CasHelper as BaseCasHelper;

class CasHelper extends BaseCasHelper
{
    private $logs = [];
    private $captureLogs = false;

    /**
     * {@inheritdoc}
     */
    public function getServerBaseUrl()
    {
        $protocol = $this->settings->get('server.protocol');
        $url = $protocol . '://' . $this->settings->get('server.hostname');

        // determine host based on the current request
        if (\Drupal::hasRequest()) {
            $host = \Drupal::request()->getHost();
            if ($host && 'localhost' != $host) {
                $url = $protocol . '://' . $host;
            }
        }

        // Only append port if it's non standard.
        $port = $this->settings->get('server.port');
        if (($protocol == 'http' && $port != 80) || ($protocol == 'https' && $port != 443)) {
            $url .= ':' . $this->settings->get('server.port');
        }

        $url .= $this->settings->get('server.path');
        $url = rtrim($url, '/') . '/';

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if ($this->captureLogs) {
            $this->logs[$level][$message] = $context;
        }

        parent::log($level, $message, $context);
    }

    public function getLogs()
    {
        $logs = $this->logs;
        $this->logs = [];

        return $logs;
    }

    public function captureLogs($capture = true)
    {
        $this->captureLogs = (bool) $capture;

        return $this;
    }
}
