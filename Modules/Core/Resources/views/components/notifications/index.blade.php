@props(['message' => null])

@if($message)
    <div class="notification">{{ $message }}</div>
@endif

@pushOnce('scripts')
<script type="text/x-template" id="v-notifications-template">
    <div v-if="message" class="notification">@{{ message }}</div>
</script>
<script type="module">
    const notificationsComponent = {
        template: '#v-notifications-template',
        props: { message: String }
    };
    app.component('v-notifications', notificationsComponent);
    app.component('notifications', notificationsComponent);
</script>
@endpushOnce
