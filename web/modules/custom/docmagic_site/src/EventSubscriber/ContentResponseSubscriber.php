<?php

namespace Drupal\docmagic_site\EventSubscriber;

use Drupal\Core\EventSubscriber\MainContentViewSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContentResponseSubscriber implements EventSubscriberInterface
{
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();

        if (is_array($result) && ($request->query->has(MainContentViewSubscriber::WRAPPER_FORMAT) || $request->getRequestFormat() == 'html')) {
            // update search results page content
            if (isset($result['search_form'])
                && isset($result['search_results_title'])
                && isset($result['search_results'])
            ) {
                global $pager_total_items;
                $totalItemsCount = isset($pager_total_items[0]) ? $pager_total_items[0] : 0;

                $newResult = array();
                foreach ($result as $key => $value) {
                    $newResult[$key] = $value;
                    if ('search_results_title' == $key && $totalItemsCount > 0) {
                        $newResult['search_results_count'] = array(
                            '#markup' => sprintf(
                                '<span class="search-results-count">%s</span>',
                                \Drupal::translation()->formatPlural($totalItemsCount, '1 result', '@count results')
                            ),
                        );
                    }
                }

                $event->setControllerResult($newResult);
            }
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $content = $response->getContent();

        if (false !== strpos($request->getPathInfo(), '/support/clean/premium')) {
            // remove header and footer from page that displays in an iframe
            $newContent = $content;
            $newContent = $this->replaceHeader($newContent);
            $newContent = $this->replaceFooter($newContent);
            // there seems to be CSS for #premiumOptions in the content which affects the layout
            $newContent = str_replace('width: 615px;', '/*width: 615px;*//* rpl */', $newContent);

            try {
                $response->setContent($newContent);
                $event->setResponse($response);
            } catch (\Exception $e) {
                // do nothing
            }
        }
    }

    private function replaceHeader($content)
    {
        // find script tags
        // http://www.webmasterworld.com/forum88/10822.htm
        // The pattern matches anything that starts with '<script' followed by zero or more of anything that is not
        // a '>', followed by a '>', followed by anything (and that part gets captured), followed by a closing
        // '<script>' tag. The 'Uis' modifiers mean to make it Ungreedy, case-insensitive, and to match newlines
        // in the dot metacharacter.
        //$pattern = "/<script[^>]*>(.*)<\/script>/Uis";

        // <header id="header" class="header-v1">
        $pattern = "/<header id=\"header\" class=\"header[^>]*>(.*)<\/header>/Uis";

        $matches = array();
        preg_match_all($pattern, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $tags) {
                $content = str_replace($tags, '<!-- header -->', $content);
            }
        }

        return $content;
    }

    private function replaceFooter($content)
    {
        // find script tags
        // http://www.webmasterworld.com/forum88/10822.htm
        // The pattern matches anything that starts with '<script' followed by zero or more of anything that is not
        // a '>', followed by a '>', followed by anything (and that part gets captured), followed by a closing
        // '<script>' tag. The 'Uis' modifiers mean to make it Ungreedy, case-insensitive, and to match newlines
        // in the dot metacharacter.
        //$pattern = "/<script[^>]*>(.*)<\/script>/Uis";

        // <footer id="footer" class="footer">
        $pattern = "/<footer id=\"footer\" class=\"footer[^>]*>(.*)<\/footer>/Uis";

        $matches = array();
        preg_match_all($pattern, $content, $matches);

        if (isset($matches[0])) {
            foreach ($matches[0] as $tags) {
                $content = str_replace($tags, '<!-- footer -->', $content);
            }
        }

        return $content;
    }

    public static function getSubscribedEvents() {
        $events[KernelEvents::VIEW][] = array('onKernelView', 1);
        $events[KernelEvents::RESPONSE][] = array('onKernelResponse', -10);
        return $events;
    }
}
