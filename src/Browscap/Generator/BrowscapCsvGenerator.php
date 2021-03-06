<?php

namespace Browscap\Generator;

/**
 * Class BrowscapCsvGenerator
 *
 * @package Browscap\Generator
 */
class BrowscapCsvGenerator extends AbstractGenerator
{
    /**
     * Generate and return the formatted browscap data
     *
     * @return string
     */
    public function generate()
    {
        $this->logger->debug('build output for csv file');

        return $this->render(
            $this->collectionData,
            $this->renderVersion(),
            array_keys(array('Parent' => '') + $this->collectionData['DefaultProperties'])
        );
    }

    /**
     * renders all found useragents into a string
     *
     * @param array[] $allDivisions
     * @param string  $output
     * @param array   $allProperties
     *
     * @return string
     */
    private function render(array $allDivisions, $output, array $allProperties)
    {
        $this->logger->debug('rendering CSV header');

        $output .= '"PropertyName","AgentID","MasterParent","LiteMode"';

        foreach ($allProperties as $property) {

            if (!CollectionParser::isOutputProperty($property)) {
                continue;
            }

            if (CollectionParser::isExtraProperty($property)) {
                continue;
            }

            $output .= ',"' . str_replace('"', '""', $property) . '"';
        }

        $output .= PHP_EOL;

        $counter = 1;

        $this->logger->debug('rendering all divisions');
        foreach ($allDivisions as $key => $properties) {
            $this->logger->debug('rendering division "' . $properties['division'] . '" - "' . $key . '"');

            $counter++;

            if (!$this->firstCheckProperty($key, $properties, $allDivisions)) {
                $this->logger->debug('first check failed on key "' . $key . '" -> skipped');

                continue;
            }

            // create output - csv
            $output .= '"' . str_replace('"', '""', $key) . '"'; // PropertyName
            $output .= ',"' . str_replace('"', '""', $counter) . '"'; // AgentID
            $output .= ',"' . str_replace('"', '""', $this->detectMasterParent($key, $properties)) . '"'; // MasterParent

            $output .= ',"'
                . ((!isset($properties['lite']) || !$properties['lite']) ? 'false' : 'true') . '"'; // LiteMode

            foreach ($allProperties as $property) {
                if (!CollectionParser::isOutputProperty($property)) {
                    continue;
                }

                if (CollectionParser::isExtraProperty($property)) {
                    continue;
                }

                $output .= ',"' . str_replace('"', '""', $this->formatValue($property, $properties)) . '"';

                unset($property);
            }

            $output .= PHP_EOL;

            unset($properties);
        }

        return $output;
    }

    /**
     * renders the version information
     *
     * @return string
     */
    private function renderVersion()
    {
        $this->logger->debug('rendering version information');
        $header = '"GJK_Browscap_Version","GJK_Browscap_Version"' . PHP_EOL;

        $versionData = $this->getVersionData();

        if (!isset($versionData['version'])) {
            $versionData['version'] = '0';
        }

        if (!isset($versionData['released'])) {
            $versionData['released'] = '';
        }

        $header .= '"' . str_replace('"', '""', $versionData['version']) . '","'
            . str_replace('"', '""', $versionData['released']) . '"' . PHP_EOL;

        return $header;
    }
}
