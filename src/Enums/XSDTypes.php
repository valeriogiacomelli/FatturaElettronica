<?php


namespace FatturaElettronicaPhp\FatturaElettronica\Enums;


use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;

abstract class XSDTypes
{
    protected static $types = [];

    public static function types(string $type): array
    {
        if (!self::$types){
            self::$types = self::extractValuesFromXSD();
        }

        return self::$types[$type] ?? [];
    }

    protected static function extractValuesFromXSD(): array
    {
        $xsd             = file_get_contents(dirname(__DIR__) . '/Validator/xsd/Schema_del_file_xml_FatturaPA_versione_1.2.1.xsd');
        $xmldsigFilename = dirname(__DIR__) . '/Validator/xsd/core.xsd';
        $xsd             = preg_replace('/(\bschemaLocation=")[^"]+"/', sprintf('\1%s"', $xmldsigFilename), $xsd);

        $doc = new DOMDocument();
        $doc->loadXML(mb_convert_encoding($xsd, 'utf-8', mb_detect_encoding($xsd)));

        $xpath = '/xs:schema/xs:simpleType';
        $domPath = new DOMXPath($doc);
        $nodes = $domPath->evaluate($xpath);

        $types = [];
        /** @var DOMElement $node */
        foreach ($nodes as $node) {
            $xpath = 'xs:restriction/xs:enumeration';

            /** @var DOMNodeList $nodes */
            $nodes = $domPath->evaluate($xpath, $node);
            if ($nodes->count() <= 0) {
                continue;
            }

            $typeName = $node->getAttribute('name');
            $types[$typeName] = [];
            foreach ($nodes as $values) {
                $value = $values->getAttribute('value');
                $desc = $value;
                $xpath = 'xs:annotation/xs:documentation';
                $descriptions = $domPath->evaluate($xpath, $values);
                /** @var DOMElement $description */
                foreach ($descriptions as $description) {
                    $desc = $description->textContent;
                }
                $types[$typeName][$value] = $desc;
            }
        }

        return $types;
    }
}
