<?php


namespace Habib\Payment\Http\Resources;


class CartResource
{
    private $array;
    private static $staticArray;

    /**
     * CartResource constructor.
     * @param $array
     */
    public function __construct($array)
    {
        $this->array = $array;
        static::$staticArray=$array;
    }

    /**
     * @return mixed
     */
    public function getArray():array
    {
        return $this->array;
    }

    /**
     * @param array $array
     * @return $this
     */
    public function setArray(array $array): self
    {
        $this->array = $array;
        return $this;
    }

    public function MapArray():array
    {
        return [
                'qty' => $this->array->qty >= 1 ? $this->array->qty :1 ,
                'name' => optional($this->array->cartable)->name ?? optional($this->array->cartable)->title,
                'desc' => optional($this->array->cartable)->id ?? "",
                'price' => optional($this->array->cartable)->price,
                'class' => optional($this->array->cartable)->getMorphClass(),
            ];
    }

    public static function toArray($array=null):array
    {
        return (new static($array ?? static::$staticArray ))->MapArray();
    }

}
