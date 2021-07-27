<?php

namespace Drupal\docmagic_compliance_newsletter\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\Entity\Node;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\CssInliner\CssInlinerExtension;
use Twig\Environment;
use Twig\Inky\InkyExtension;
use Twig\Loader\FilesystemLoader;

class ComplianceNewsletter
{
    private $requestStack;
    private $client;
    private $currentUser;
    private $entityTypeManager;

    public function __construct(RequestStack $requestStack, ClientInterface $client, AccountProxyInterface $currentUser, EntityTypeManagerInterface $entityTypeManager)
    {
        $this->requestStack = $requestStack;
        $this->client = $client;
        $this->currentUser = $currentUser;
        $this->entityTypeManager = $entityTypeManager;
    }

    public function generateComplianceNewsletter($year = null, $month = null, $regenerate = false)
    {
        if (!$regenerate && !$this->canGenerateComplianceNewsletter($year, $month)) {
            return;
        }

        $newsletterId = $this->getComplianceNewsletterId();
        $currentUserId = $this->getCurrentUserId();
        $issueNode = $this->createNewsletterIssue($newsletterId, $currentUserId, $year, $month);

        return $issueNode;
    }

    public function canGenerateComplianceNewsletter($year = null, $month = null)
    {
        if ($year && $month) {
            $newsletterId = $this->getComplianceNewsletterId();
            $issues = $this->findNewsletterIssues($newsletterId, $year, $month);

            if (count($issues) <= 0) {
                return true;
            }
        }

        return false;
    }

    public function getMonths()
    {
        return array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        );
    }

    public function getYears()
    {
        $now = new \DateTime();

        $years = range(2018, $now->format('Y'));

        return array_reverse($years);
    }

    private function getComplianceNewsletterId()
    {
        $newsletterId = null;
        $newsletterList = simplenews_newsletter_list();

        foreach ($newsletterList as $id => $name) {
            if (false !== stripos($name, 'Compliance')
                && false !== stripos($name, 'Newsletter')
            ) {
                $newsletterId = $id;
            }
        }

        return $newsletterId;
    }

    private function getCurrentUserId()
    {
        if ($this->currentUser) {
            return $this->currentUser->id();
        }

        return null;
    }

    private function findNewsletterIssues($newsletterId, $year, $month)
    {
        $monthYear = new \DateTime($month.' 1, '.$year);

        $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'simplenews_issue',
            'simplenews_issue' => $newsletterId,
            'field_month_year' => $monthYear->format('Y-m-d').'T00:00:00',
        ));

        return $nodes;
    }

    private function createNewsletterIssue($newsletterId, $currentUserId = null, $year = null, $month = null)
    {
        if (!$newsletterId) {
            return;
        }

        $content = $this->findComplianceDataForDate($year, $month);

        $emailContent = $this->generateEmailContent($content);
        $body = implode("\n\n", $emailContent['body']);

        $existing = $this->findNewsletterIssues($newsletterId, $content['year'], $content['month']);
        if (count($existing)) {
            $issueNode = reset($existing);
        } else {
            $issueNode = Node::create(array(
                'type' => 'simplenews_issue',
                'uid' => $currentUserId ?: 1,
                'status' => 1,
            ));
        }
        $issueNode->set('title', sprintf('Compliance Newsletter - %s %s', $content['month'], $content['year']));
        $issueNode->set('body', array(
            'format' => 'full_html',
            'summary' => '',
            'value' => $body,
        ));
        $issueNode->set('simplenews_issue', array(
            'target_id' => $newsletterId,
            //'handler' => 'simplenews_compliance_newsletter',
            //'handler_settings' => array(),
        ));
        $monthYear = new \DateTime($content['month'].' 1, '.$content['year']);
        $issueNode->set('field_month_year', $monthYear->format('Y-m-d').'T00:00:00');
        $issueNode->save();

        return $issueNode;
    }

    private function findComplianceDataForDate($year = null, $month = null)
    {
        $months = $this->getMonths();
        $years = $this->getYears();
        $now = new \DateTime();

        if (!in_array($year, $years)) {
            $year = $now->format('Y');
        }
        if (!in_array($month, $months)) {
            $month = $now->format('F');
        }

        $termWizardMonthYear = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'wizard',
            'name' => $month.' '.$year,
        ));
        $termComplianceFeaturedArticle = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'compliance_feed',
            'name' => 'Compliance Featured Article',
        ));
        $termLegalAndRegulatoryUpdates = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'compliance_feed',
            'name' => 'Legal and Regulatory Updates',
        ));
        $termComplianceTermOfTheMonth = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'compliance_feed',
            'name' => 'Compliance Term of the Month',
        ));
        $termFormUpdates = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'compliance_feed',
            'name' => 'Form Updates',
        ));
        $termIndustryAnnouncement = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(array(
            'vid' => 'compliance_feed',
            'name' => 'Industry Announcement',
        ));

        $mnthYr = $termWizardMonthYear;
        if (is_array($mnthYr)) {
            $mnthYr = reset($mnthYr);
        }
        $fa = $termComplianceFeaturedArticle;
        if (is_array($fa)) {
            $fa = reset($fa);
        }
        $lrup = $termLegalAndRegulatoryUpdates;
        if (is_array($lrup)) {
            $lrup = reset($lrup);
        }
        $totm = $termComplianceTermOfTheMonth;
        if (is_array($totm)) {
            $totm = reset($totm);
        }
        $fup = $termFormUpdates;
        if (is_array($fup)) {
            $fup = reset($fup);
        }
        $ina = $termIndustryAnnouncement;
        if (is_array($ina)) {
            $ina = reset($ina);
        }

        $featuredArticleRows = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'wizard',
            'field_compliance_feed' => $fa->id(),
            'field_wizard' => $mnthYr->id(),
        ));
        $legalAndRegulatoryUpdateRows = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'wizard',
            'field_compliance_feed' => $lrup->id(),
            'field_wizard' => $mnthYr->id(),
        ));
        $termOfTheMonthRows = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'wizard',
            'field_compliance_feed' => $totm->id(),
            'field_wizard' => $mnthYr->id(),
        ));
        $formUpdateRows = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'wizard',
            'field_compliance_feed' => $fup->id(),
            'field_wizard' => $mnthYr->id(),
        ));
        $industryAnnouncementRows = $this->entityTypeManager->getStorage('node')->loadByProperties(array(
            'type' => 'wizard',
            'field_compliance_feed' => $ina->id(),
            'field_wizard' => $mnthYr->id(),
        ));

        $databaseData['taxonomy_term']['wizard']['month_year'] = $termWizardMonthYear;
        $databaseData['taxonomy_term']['compliance_feed']['featured_article'] = $termComplianceFeaturedArticle;
        $databaseData['taxonomy_term']['compliance_feed']['legal_and_regulatory_updates'] = $termLegalAndRegulatoryUpdates;
        $databaseData['taxonomy_term']['compliance_feed']['term_of_the_month'] = $termComplianceTermOfTheMonth;
        $databaseData['taxonomy_term']['compliance_feed']['form_updates'] = $termFormUpdates;
        $databaseData['taxonomy_term']['compliance_feed']['industry_announcement'] = $termIndustryAnnouncement;
        $databaseData['node']['wizard']['featured_article'] = $featuredArticleRows;
        $databaseData['node']['wizard']['legal_and_regulatory_updates'] = $legalAndRegulatoryUpdateRows;
        $databaseData['node']['wizard']['term_of_the_month'] = $termOfTheMonthRows;
        $databaseData['node']['wizard']['form_updates'] = $formUpdateRows;
        $databaseData['node']['wizard']['industry_announcement'] = $industryAnnouncementRows;


        $url = sprintf('/compliance/wizard/%s/%s-%s/feed', $year, strtolower($month), $year);

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $url = $request->getUriForPath($url);
        }

        $data = array(
            'url' => $url,
            'year' => $year,
            'month' => $month,
            'body' => '',
            'database' => $databaseData,
        );

