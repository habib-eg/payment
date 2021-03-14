<?php

namespace Habib\Payment\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|RedirectResponse|Response
     */

    public function addCart(Request $request)
    {
        \Cart::addToCart($request);
        if ($request->ajax()) {
            return response()->json([
                "message"=>"Success"
            ]);
        }
        alert()->success('Cart','Added');
        return back();
    }

    public function index()
    {
        $carts=\Cart::all();

        return view('cart',compact('carts'));
    }

    public function edit(Request $request,string $id)
    {

        \Cart::EditCart($id,(int) $request->get('qty'));

        if ($request->ajax()) {
            return response()->json([
                "message"=>"edited"
            ]);
        }
        alert()->success('Cart','edited');
        return back();
    }

    public function products()
    {
        return view('products');
    }
}
