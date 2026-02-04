{{-- 
    Reusable Toast Container Component
    Include this in your layout to enable toast notifications
    Usage: @include('components.admin.toast-container')
--}}

<div class="toast-container" id="toast-container"></div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Toast !== 'undefined') {
            Toast.success('{{ session('success') }}');
        }
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Toast !== 'undefined') {
            Toast.error('{{ session('error') }}');
        }
    });
</script>
@endif

@if(session('warning'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Toast !== 'undefined') {
            Toast.warning('{{ session('warning') }}');
        }
    });
</script>
@endif

@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Toast !== 'undefined') {
            Toast.info('{{ session('info') }}');
        }
    });
</script>
@endif
