<?php


namespace Habib\Payment\Traits;


use App\Models\User;
use Habib\Payment\Facades\Cart;
use Habib\Payment\Helpers\CartSystem;
use Habib\Payment\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

trait ControllerPaymentMethods
{
    /**
     * @var
     */
    protected $provider;
    /**
     * @var array
     */
    protected $response = [];
    /**
     * @var string
     */
    protected $currency = 'USD';


    /**
     * @param $transaction
     * @param array $response
     * @return mixed
     */
    abstract public function succeeded($transaction, array $response);

    /**
     * @param array $response
     * @return mixed
     */
    abstract public function canceled(array $response, $closure=null);

    /**
     * @param Transaction $transaction
     * @param array $response
     * @return mixed
     */
    abstract public function failured($transaction, array $response, $closure=null);

    /**
     * @param array $response
     * @return mixed
     */
    abstract public function redirectLink(array $response);


    /**
     * @return Transaction|\Illuminate\Database\Eloquent\Model|void
     * @throws Throwable
     */
    public function createTransaction()
    {
        try {
            return DB::Transaction(function () {
                $user = auth()->user();
                /**
                 * @var $user User
                 */
                $transaction = $user->transactions()->create(
                    $this->transaction(
                        $this->prepareCartToCheckOut->transaction([
                            "token" => (string)$this->response['TOKEN'],
                            "pay_url" => (string)$this->response['paypal_link'],
                        ])
                    )
                );

                $this->prepareCartToCheckOut->setTransactionItems($transaction);
                $user->carts()->forceDelete();
                return $transaction;
            },4);

        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
        alert()->error(__('main.payment'),__('main.canceled'));
        redirect($this->redirectPath())->send();
    }

    public function transaction(array $array):array
    {
        return $array;
    }
    public function checkOut(Request $request): array
    {
        return $this->prepareCheckOut($request);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function prepareCheckOut(Request $request): array
    {
        $user = auth()->user();
        /**
         * @var $user User
         */
        $this->prepareCartToCheckOut = $user->prepareCartToCheckOut();
        $this->prepareCartToCheckOut->setCancelUrl(route('payment.cancel'));
        $this->prepareCartToCheckOut->setReturnUrl(route('payment.success'));

        return $this->prepareCartToCheckOut->getCheckoutOrder();
    }
    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }

    /**
     * @return Transaction|\Illuminate\Database\Eloquent\Model|void
     * @throws Throwable
     */
    public function paymentOnDelivery()
    {
        $this->response = [
            "TOKEN"=>uniqid('TOKEN',false),
            "paypal_link"=>null,
        ];
        return DB::Transaction(function () {
            $user = auth()->user();
            /**
             * @var $user User
             */
            $transaction = $user->transactions()->create(
                $this->transaction([
                    "token" => (string)$this->response['TOKEN'],
                    "pay_url" => (string)$this->response['paypal_link'],
                    "method"=>Transaction::PAYMENT['CASH_ON_DELIVERY'],
                    "total" => \Cart::getTotalAfterCoupon(),
                    "shipping_discount" => \Cart::getShippingDiscount(),
                    "tax" => \Cart::getTax(),
                    "shipping" => \Cart::getShipping(),
                ])
            );
            $user->prepareCartToCheckOut()->setTransactionItems($transaction);

            $user->carts()->forceDelete();
            return $transaction;
        });
    }
}
