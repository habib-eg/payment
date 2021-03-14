<?php

namespace Habib\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Exception;
use Habib\Payment\Helpers\PaymentCheckout;
use Habib\Payment\Models\Transaction;
use Habib\Payment\Traits\ControllerPaymentMethods;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Srmklive\PayPal\Services\ExpressCheckout;
use Throwable;

/**
 * Class PayPalController
 * @package Habib\Payment\Http\Controllers
 * @property-read ControllerPaymentMethods
 */
class PayPalController extends Controller
{
    use ControllerPaymentMethods;
    const SUCCESS=[
        'SUCCESS', 'SUCCESSWITHWARNING','Success'
    ];
    protected $redirectTo =RouteServiceProvider::HOME;
    /**
     * @var ExpressCheckout
     */
    protected $provider;
    /**
     * @var array
     */
    protected $response = [];
    /**
     * @var PaymentCheckout
     */
    protected $prepareCartToCheckOut;

    /**
     * ControllerPaymentMethods constructor.
     */
    public function __construct()
    {
        $this->provider = new ExpressCheckout();
    }

    /**
     * Retrieve IPN Response From PayPal
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function postNotify(Request $request)
    {
        $request->merge(['cmd' => '_notify-validate']);
        $post = $request->all();

        $this->response = (string)$this->provider->verifyIPN($post);

        if ($this->response === 'VERIFIED') {
            return $this->NotifySuccess($this->response);
        }
        return $this->NotifyFail($this->response);
    }

    /**
     * @param array $response
     */
    public function NotifySuccess(array $response)
    {
        \Log::info($response);
        dd($response, ' NotifySuccess ');
    }

    /**
     * @param array $response
     */
    public function NotifyFail(array $response)
    {
        \Log::info($response);
        dd($response, ' NotifyFail ');
    }

    /**
     * @param Request $request
     * @return Application|RedirectResponse|Redirector|mixed
     * @throws Throwable
     */
    public function payment(Request $request)
    {
        $order = $this->checkOut($request);

        $this->response = $this->provider->setExpressCheckout(
            $order
        );

        if (in_array(strtoupper($this->response['ACK']), self::SUCCESS)) {
            $this->createTransaction();
            return $this->redirectLink($this->response);
        }
        alert(__('main.payment'),__('main.canceled'));
        return redirect($this->redirectPath());
    }


    /**
     * @param array $response
     * @return mixed
     */
    public function redirectLink(array $response)
    {
        return redirect($response['paypal_link']);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function success(Request $request)
    {
        $this->response = $this->provider->getExpressCheckoutDetails($request->get('token'));

        $transaction = config('payment.transaction_model',Transaction::class)::firstWhere('token', $this->response['TOKEN']);

        if ( in_array(strtoupper($this->response['ACK']), self::SUCCESS) && $transaction ) {
            return $this->succeeded($transaction, $this->response,);
        }

        return $this->failured($transaction, $this->response);
    }

    /**
     * @param $transaction
     * @param array $response
     * @param null $closure
     * @return Application|RedirectResponse|Redirector|mixed
     */
    public function succeeded($transaction, array $response, $closure=null)
    {
        if ($transaction->status !== config('payment.transaction_model',Transaction::class)::STATUS['SUCCESS']) {
            $transaction->update(["status" => config('payment.transaction_model',Transaction::class)::STATUS[strtoupper($response['ACK'])] ?? $response['ACK']]);
            alert()->success(__('main.payment'),__('main.succeeded'));
        }
        return  ($closure instanceof \Closure)? $closure($transaction,$response) : redirect($this->redirectPath());
    }

    /**
     * @param $transaction
     * @param array $response
     * @param null $closure
     * @return mixed
     */
    public function failured($transaction, array $response,$closure=null)
    {
        $transaction->update([
            "status" => config('payment.transaction_model',Transaction::class)::STATUS['FAILURE']
        ]);
        return ($closure instanceof \Closure)? $closure($transaction,$response) : redirect($this->redirectPath())->withError(__('main.failured'));

    }

    /**
     * @param array $response
     * @param null $closure
     * @return mixed
     */
    public function canceled(array $response,$closure=null)
    {
        $transaction = config('payment.transaction_model',Transaction::class)::where('status', '!=', Transaction::STATUS['SUCCESS'])->firstWhere('token', $this->response['TOKEN']);

        return ($closure instanceof \Closure)? $closure($transaction,$response) : redirect($this->redirectPath())->withError(__('main.canceled'));
    }
}
