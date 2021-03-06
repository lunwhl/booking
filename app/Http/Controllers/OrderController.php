<?php

namespace App\Http\Controllers;

use Auth;
use Cart;
use App\Order;
use App\Item;
use App\Product;
use App\Common;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseToAdminEmail;
use App\Mail\PurchaseToCustomerEmail;
use Illuminate\Http\Request;
use Billplz\Client;
use Http\Client\Common\HttpMethodsClient;
use Http\Adapter\Guzzle6\Client as GuzzleHttpClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public $rules = array(
        'billing_name' => 'required|string|max:255',
        'billing_address' => 'required|string|max:255',
        'billing_city' => 'required|string|max:255',
        'billing_state' => 'required|string|max:255',
        'billing_postcode' => 'required|string|max:255',
        'billing_email' => 'required|string|email|max:255',
        'billing_phone' => 'required|string|max:255',
        'shipping_name' => 'required_if:different_shipping,true',
        'shipping_address'=> 'required_if:different_shipping,true',
        'shipping_city'=> 'required_if:different_shipping,true',
        'shipping_state'=> 'required_if:different_shipping,true',
        'shipping_postcode'=> 'required_if:different_shipping,true',
        'shipping_email'=> 'required_if:different_shipping,true',
        'shipping_phone'=> 'required_if:different_shipping,true'
    );

    public $messages = array(
        'shipping_name.required_if'=> 'Name is required',
        'shipping_address.required_if'=> 'Address is required',
        'shipping_city.required_if'=> 'City is required',
        'shipping_state.required_if'=> 'State is required',
        'shipping_postcode.required_if'=> 'Postcode is required',
        'shipping_email.required_if'=> 'Email is required',
        'shipping_phone.required_if'=> 'Phone is required'
    );

    public function index()
    {
        return view('order.page');
    }

    public function items()
    {
        $sorting = request()->descending == 'true' ? 'DESC' : 'ASC';
        $skip = (request()->page - 1) * request()->row;
        
        $orders = Auth::user()->orders()->with('items')
                                ->orderBy(request()->sort_by, $sorting)
                                ->skip($skip)
                                ->take(request()->row)
                                ->get();

        return response(['orders' => $orders]);
    }

    public function total()
    {
        $total = Auth::user()->orders()->count();

        return response(['total' => $total]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate($this->rules, $this->messages);

        $order = $this->createOrder($request);
        $this->createItem($order);

        Common::deleteCart();
        Cart::destroy();

        // $collectionClient = new Client(['auth' => ['5aa9c747-9532-442b-88c8-8c84b36be2cd']]);

        // $response = $collectionClient->post('https://billplz-staging.herokuapp.com/api/v3/collections', [
        //     'form_params' => [
        //         'title' => 'test1',
        //     ],
        // ]);

        // $response = json_decode($response->getBody(), true);

        $email = $request->shipping_email? $request->shipping_email : $request->billing_email;
        Mail::to("info@kalt.com.my")->send(new PurchaseToAdminEmail($request));
        Mail::to($email)->send(new PurchaseToCustomerEmail($request));

        // return response($response);
        return response(200);
    }

    public function createOrder($request)
    {
        if($request->different_shipping)
            return Order::create([
                        'user_id' => auth()->user()->id,
                        'billing_name'=> $request->billing_name,
                        'billing_company_name'=> $request->billing_company_name,
                        'billing_address'=> $request->billing_address,
                        'billing_city'=> $request->billing_city,
                        'billing_state'=> $request->billing_state,
                        'billing_postcode'=> $request->billing_postcode,
                        'billing_email'=> $request->billing_email,
                        'billing_phone'=> $request->billing_phone,
                        'shipping_name'=> $request->shipping_name,
                        'shipping_company_name'=> $request->shipping_company_name,
                        'shipping_address'=> $request->shipping_address,
                        'shipping_city'=> $request->shipping_city,
                        'shipping_state'=> $request->shipping_state,
                        'shipping_postcode'=> $request->shipping_postcode,
                        'shipping_email'=> $request->shipping_email,
                        'shipping_phone'=> $request->shipping_phone,
                        'subtotal'=> $request->subtotal,
                        'shipping_price'=> $request->shipping_price,
                        'total'=> $request->total,
                        'note'=> $request->note,
                        'pickup'=> $request->pickup
            ]);
        else
            return Order::create([
                        'user_id' => auth()->user()->id,
                        'billing_name'=> $request->billing_name,
                        'billing_company_name'=> $request->billing_company_name,
                        'billing_address'=> $request->billing_address,
                        'billing_city'=> $request->billing_city,
                        'billing_state'=> $request->billing_state,
                        'billing_postcode'=> $request->billing_postcode,
                        'billing_email'=> $request->billing_email,
                        'billing_phone'=> $request->billing_phone,
                        'shipping_name'=> $request->billing_name,
                        'shipping_company_name'=> $request->billing_company_name,
                        'shipping_address'=> $request->billing_address,
                        'shipping_city'=> $request->billing_city,
                        'shipping_state'=> $request->billing_state,
                        'shipping_postcode'=> $request->billing_postcode,
                        'shipping_email'=> $request->billing_email,
                        'shipping_phone'=> $request->billing_phone,
                        'subtotal'=> $request->subtotal,
                        'shipping_price'=> $request->shipping_price,
                        'total'=> $request->total,
                        'note'=> $request->note,
                        'pickup'=> $request->pickup
            ]);
    }

    public function createItem($order)
    {
        $carts = Cart::content();
        foreach($carts as $cart) {
            Item::create([
                   'order_id' => $order->id,
                   'name' => $cart->name,
                   'description' => Product::find($cart->id)->description,
                   'price' => $cart->price,
                   'image_path' => Product::find($cart->id)->image_path,
                   'qty' => $cart->qty,
                   'installation_type' => $cart->options['installation'],
                   'installation_price' => $cart->options['installationPrice'],
            ]); 

            $product = Product::find($cart->id);
            $product->sold_qty = $product->sold_qty + $cart->qty;
            $product->save();
        };       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function loadDirect()
    {
        return view('checkout.thankyou');
    }

    public function thankyou()
    {
        $order = Order::with('items')->where('user_id', auth()->user()->id)->orderBy('updated_at', 'DESC')->first();

        return view('thankyou.page', ['order' => $order]);
    }
}
