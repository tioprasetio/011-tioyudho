<?php

namespace App\Http\Controllers;

use App\Models\book;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    // Will show home page
    public function index(Request $request) {
        $books = book::orderBy('created_at', 'DESC');

        if (!empty($request->keyword)) {
            $books->where('title', 'like', '%'.$request->keyword.'%');
        }

        $books = $books->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('status', 1)
            ->paginate(8);

        return view('home', [
            'books'=>$books
        ]);
    }

    // Will show book detail page
    public function detail($id)
    {
        $book = Book::with(['reviews.user', 'reviews' => function ($query) {
            $query->where('status',
                1
            );
        }])
        ->where('status', 1)
        ->findOrFail($id);

        if (!$book) {
            abort(404);
        }

        $book->loadCount('reviews')->loadAvg('reviews', 'rating');

        $relatedBooks = Book::where('status', 1)
        ->take(3)
        ->where('id', '!=', $id)
        ->inRandomOrder()
        ->get();

        $relatedBooks->loadCount('reviews')->loadAvg('reviews', 'rating');

        return view('book-detail',
            [
                'book' => $book,
                'relatedBooks' => $relatedBooks
            ]
        );
    }


    // Will save review in db
    public function saveReview(Request $request) {
        $validator = Validator::make($request->all(),[
            'review' => 'required|min:10',
            'rating' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // Apply condition here
        $countReview = Review::where('user_id', Auth::user()->id)->where('book_id', $request->book_id)->count();

        if ($countReview > 0) {
            session()->flash('error', 'You already submitted a review.');
            return response()->json([
                'status' => true,
            ]);
        }

        $review = new Review();
        $review->review = $request->review;
        $review->rating = $request->rating;
        $review->user_id = Auth::user()->id;
        $review->book_id = $request->book_id;
        $review->status = 1;
        $review->save();

        session()->flash('success', 'Review submitted successfully.');

        return response()->json([
            'status' => true,
        ]);
    }
}
