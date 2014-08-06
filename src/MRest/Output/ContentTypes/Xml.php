<?php

namespace MRest\Output\ContentTypes;

class Xml implements \MRest\Output\IOutput {

    public static function getHeader() {
        return 'application/xml';
    }

    public static function parse($data) {
        $xml = new \SimpleXMLElement('<root />');
        $xml = self::_recursiveParsing($xml, $data);
        
        return $xml->asXML();
    }

    private static function _recursiveParsing(\SimpleXMLElement $xml, $data) {
        if (!is_array($data)) {
            throw new \Exception('Invalid array to be parsed into XML');
        }
        foreach ($data as $itemKey => $itemValue) {
            $itemKey = is_int($itemKey) ? 'item' : $itemKey;
            if (is_array($itemValue)) {
                self::_recursiveParsing($xml->addChild($itemKey), $itemValue);
            } else {
                $xml->addChild($itemKey, (string) $itemValue);
            }
        }
        return $xml;
    }

}
