{{-- Browser and home-screen icons: the tree from the official logo (shared by the site, error pages and dashboard).
     ?v= changes whenever the icon file changes, so browsers drop a cached old icon. --}}
@php $v = substr(md5_file(public_path('favicon.ico')), 0, 8); @endphp
<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ $v }}" sizes="48x48">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ $v }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v={{ $v }}">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ $v }}">
<link rel="manifest" href="{{ asset('site.webmanifest') }}?v={{ $v }}">
