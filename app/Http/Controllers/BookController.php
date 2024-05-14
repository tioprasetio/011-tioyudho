<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BookController extends Controller
{
    // Will show books listing page
    public function index()
    {
        return view('books.list');
    }

    // Will show create book page
    public function create()
    {
        return view('books.create');
    }

    // a book in database
    public function store(Request $request)
    {

        $rules = [
            'title' => 'required|min:5',
            'author' => 'required|min:3',
            'status' => 'required',
        ];

        if (!empty($request->image)) {
            $rules['image'] = 'image';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('books.create')->withInput()->withErrors($validator);
        }

        // Save Book in DB
        $book = new Book();
        $book->title = $request->title;
        $book->description = $request->description;
        $book->author = $request->author;
        $book->status = $request->status;
        $book->save();

        // Upload Book Image Here
        if (!empty($request->image)) {
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time().'.'.$ext;
            $image->move(public_path('uploads/books'),$imageName);

            $book->image = $imageName;
            $book->save();

            $manager = new ImageManager(Driver::class);
            $img = $manager->read(public_path('uploads/books/' . $imageName));

            $img->resize(990);
            $img->save(public_path('uploads/books/thumb/' . $imageName)); // VID-7 SELESAI
        }

        return redirect()->route('books.index')->with('success', 'Book added successfully.');
    }

    // Will show edit book page
    public function edit()
    {
    }

    // Will delete a book from database
    public function destroy()
    {
    }
}
