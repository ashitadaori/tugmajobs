@php
    $user = auth()->user();
@endphp

@if($user->isAdmin() || $user->isSuperAdmin())
    @include('admin.sidebar')
@elseif($user->isEmployer())
    @include('front.account.employer.sidebar')
@elseif($user->isJobSeeker())
    @include('front.account.sidebar')
@endif 