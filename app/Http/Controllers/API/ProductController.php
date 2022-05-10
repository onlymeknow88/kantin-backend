<?php

namespace App\Http\Controllers\API;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGallery;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function addProduct(Request $request){

        $data = $request->except('image');

        $product = Product::create($data);

        if($request->hasFile('image'))
        {
            $url = $request->file('image')->store('public/gallery');

            ProductGallery::create([
                'products_id' => $product->id,
                'url' => $url
            ]);

        }
        return ResponseFormatter::success(
            $product,
            'Upload berhasil'
        );
        
    }

    public function All(Request $request) {

        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $name = $request->input('name');
        $tags = $request->input('tags');
        $categories = $request->input('categories');

        $price_from = $request->input('price_from');
        $price_to = $request->input('price_to');

        if($id) {

            $product = Product::with(['category','galleries'])->find($id);

            if($product)
                return ResponseFormatter::success(
                    $product,
                    'Data produk berhasil diambil'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Data produk tidak ada',
                    404
                );
        }

        $product = Product::with(['category','galleries']);

        if($name)
            $product->where('name', 'like', '%' . $name . '%');

        if($tags)
            $product->where('tags', 'like', '%' . $tags . '%');

        if($price_from)
            $product->where('price', '>=', $price_from);

        if($price_to)
            $product->where('price', '<=', $price_to);

        if($categories)
            $product->where('categories_id', $categories);

        return ResponseFormatter::success(
            $product->paginate($limit),
            'Data list produk berhasil diambil'
        );
    }

    public function getProductByCategory(Request $request) {
        $categoryName = $request->name;

        $categories = ProductCategory::where('name',$categoryName)->first();
        $categoryId = $categories->id;

        $product = Product::with(['category','galleries'])->where('categories_id',$categoryId)->get();

        return ResponseFormatter::success(
            $product,
            'Data list produk by category berhasil diambil'
        );

    }

    public function getSearchProductByCategory(Request $request){
        $categoryName = $request->name;
        $cari = $request->cari;

        $categories = ProductCategory::where('name',$categoryName)->first();
        $categoryId = $categories->id;


        $product = Product::with(['category','galleries'])->where('categories_id',$categoryId)->where('name', 'like', '%' . $cari . '%')->get();

        return ResponseFormatter::success(
            $product,
            'Data Search berhasil diambil'
        );
    }

    public function editProduct(Request $request){
        $id = $request->id;

        $data = $request->except('id','image');

        $product = Product::with(['category','galleries'])->find($id);
        $product->update($data);

        $cek_image = ProductGallery::where('product_id',$product_id)->first();
        
        if($request->has('image')){
            if($cek_image != null){
                Storage::delete($cek_image->url);
                ProductGallery::where('id','=',$cek_image->id ?? null)->delete();
            }
        }

        if($product) {

            $path = $request->file('image')->store('public/gallery');

            ProductGallery::where('id','=',$cek_image->id ?? null)->update([
                'url' => $path
            ]);
        }
            
        

        return ResponseFormatter::success(
            $product,
            'Data Product Berhasil diedit'
        );
    }

    public function deleteProduct(Request $request){
        $id = $request->id;

        $product = Product::with(['category','galleries'])->find($id);

        $gallery = ProductGallery::where('products_id',$product->id)->first();

        $product->delete();
        $gallery->delete();

        return ResponseFormatter::success(
            null,
            'Data Product Berhasil dihapus'
        );

    }
}
