<?php
namespace Habib\Payment;
/**
 * Interface CartProductPriceInterface
 * @package Habib\Payment
 */
interface CartProductPriceInterface
{
    /**
     * @return float
     */
    public function getPrice():float;

    /**
     * @return string
     */
    public function getImage():string;

    /**
     * @return string
     */
    public function getName():string;

    /**
     * @return string
     */
    public function getUrl():string;

    /**
     * @return string
     */
    public function getQty():int;

}