//        try {
//            $response = $this->client->request('GET', $url);
//            $output = (string) $response->getBody();
//        } catch (\Exception $e) {
//            // do nothing
            $output = '';
//        }

        $data['body'] = $output;

        /* @var \Drupal\serialization\Encoder\XmlEncoder $xmlEncoder */
        if (\Drupal::hasService('serializer.encoder.xml')) {
            $xmlEncoder = \Drupal::service('serializer.encoder.xml');
        } else {
            $xmlEncoder = new \Drupal\serialization\Encoder\XmlEncoder();
        }
        if (method_exists($xmlEncoder, 'setSerializer')) {
            $serializer = \Drupal::service('serializer');
            $xmlEncoder->setSerializer($serializer);
        }
        try {
            $xmlData = $xmlEncoder->decode(preg_replace('`.*<?xml `s', '<?xml ', $output), 'xml');
        } catch (\Exception $e) {
            $xmlData = array();
        }

        $data['decoded'] = $xmlData;

        return $data;
    }

    private function generateEmailContent($content = array())
    {
        $loader = new FilesystemLoader(__DIR__.'/../../templates');
        $loader->addPath(__DIR__.'/../../assets', 'assets');

        $twig = new Environment($loader);
        $twig->addExtension(new CssInlinerExtension());
        $twig->addExtension(new InkyExtension());

        $items = isset($content['decoded']['channel']['item']) ? $content['decoded']['channel']['item'] : array();
        foreach ($items as $index => $item) {
            if (isset($item['description'])) {
                $items[$index]['description'] = $this->cleanDescription($item['description']);
            }
        }

        $larupItems = array();
        foreach ($content['database']['node']['wizard']['legal_and_regulatory_updates'] as $node) {
            /** @var Node $node */
            $larupItems[] = array(
                'title' => $node->label(),
                'description' => $this->cleanDescription(($node->body->value).('<a href="'.$node->toUrl()->setAbsolute(true)->toString().'">read more</a>')),
                'link' => $node->toUrl()->setAbsolute(true)->toString(),
                'url' => $node->toUrl()->setAbsolute(true)->toString(),
                'is_subscription_required' => $this->isSubscriptionRequired($node),
            );
        }

        $fupItems = array();
        $newRevisedDocument = null;
        foreach ($content['database']['node']['wizard']['form_updates'] as $node) {
            /** @var Node $node */
            $fupItem = array(
                'title' => $node->label(),
                'description' => $this->cleanDescription(($node->body->value).('<a href="'.$node->toUrl()->setAbsolute(true)->toString().'">read more</a>')),
                'link' => $node->toUrl()->setAbsolute(true)->toString(),
                'url' => $node->toUrl()->setAbsolute(true)->toString(),
                'is_subscription_required' => $this->isSubscriptionRequired($node),
            );
            if (false !== stripos($fupItem['title'], 'New')
                && false !== stripos($fupItem['title'], 'Revised')
                && false !== stripos($fupItem['title'], 'Document')
            ) {
                $newRevisedDocument = $fupItem;
            } else {
                $fupItems[] = $fupItem;
            }
        }
        // the last entry is always the New Revised Documents
        if ($newRevisedDocument) {
            $fupItems[] = $newRevisedDocument;
        }

        /** @var Node $featuredArticle */
        $featuredArticle = reset($content['database']['node']['wizard']['featured_article']);
        /** @var Node $termOfTheMonth */
        $termOfTheMonth = reset($content['database']['node']['wizard']['term_of_the_month']);
        /** @var Node $industryAnnouncement */
        $industryAnnouncement = reset($content['database']['node']['wizard']['industry_announcement']);

        $content['legal_and_regulatory_updates'] = $larupItems;
        $content['form_updates'] = $fupItems;
        if (is_object($featuredArticle)) {
            $content['article_of_the_month'] = array(
                'title' => $featuredArticle->label(),
                'description' => $this->cleanDescription($featuredArticle->body->value),
                'url' => $featuredArticle->toUrl()->setAbsolute(true)->toString(),
            );
        }
        if (is_object($termOfTheMonth)) {
            $content['compliance_term_of_the_month'] = array(
                'title' => $termOfTheMonth->label(),
                'description' => $this->cleanDescription($termOfTheMonth->body->value, false), // no length limit
                'url' => $termOfTheMonth->toUrl()->setAbsolute(true)->toString(),
            );
        }
        if (is_object($industryAnnouncement)) {
            $content['upcoming_event'] = array(
                'title' => $industryAnnouncement->label(),
                'description' => $this->cleanDescription($industryAnnouncement->body->value),
                'url' => $industryAnnouncement->toUrl()->setAbsolute(true)->toString(),
            );
        }

        $template = 'email/compliance_newsletter.html.twig';
        $vars = array(
            'content' => $content,
            'year' => $content['year'],
            'month' => $content['month'],
            'base_url' => $this->requestStack->getCurrentRequest() ? $this->requestStack->getCurrentRequest()->getUriForPath('') : 'https://www.docmagic.com',
        );

        $content['body'] = array();
        try {
            $content['body'][] = $twig->render($template, $vars);
        } catch (\Exception $e) {
            // do nothing
        }

        return $content;
    }

    private function cleanDescription($description, $limitLength = true)
    {
        $clean = $description;

        // remove HTML comments and extraneous whitespace
        $clean = preg_replace('`<!--.*-->`Uis', '', $clean);
        $clean = preg_replace("`\n{2,}`", "\n", $clean);
        $clean = trim($clean);

        // remove and store "read more" links to allow the description text to be shortened

        // find script tags
        // http://www.webmasterworld.com/forum88/10822.htm
        // The pattern matches anything that starts with '<script' followed by zero or more of anything that is not
        // a '>', followed by a '>', followed by anything (and that part gets captured), followed by a closing
        // '<script>' tag. The 'Uis' modifiers mean to make it Ungreedy, case-insensitive, and to match newlines
        // in the dot metacharacter.
        //$pattern = "/<script[^>]*>(.*)<\/script>/Uis";
        $pattern = "/<a[^>]*>read more<\/a>/Uis";

        $readMoreLinks = array();
        $matches = array();
        preg_match_all($pattern, $clean, $matches);
        if (isset($matches[0])) {
            foreach ($matches[0] as $link) {
                $readMoreLinks[] = $link;
            }
        }
        $clean = str_replace($readMoreLinks, '', $clean);

        if ($limitLength && function_exists('text_summary')) {
            $clean = text_summary($clean, 'full_html', 190); // 3 lines max
        }

        $pos = strripos($clean, '</p>');

        // add back link tags before closing tag
        $clean = substr($clean, 0, $pos).' '.str_replace('read more', 'Read More', implode(' ', $readMoreLinks)).substr($clean, $pos);

        return $clean;
    }

    private function isSubscriptionRequired($node)
    {
        if (function_exists('_premium_node')) {
            $required = _premium_node($node);
        } else {
            $required = $node->premium;
        }

        return $required ? true : false;
    }
}
