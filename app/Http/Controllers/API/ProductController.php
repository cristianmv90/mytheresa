<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;

class ProductController extends Controller
{
	/**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    	$productsArray = Storage::disk('local')->exists('data.json') ? json_decode(Storage::disk('local')->get('data.json'), true) : [];
    	$productsArray = is_null($productsArray) ? [] : $productsArray;

    	// filter category
		if ($request->filled('category')) {
			$auxArray = [];
    		for ($i = 0; $i < count($productsArray); $i++) {
    			if (strpos($productsArray[$i]['category'], $request->input('category')) !== false) {
					$auxArray[] = $productsArray[$i];
    			}
    		}
    		$productsArray = $auxArray;
		}

    	// filter price less than
		if ($request->filled('priceLessThan')) {
			$auxArray = [];
    		for ($i = 0; $i < count($productsArray); $i++) {
    			if (floatval($productsArray[$i]['price']['original']) < floatval($request->input('priceLessThan'))) {
					$auxArray[] = $productsArray[$i];
    			}
    		}
    		$productsArray = $auxArray;
		}

		// get max 5 
		$products = [];
		$productsArray = array_slice($productsArray, 0, 5);
    	for ($i = 0; $i < count($productsArray); $i++) {
    		$product = $productsArray[$i];
			$product['price'] = $product['price']['final'];

			$products[] = $product;
    	}

        return response()->json([
        	'error' => false, 
        	'products' => $products,
        ]); 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    	$validator = Validator::make($request->all(),[
    		'sku' => 'required|string|max:255',
    		'name' => 'required|string|max:255',
    		'category' => 'required|string|max:255',
    		'price' => 'required|int',
    		'discount' => 'nullable|int',
    	]);

    	if($validator->fails()){
    		return response()->json([
            	'error' => true, 
            	'messages' => $validator->errors()
    		], 404);       
    	}

        try {
            // storage/app/data.json
            $productsArray = Storage::disk('local')->exists('data.json') ? json_decode(Storage::disk('local')->get('data.json'), true) : [];
        	$productsArray = is_null($productsArray) ? [] : $productsArray;

            $inputData = $request->only([
            	'sku', 
            	'name', 
            	'category',
            ]);

            // validate if already exists the product sku
        	$skuArray = array_filter(array_column($productsArray, 'sku'));
            if(in_array($inputData['sku'], $skuArray)){
	            return response()->json([
	            	'error' => true, 
	            	'message' => 'The sku ' . $inputData['sku'] . ' already exists in the database',
	            ]); 
            }

            // calulate the discount applied 
            $inputData['price'] = $this->calculatePrice($request);

            // push the data to the json
            array_push($productsArray, $inputData);
            Storage::disk('local')->put('data.json', json_encode($productsArray));
 	
 			// response
            return response()->json([
            	'error' => false, 
            	'message' => 'Product created successfully'
            ]);
 
        } catch(Exception $e) {
            return response()->json([
            	'error' => true, 
            	'message' => $e->getMessage()
            ], 404); 
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $sku
     * @return \Illuminate\Http\Response
     */
    public function show($sku)
    {
    	$productsArray = Storage::disk('local')->exists('data.json') ? json_decode(Storage::disk('local')->get('data.json'), true) : [];
    	$productsArray = is_null($productsArray) ? [] : $productsArray;

    	// validate if already exists the product sku
    	$skuArray = array_filter(array_column($productsArray, 'sku'));
    	$position = array_search($sku, $skuArray);

        if ($position !== false) {
        	$product = $productsArray[$position];
        	$product['price'] = $product['price']['final'];

        	return response()->json([
        		'error' => false,
        		'product' => $product,
        	]);
        }

        return response()->json([
        	'error' => true, 
        	'message' => 'Data not found',
        ], 404); 
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sku
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $sku)
    {
    	$validator = Validator::make($request->all(),[
    		'name' => 'nullable|string|max:255',
    		'category' => 'nullable|string|max:255',
    		'price' => 'nullable|int',
    		'discount' => 'nullable|int',
    	]);

    	if($validator->fails()){
    		return response()->json([
            	'error' => true, 
            	'messages' => $validator->errors()
    		]);       
    	}

        try {
            // storage/app/data.json
            $productsArray = Storage::disk('local')->exists('data.json') ? json_decode(Storage::disk('local')->get('data.json'), true) : [];
        	$productsArray = is_null($productsArray) ? [] : $productsArray;

        	$skuArray = array_filter(array_column($productsArray, 'sku'));
        	$position = array_search($sku, $skuArray);

        	if ($position === false) {
		        return response()->json([
		        	'error' => true, 
		        	'message' => 'Data not found',
		        ], 404); 
        	}

            $inputData = $productsArray[$position];

            if(!is_null($request->input('name'))){
            	$inputData['name'] = $request->input('name');
            }

            if(!is_null($request->input('category'))){
            	$inputData['category'] = $request->input('category');
            }

            // calulate the discount applied 
            if(!is_null($request->input('price'))){
            	$inputData['price'] = $this->calculatePrice($request);
            }

            // replace the data to the json
            $productsArray[$position] = $inputData;
            Storage::disk('local')->put('data.json', json_encode($productsArray));
 	
 			// response
            return response()->json([
            	'error' => false, 
            	'message' => 'Product updated successfully'
            ]);
 
        } catch(Exception $e) {
            return response()->json([
            	'error' => true, 
            	'message' => $e->getMessage()
            ]); 
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $sku
     * @return \Illuminate\Http\Response
     */
    public function destroy($sku)
    {
    	$productsArray = Storage::disk('local')->exists('data.json') ? json_decode(Storage::disk('local')->get('data.json'), true) : [];
    	$productsArray = is_null($productsArray) ? [] : $productsArray;

    	// validate if already exists the product sku
    	$skuArray = array_filter(array_column($productsArray, 'sku'));
    	$position = array_search($sku, $skuArray);

        if ($position !== false) {
        	// unset the element
        	unset($productsArray[$position]);
            Storage::disk('local')->put('data.json', json_encode(array_values($productsArray)));

        	return response()->json([
        		'error' => false,
        		'message' => 'Product delete successfully',
        	]);
        }

        return response()->json([
        	'error' => true, 
        	'message' => 'Data not found',
        ], 404); 
    }


    private function calculatePrice($request)
    {
        $discount = 0;
        $originalPrice = $request->input('price');
        if(!is_null($request->input('discount'))){
        	$discount = $request->input('discount');
        }

        if($request->input('sku') == '000003' && $discount < 15){
        	$discount = 15;
        }

        if($request->input('category') == 'boots' && $discount < 30){
        	$discount = 30;
        }

        return [
        	"original" => $originalPrice,
        	"final" => ($discount) ? ($originalPrice - ($originalPrice * $discount / 100)) : null, 
        	"discount_percentage" => ($discount) ? $discount . '%': null, 
        	"currency" => "EUR",
        ];
    }
}
