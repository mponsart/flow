@if(session('success'))
    <div class="bg-green-600 text-white px-4 py-2 rounded mb-4 animate-fade-in">
        {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="bg-red-600 text-white px-4 py-2 rounded mb-4 animate-fade-in">
        {{ $errors->first() }}
    </div>
@endif
