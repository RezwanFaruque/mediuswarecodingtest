<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use App\Model\ProductImage;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {

        // fetching products

        $products = Product::with('variants')->with('prices')->paginate(2);

        // dd($products);

        return view('products.index',compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        // dd($request->all());
       
        $product = new Product();

        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;

        // saving for product table
        $product->save();

        foreach ($request->product_variant as $key => $value) {
            // dd(implode("/", $value['tags']));

            $product_variant = new ProductVariant();

            $product_variant->variant_id = $value['option'];
            $product_variant->product_id = $product->id;
            $product_variant->variant = implode("/", $value['tags']);

            $product_variant->save();
            
        }

        // dd($request->product_variant_prices);
        foreach ($request->product_variant_prices as  $value) {



            $product_variant_price = new ProductVariantPrice();

            $product_variant_price->product_id = $product->id;

            $product_variant_price->price = $value['price'];

            $product_variant_price->stock = $value['stock'];

            $product_variant_price->product_variant_one = $product_variant->id;

            $product_variant_price->product_variant_two = $product_variant->id;

            $product_variant_price->product_variant_three = $product_variant->id;




            $product_variant_price->save();
        }


        if ($request->hasFile('profile_image')) {
            foreach ($request->product_image as  $value) {
            
            $product_image = new ProductImage();

            $file = $value;
            $filename = time() . '.' . $file->getClientOriginalExtension();

            $path = public_path('assets/createproductimages');
            $file->move($path,$filename);

            $product_image->file_path = 'assets/createproductimages/' . $filename;

            $product_image->product_id = $product->id;

            $product_image->thumbnail = 'thumbnail';

            $product_image->save();


        }

    }

        return response()->json([

            'status' => 'success',
            'message' => 'Your Product Saved Successsfully',
        ]);
        

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $products = Product::find($id)->with('variants')->with('prices')->first();


        $variants = Variant::all();
        return view('products.edit', compact('variants','products'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }


    public function fileterProduct(Request $request){

        $product = Product::with('variants','prices');

        // dd('products.variants.title');

        if($request->title != ''){
            $title = $request->keyword;

            $product->where('products.title','like', '%'.$title.'%');
        }

        if($request->price_from != ''){

            $price_from = [];
            if (gettype($request->get('price_from')) == 'string') {
                $price_from = json_decode($request->get('price_from'));
            } elseif (gettype($request->get('price_from')) == 'array') {
                $price_from = $request->get('price_from');
            }

            $price_from = $request->price_from;
            $product->where(function ($query) use ($price_from) {
                $query->whereHas('prices', function ($query) use ($price_from) {
                    $query->whereIn('product_id', $price_from);
                });
            });
           
        }

        if($request->price_to != ''){

        }


        if($request->date != ''){

        }

        if($product){

            $products = $product->paginate(2);

        }

        return view('products.index',compact('products'));


    }
}
