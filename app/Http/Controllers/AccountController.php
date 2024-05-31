<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Symfony\Component\HttpKernel\Profiler\Profile;

class AccountController extends Controller
{
    // show register page
    public function register()
    {
        return view('account.register');
    }

    // will register a user
    public function processRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:5',
            'password_confirmation' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.register')->withInput()->withErrors($validator);
        }

        // now register user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('account.login')->with('success', 'You have registerd successfully');
    }

    public function login()
    {
        return view('account.login');
    }

    public function authenticate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return redirect()->route('account.login')->withInput()->withErrors($validator);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->route('account.profile');
        } else {
            return redirect()->route('account.login')->with('error', 'Either email/password is incorrect.');
        }
    }

    //Will show user profile page
    public function profile()
    {
        $user = user::find(Auth::user()->id);

        return view('account.profile', [
            'user' => $user
        ]);
    }

    //Will update user profile
    public function updateProfile(Request $request)
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . Auth::user()->id . ',id',
        ];

        if (!empty($request->image)) {
            $rules['image'] = 'image';
        }

        if (!empty($request->password)) {
            $rules['current_password'] = 'required';
            $rules['password'] = 'required|min:6|confirmed';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route('account.profile')->withInput()->withErrors($validator);
        }

        $user = User::find(Auth::user()->id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        if (!empty($request->password)) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->route('account.profile')->withInput()->withErrors(['current_password' => 'Current password is incorrect']);
            }

            $user->password = Hash::make($request->password);
            $user->save();
        }

        if (!empty($request->image)) {

            File::delete(public_path('uploads/profile/' . $user->image));
            File::delete(public_path('uploads/profile/thumb/' . $user->image));

            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $image->move(public_path('uploads/profile'), $imageName);

            $user->image = $imageName;
            $user->save();

            $manager = new ImageManager(Driver::class);
            $img = $manager->read(public_path('uploads/profile/' . $imageName));

            $img->cover(150, 150);
            $img->save(public_path('uploads/profile/thumb/' . $imageName));
        }

        return redirect()->route('account.profile')->with('success', 'Profile updated successfully.');
    }


    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function myReviews(Request $request){
        
        $reviews = Review::with('book')->where('user_id', Auth::user()->id);
        $reviews = $reviews->orderBy('created_at', 'DESC');

        if (!empty($request->keyword)) {
            $reviews = $reviews->where('review', 'like', '%'. $request->keyword .'%');
        }

        $reviews = $reviews->paginate(10);

        return view('account.my-reviews', [
            'reviews' => $reviews
        ]);
    }
}
