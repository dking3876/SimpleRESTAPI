<?php
namespace SimpleRESTAPI;

class AutoMapper{
    private $mappedProperties;
    private $object;
    private $omitProperty;
    function createNewObject($obj){
        $this->object = $obj;
    }
    function mapProperty($originProperty, $destinationProperty){
        $this->mappedProperties[ $originProperty] = $destinationProperty;
    }
    function omitProperty($originProperty){
        $this->omitProperty[$originProperty] = true;
    }
    function convertObjects(&$originObject){
        $obj = new $this->object;

        foreach($this->mappedProperties as $map => $dest){
            $_map = explode(":", $map);
            if(!isset($originObject[$_map[0]])){
                continue;
            }
            $v = array_reduce($_map, function($v1, $v2){
                return isset($v1[$v2])? $v1[$v2] : $v1;
            }, $originObject);
            $obj->{$dest} = $v;
            unset($originObject[$_map[0]]);
        }

        foreach($originObject as $prop => $value){
           
            if(\property_exists($obj, $prop) ){

                if(isset($this->omitProperty[$prop]) && $this->omitProperty[$prop]){
                    continue;
                }

                $obj->{$prop} = $value;
            }
           
        }
        return $obj;
    }
}