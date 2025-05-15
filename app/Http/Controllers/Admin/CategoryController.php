<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
   
    public function generatePDF($lang, Category $category)
    {
        $pdf = Pdf::loadView('admin.categories.pdf', compact('category'))
          ->setPaper('a4', 'portrait')
          ->setOption(['isRemoteEnabled' => true]);
            return $pdf->download("categories_{$category->id}.pdf");
    }
    public function index($lang)
    {
        $name = 'name_' . $lang;
        $desc = 'desc_' . $lang;
        $categories = Category::all();
        return view('admin.category.index', compact('categories','name','desc'));
    }

    public function create($lang)
    {
        return view('admin.category.create');
    }

    public function store($lang,Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'desc_ar' => 'required|string',
            'desc_en' => 'required|string',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        Category::create($data);

        return redirect()->route('categories.index', ['lang' => app()->getLocale()])->with('success',  __('messages.category_created_successfully'));
    }

    public function edit($lang,Category $category)
    {
        return view('admin.category.edit', compact('category'));
    }

    public function update($lang,Request $request, Category $category)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'desc_ar' => 'required|string',
            'desc_en' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png',
            'images.*' => 'nullable|image|mimes:jpg,jpeg,png',
        ]);

        $data = $request->except('image', 'images');

        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete($category->image);
            $img = $request->file('image');
            $ext = $img->getClientOriginalExtension();
            $name = time() . '.' . $ext;
            $path = public_path('storegs/Category/image');
            $img->move($path, $name);
            $imager = "storegs/Category/image/".$name;
            $data['image'] = $imager;
        }

        if ($request->hasFile('images')) {
            if ($category->images) {
                foreach (json_decode($category->images) as $img) {
                    Storage::disk('public')->delete($img);
                }
            }
            $images = [];

            foreach ($request->file('images') as $img) {
                $ext = $img->getClientOriginalExtension();
                $name = time() . rand(1000, 9999) . '.' . $ext; // لضمان عدم تكرار الأسماء
                $path = public_path('storegs/Category/images');

                // التأكد من أن المجلد موجود، وإذا لم يكن موجودًا، يتم إنشاؤه
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }

                $img->move($path, $name);
                $images[] = "storegs/Category/images/" . $name; // تخزين المسار النسبي
            }

            $gallary_images = json_encode($images);
            $data['images'] = $gallary_images;
        }

        $category->update($data);

        return redirect()->route('categories.index', ['lang' => app()->getLocale()])->with('success', __('messages.category_updated_successfully'));
    }

    public function destroy($lang,Category $category)
    {
        if ($category->image) Storage::disk('public')->delete($category->image);
        if ($category->images) {
            foreach (json_decode($category->images) as $img) {
                Storage::disk('public')->delete($img);
            }
        }
        $category->delete();
        return redirect()->route('categories.index', ['lang' => app()->getLocale()])->with('success', __('messages.category_delete_successfully'));
    }
}
