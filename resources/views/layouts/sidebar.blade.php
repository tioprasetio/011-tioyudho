<div class="card border-0 shadow-lg">
                    <div class="card-header  text-white">
                        Welcome, {{ Auth::user()->name}}
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if (Auth::user()->image != "")
                            <img src="{{ asset('uploads/profile/thumb/'.Auth::user()->image) }}" class="img-fluid rounded-circle" alt="{{ Auth::user()->name }}" loading=lazy>
                            @endif
                        </div>
                        <div class="h5 text-center">
                            <strong>{{ Auth::user()->name}}</strong>
                            <p class="h6 mt-2 text-muted">{{ Auth::user()->role }}</p>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-lg mt-3">
                    <div class="card-header  text-white">
                        Navigation
                    </div>
                    <div class="card-body sidebar">
                        <ul class="nav flex-column">
                            @if (Auth::user()->role == 'admin')
                            <li class="nav-item">
                                <a href="{{ route('books.index') }}">Books</a>                               
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('account.reviews') }}">Reviews</a>                               
                            </li>   
                            @endif
                            
                            <li class="nav-item">
                                <a href="{{ route('account.profile') }}">Profile</a>                               
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('account.myReviews') }}">My Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a onclick="logoutUser()">Logout</a>
                            </li>                           
                        </ul>
                    </div>
                </div>

@section('script')
    <script>
        function logoutUser() {
            if (confirm("Are you sure you want to logout?")) {
                $.ajax({
                    success: function(response){
                        window.location.href = '{{ route("account.logout") }}';
                    }
                });
            }
        }
    </script>
@endsection