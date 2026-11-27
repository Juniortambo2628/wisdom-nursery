@props(['till' => setting('till_number'), 'account' => setting('mpesa_account')])
{{-- "Paybill 303030, account 2043089423" or "Till 123456", from Site settings → Ordering. --}}
@if ($account)Paybill <strong>{{ $till }}</strong>, account <strong>{{ $account }}</strong>@else Till <strong>{{ $till }}</strong>@endif
