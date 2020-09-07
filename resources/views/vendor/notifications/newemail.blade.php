@component('mail::your-mail-template')

the content that should be placed at the position of the variable in your template, for example:
We send this email to inform you that your order has been send

@slot(‘title’)
Orderstatus
@endslot

@endcomponent
