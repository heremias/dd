<?php

namespace Drupal\docmagic_migrate_content\DefaultContent;

use Drupal\default_content\ImporterInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Importer implements ImporterInterface
{
    private $decorated;

    public function __construct(ImporterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * {@inheritdoc}
     */
    public function importContent($module)
    {
        $this->replaceUserReferences();

        return $this->decorated->importContent($module);
    }

    private function replaceUserReferences()
    {
        $contentDir = __DIR__.'/../../content';
        $originalContentDir = __DIR__.'/../../original_content';

        $filesystem = new Filesystem();

        if ($filesystem->exists($originalContentDir)) {
            $filesystem->mirror($originalContentDir, $contentDir, null, array('override' => true));
        } else {
            $filesystem->mirror($contentDir, $originalContentDir);
        }

        if (!$filesystem->exists($contentDir.'/user')) {
            return;
        }

        $replaceUsers = array();
        $oldUsers = $this->getReferences($contentDir.'/user', 'user');
        foreach ($oldUsers as $oldUserUuid => $oldUserId) {
            $user = \Drupal::entityTypeManager()->getStorage('user')->load($oldUserId);
            if ($user && $oldUserUuid != $user->uuid()) {
                $replaceUsers[$oldUserUuid] = $user->uuid();
            }
        }

        if (!count($replaceUsers)) {
            return;
        }

        $serializer = $this->getSerializer();

        $finder = Finder::create()->files()->name('*.json')->in($contentDir);
        foreach ($finder as $file) {
            /** @var \SplFileInfo $file */
            $filename = $file->getPathname();
            $contents = $original = file_get_contents($filename);
            foreach ($replaceUsers as $oldUuid => $newUuid) {
                $contents = str_replace($oldUuid, $newUuid, $contents);
            }
            if (false !== strpos($contents, 'id"')) {
                $decoded = $serializer->decode($contents, 'hal_json');
                if (isset($decoded['bundle'][0]['value'])
                    && 'menu_link_content' == $decoded['bundle'][0]['value']
                ) {
                    if (isset($decoded['parent'])
                        && !isset($decoded['_embedded'])
                    ) {
                        // include an embedded reference to the parent menu item so that it is sorted correctly on import
                        $parentUuid = str_replace('menu_link_content:', '', $decoded['parent'][0]['value']);
                        //$type = 'http://drupal.org/rest/type/menu_link_content/menu_link_content/parent';
                        // use a non-existent type to prevent any of these references from being stored
                        $type = 'menu_link_content/parent';
                        $decoded['_embedded'][$type][0]['uuid'][0]['value'] = $parentUuid;
                    }
                    if (isset($decoded['link'])
                        && isset($decoded['link'][0])
                        && isset($decoded['link'][0]['uri'])
                        && false !== strpos($decoded['link'][0]['uri'], 'entity:node/')
                    ) {
                        // update menu links since the node ID values will not match after import
                        $nodeId = str_replace('entity:node/', '', $decoded['link'][0]['uri']);
                        $nodeFinder = Finder::create()->files()->name('*.json')->contains($nodeId)->in($originalContentDir.'/node');
                        foreach ($nodeFinder as $nodeFile) {
                            $decodedNode = $serializer->decode(file_get_contents($nodeFile->getPathname()), 'hal_json');
                            if (isset($decodedNode['nid'])
                                && isset($decodedNode['nid'][0])
                                && isset($decodedNode['nid'][0]['value'])
                                && $nodeId == $decodedNode['nid'][0]['value']
                            ) {
                                if (isset($decodedNode['path'])
                                    && isset($decodedNode['path'][0])
                                    && isset($decodedNode['path'][0]['alias'])
                                ) {
                                    $decoded['link'][0]['uri'] = 'internal:'.$decodedNode['path'][0]['alias'];
                                }
                            }
                        }
                    }
                }
                unset($decoded['id']);
                unset($decoded['fid']);
                unset($decoded['nid']);
                unset($decoded['tid']);
                unset($decoded['uid']);
                unset($decoded['revision_id']);
                if (isset($decoded['vid'])
                    && isset($decoded['vid'][0])
                    && isset($decoded['vid'][0]['value'])
                    && is_numeric($decoded['vid'][0]['value'])
                ) {
                    unset($decoded['vid']);
                }
                $contents = $serializer->encode($decoded, 'hal_json', ['json_encode_options' => JSON_PRETTY_PRINT]);
            }
            if ($contents != $original) {
                $filesystem->dumpFile($filename, $contents);
            }
        }

        $filesystem->remove($contentDir.'/user');
        // remove unnecessary content as well
        $filesystem->remove($contentDir.'/shortcut');
    }

    private function getReferences($dir, $type = null)
    {
        $scanner = $this->getScanner();
        $serializer = $this->getSerializer();

        $references = array();
        $files = $scanner->scan($dir);
        foreach ($files as $file) {
            $contents = $this->parseFile($file);
            $decoded = $serializer->decode($contents, 'hal_json');
            $uuid = $decoded['uuid'][0]['value'];
            if ('user' == $type) {
                $value = $decoded['uid'][0]['value'];
            } else {
                $value = array('file' => $file, 'decoded' => $decoded);
            }
            $references[$uuid] = $value;
        }

        return $references;
    }

    /**
     * @return \Drupal\default_content\Scanner
     */
    private function getScanner()
    {
        $propScanner = new \ReflectionProperty($this->decorated, 'scanner');
        $propScanner->setAccessible(true);

        return $propScanner->getValue($this->decorated);
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    private function getSerializer()
    {
        $propSerializer = new \ReflectionProperty($this->decorated, 'serializer');
        $propSerializer->setAccessible(true);

        return $propSerializer->getValue($this->decorated);
    }

    private function parseFile($file)
    {
        $methodParseFile = new \ReflectionMethod($this->decorated, 'parseFile');
        $methodParseFile->setAccessible(true);

        return $methodParseFile->invoke($this->decorated, $file);
    }
}
